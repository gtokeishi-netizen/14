<?php
/**
 * Grant Archive Template - Clean & Professional Edition v9.0
 * File: archive-grant.php
 * 
 * シンプルでスタイリッシュな信頼感のあるデザイン
 * 機能はそのまま、デザインをクリーンに刷新
 * 
 * @package Grant_Insight_Clean
 * @version 9.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// 必要な関数の存在確認
$required_functions = [
    'gi_safe_get_meta',
    'gi_get_formatted_deadline',
    'gi_map_application_status_ui',
    'gi_get_user_favorites',
    'gi_get_grant_amount_display'
];

// URLパラメータから検索条件を取得（両方のパラメータ名に対応）
$search_params = [
    'search' => sanitize_text_field($_GET['s'] ?? ''),
    'category' => sanitize_text_field($_GET['category'] ?? $_GET['grant_category'] ?? ''),
    'prefecture' => sanitize_text_field($_GET['prefecture'] ?? $_GET['grant_prefecture'] ?? ''),
    'municipality' => sanitize_text_field($_GET['municipality'] ?? $_GET['grant_municipality'] ?? ''),
    'region' => sanitize_text_field($_GET['region'] ?? ''),
    'amount' => sanitize_text_field($_GET['amount'] ?? ''),
    'status' => sanitize_text_field($_GET['status'] ?? ''),
    'difficulty' => sanitize_text_field($_GET['difficulty'] ?? ''),
    'success_rate' => sanitize_text_field($_GET['success_rate'] ?? ''),
    'application_method' => sanitize_text_field($_GET['method'] ?? ''),
    'is_featured' => sanitize_text_field($_GET['featured'] ?? ''),
    'sort' => sanitize_text_field($_GET['sort'] ?? 'date_desc'),
    'view' => sanitize_text_field($_GET['view'] ?? 'grid'),
    'page' => max(1, intval($_GET['paged'] ?? 1))
];

// 統計データ取得
$stats = function_exists('gi_get_cached_stats') ? gi_get_cached_stats() : [
    'total_grants' => wp_count_posts('grant')->publish ?? 0,
    'active_grants' => 0,
    'prefecture_count' => 47,
    'avg_success_rate' => 65
];

// お気に入りリスト取得
$user_favorites = function_exists('gi_get_user_favorites_cached') ? 
    gi_get_user_favorites_cached() : 
    (function_exists('gi_get_user_favorites') ? gi_get_user_favorites() : []);

// 初期表示用クエリの構築
$initial_args = [
    'post_type' => 'grant',
    'posts_per_page' => 12,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => false
];

// 検索条件の適用
if (!empty($search_params['search'])) {
    $initial_args['s'] = $search_params['search'];
}

// タクソノミークエリ
$tax_query = ['relation' => 'AND'];
if (!empty($search_params['category'])) {
    $tax_query[] = [
        'taxonomy' => 'grant_category',
        'field' => 'slug',
        'terms' => explode(',', $search_params['category'])
    ];
}
if (!empty($search_params['prefecture'])) {
    $tax_query[] = [
        'taxonomy' => 'grant_prefecture',
        'field' => 'slug',
        'terms' => explode(',', $search_params['prefecture'])
    ];
}
if (!empty($search_params['municipality'])) {
    // 市町村フィルター：市町村 OR その都道府県の助成金を含める
    $municipality_slugs = explode(',', $search_params['municipality']);
    
    // 市町村から都道府県を取得
    $prefecture_slugs = [];
    foreach ($municipality_slugs as $muni_slug) {
        $muni_term = get_term_by('slug', $muni_slug, 'grant_municipality');
        if ($muni_term && !is_wp_error($muni_term)) {
            // 市町村名から都道府県を推測（例：「東京都渋谷区」→「東京都」）
            // または親タームがある場合は親タームを使用
            if ($muni_term->parent) {
                $parent_term = get_term($muni_term->parent, 'grant_municipality');
                if ($parent_term && !is_wp_error($parent_term)) {
                    $prefecture_slugs[] = $parent_term->slug;
                }
            }
            
            // 市町村名から都道府県名を抽出
            $muni_name = $muni_term->name;
            foreach (['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
                     '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
                     '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
                     '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
                     '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
                     '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
                     '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'] as $pref_name) {
                if (strpos($muni_name, $pref_name) === 0) {
                    $pref_term = get_term_by('name', $pref_name, 'grant_prefecture');
                    if ($pref_term && !is_wp_error($pref_term)) {
                        $prefecture_slugs[] = $pref_term->slug;
                    }
                    break;
                }
            }
        }
    }
    
    // 市町村 OR 都道府県のクエリ
    $location_query = ['relation' => 'OR'];
    $location_query[] = [
        'taxonomy' => 'grant_municipality',
        'field' => 'slug',
        'terms' => $municipality_slugs
    ];
    
    if (!empty($prefecture_slugs)) {
        $location_query[] = [
            'taxonomy' => 'grant_prefecture',
            'field' => 'slug',
            'terms' => array_unique($prefecture_slugs)
        ];
    }
    
    $tax_query[] = $location_query;
}
if (count($tax_query) > 1) {
    $initial_args['tax_query'] = $tax_query;
}

// メタクエリ
$meta_query = ['relation' => 'AND'];

if (!empty($search_params['status'])) {
    $statuses = explode(',', $search_params['status']);
    $db_statuses = array_map(function($s) {
        return $s === 'active' ? 'open' : ($s === 'upcoming' ? 'upcoming' : $s);
    }, $statuses);
    
    $meta_query[] = [
        'key' => 'application_status',
        'value' => $db_statuses,
        'compare' => 'IN'
    ];
}

if (!empty($search_params['is_featured']) && $search_params['is_featured'] === '1') {
    $meta_query[] = [
        'key' => 'is_featured',
        'value' => '1',
        'compare' => '='
    ];
}

if (count($meta_query) > 1) {
    $initial_args['meta_query'] = $meta_query;
}

// ソート処理
switch($search_params['sort']) {
    case 'amount_desc':
        $initial_args['orderby'] = 'meta_value_num';
        $initial_args['meta_key'] = 'max_amount_numeric';
        $initial_args['order'] = 'DESC';
        break;
    case 'featured_first':
        $initial_args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];
        $initial_args['meta_key'] = 'is_featured';
        break;
    default:
        $initial_args['orderby'] = 'date';
        $initial_args['order'] = 'DESC';
}

// クエリ実行
$grants_query = new WP_Query($initial_args);

// タクソノミー取得
$all_categories = get_terms([
    'taxonomy' => 'grant_category',
    'hide_empty' => true,  // 0件のカテゴリを非表示
    'orderby' => 'count',
    'order' => 'DESC'
]);

// 47都道府県を北海道から沖縄まで固定順序で取得
$all_prefectures_terms = get_terms([
    'taxonomy' => 'grant_prefecture',
    'hide_empty' => true  // 0件の都道府県を非表示
]);

// まずタームが取得できているか確認
if (empty($all_prefectures_terms) || is_wp_error($all_prefectures_terms)) {
    $all_prefectures = [];
} else {
    // タームをslugでマップ化
    $prefecture_term_map = [];
    foreach ($all_prefectures_terms as $term) {
        $prefecture_term_map[$term->slug] = $term;
    }

    // 固定順序でタームオブジェクトを並べ替え
    $all_prefectures = [];
    if (function_exists('gi_get_all_prefectures')) {
        $prefecture_order = gi_get_all_prefectures();
        foreach ($prefecture_order as $pref_data) {
            if (isset($prefecture_term_map[$pref_data['slug']])) {
                $all_prefectures[] = $prefecture_term_map[$pref_data['slug']];
            }
        }
        // 固定順序に含まれない都道府県も追加
        if (empty($all_prefectures)) {
            $all_prefectures = $all_prefectures_terms;
        }
    } else {
        // フォールバック: 関数がない場合は取得したタームをそのまま使用
        $all_prefectures = $all_prefectures_terms;
    }
}

// 市町村タクソノミー取得
$all_municipalities = get_terms([
    'taxonomy' => 'grant_municipality',
    'hide_empty' => true,  // 0件の市町村を非表示
    'orderby' => 'name',
    'order' => 'ASC'
]);

// 市町村と都道府県の紐付けマップを作成
$municipality_prefecture_map = [];
if (!empty($all_municipalities) && !is_wp_error($all_municipalities)) {
    foreach ($all_municipalities as $municipality) {
        // 市町村名から都道府県を推測
        $muni_name = $municipality->name;
        $pref_slug = '';
        
        // 都道府県名が市町村名に含まれているか確認
        foreach ($all_prefectures as $pref) {
            // 都道府県名から「県」「府」「都」を除いた部分で検索
            $pref_name_short = str_replace(['県', '府', '都', '道'], '', $pref->name);
            if (strpos($muni_name, $pref_name_short) !== false || strpos($muni_name, $pref->name) !== false) {
                $pref_slug = $pref->slug;
                break;
            }
        }
        
        $municipality_prefecture_map[$municipality->slug] = $pref_slug;
    }
}

// 地方区分マッピング（カーセンサー風の階層構造用）
$region_mapping = [
    '北海道・東北' => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県'],
    '関東' => ['茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県'],
    '中部' => ['新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県'],
    '近畿' => ['三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県'],
    '中国' => ['鳥取県', '島根県', '岡山県', '広島県', '山口県'],
    '四国' => ['徳島県', '香川県', '愛媛県', '高知県'],
    '九州・沖縄' => ['福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']
];
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); bloginfo('name'); ?></title>
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700;800&display=swap" as="style">
    
    <!-- Critical CSS -->
    <style>
    /* ===== Clean & Professional Design System - Monochrome Edition ===== */
    :root {
        /* Core Colors - Stylish Monochrome */
        --primary: #000000;
        --primary-light: #262626;
        --primary-dark: #000000;
        --secondary: #525252;
        --accent: #171717;
        
        /* Neutral Colors - Monochrome Palette */
        --white: #ffffff;
        --gray-50: #fafafa;
        --gray-100: #f5f5f5;
        --gray-200: #e5e5e5;
        --gray-300: #d4d4d4;
        --gray-400: #a3a3a3;
        --gray-500: #737373;
        --gray-600: #525252;
        --gray-700: #404040;
        --gray-800: #262626;
        --gray-900: #171717;
        
        /* Semantic Colors */
        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #000000;
        
        /* Typography */
        --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
        --font-japanese: 'Noto Sans JP', 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', 'Yu Gothic Medium', 'Meiryo', sans-serif;
        
        /* Spacing */
        --space-1: 0.25rem;
        --space-2: 0.5rem;
        --space-3: 0.75rem;
        --space-4: 1rem;
        --space-5: 1.25rem;
        --space-6: 1.5rem;
        --space-8: 2rem;
        --space-10: 2.5rem;
        --space-12: 3rem;
        --space-16: 4rem;
        --space-20: 5rem;
        
        /* Border Radius */
        --radius-sm: 0.25rem;
        --radius-md: 0.375rem;
        --radius-lg: 0.5rem;
        --radius-xl: 0.75rem;
        --radius-2xl: 1rem;
        
        /* Shadows */
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        
        /* Transitions */
        --transition: all 0.15s ease-in-out;
        --transition-slow: all 0.3s ease-in-out;
    }
    
    /* Reset & Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    html {
        font-size: 16px;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    body {
        font-family: var(--font-primary);
        color: var(--gray-900);
        background-color: var(--gray-50);
        font-weight: 400;
    }
    
    /* Container */
    .clean-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 var(--space-4);
    }
    
    @media (min-width: 768px) {
        .clean-container {
            padding: 0 var(--space-6);
        }
    }
    
    /* ===== HEADER SECTION ===== */
    .clean-header {
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding: var(--space-8) 0;
    }
    
    .clean-header-content {
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .clean-title {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: var(--space-4);
        letter-spacing: -0.025em;
    }
    
    .clean-subtitle {
        font-size: 1.125rem;
        color: var(--gray-600);
        font-weight: 400;
        max-width: 600px;
        margin: 0 auto;
    }
    
    /* ===== SEARCH SECTION ===== */
    .clean-search-section {
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding: var(--space-6) 0;
    }
    
    .clean-search-wrapper {
        max-width: 600px;
        margin: 0 auto var(--space-6);
    }
    
    .clean-search-box {
        position: relative;
    }
    
    .clean-search-input {
        width: 100%;
        padding: var(--space-4) var(--space-4) var(--space-4) 3rem;
        border: 2px solid var(--gray-300);
        border-radius: var(--radius-lg);
        font-size: 1rem;
        font-weight: 400;
        background: var(--white);
        transition: var(--transition);
    }
    
    .clean-search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
    }
    
    .clean-search-input::placeholder {
        color: var(--gray-500);
    }
    
    .clean-search-icon {
        position: absolute;
        left: var(--space-4);
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: 1rem;
    }
    
    .clean-search-clear {
        position: absolute;
        right: var(--space-4);
        top: 50%;
        transform: translateY(-50%);
        width: 1.75rem;
        height: 1.75rem;
        border: none;
        background: var(--gray-300);
        color: var(--gray-600);
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        font-size: 0.75rem;
    }
    
    .clean-search-clear:hover {
        background: var(--danger);
        color: var(--white);
    }
    
    /* Quick Filters */
    .clean-filters {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-2);
        justify-content: center;
    }
    
    .clean-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-4);
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-2xl);
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
        position: relative;
        overflow: hidden;
    }
    
    .clean-filter-pill::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .clean-filter-pill:hover {
        background: var(--gray-200);
        border-color: var(--gray-300);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }
    
    .clean-filter-pill:hover::before {
        left: 100%;
    }
    
    .clean-filter-pill.active {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
    }
    
    .clean-filter-pill:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    
    .clean-filter-pill i {
        font-size: 0.75rem;
    }
    
    .clean-filter-count {
        background: rgba(255, 255, 255, 0.2);
        color: inherit;
        padding: 0 var(--space-2);
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 1.25rem;
        text-align: center;
    }
    
    .clean-filter-pill:not(.active) .clean-filter-count {
        background: var(--primary);
        color: var(--white);
    }
    
    /* ===== CONTROLS SECTION ===== */
    .clean-controls {
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        padding: var(--space-4) 0;
    }
    
    .clean-controls-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--space-4);
        flex-wrap: wrap;
    }
    
    .clean-controls-left,
    .clean-controls-right {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }
    
    .clean-select {
        padding: var(--space-2) var(--space-4);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        background: var(--white);
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        cursor: pointer;
        transition: var(--transition);
        min-width: 150px;
    }
    
    .clean-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
    }
    
    .clean-filter-button {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-4);
        background: var(--white);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        color: var(--gray-700);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .clean-filter-button:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .clean-filter-button.has-filters {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }
    
    .clean-view-toggle {
        display: flex;
        background: var(--gray-100);
        border-radius: var(--radius-md);
        padding: 2px;
    }
    
    .clean-view-btn {
        padding: var(--space-2) var(--space-3);
        background: transparent;
        border: none;
        color: var(--gray-600);
        cursor: pointer;
        border-radius: calc(var(--radius-md) - 2px);
        transition: var(--transition);
        font-size: 0.875rem;
    }
    
    .clean-view-btn:hover {
        color: var(--gray-800);
    }
    
    .clean-view-btn.active {
        background: var(--white);
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }
    
    /* ===== MAIN LAYOUT ===== */
    .clean-main {
        padding: var(--space-8) 0;
        min-height: 50vh;
    }
    
    .clean-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: var(--space-8);
        align-items: start;
    }
    
    /* ===== SIDEBAR ===== */
    .clean-sidebar {
        position: sticky;
        top: 120px;
    }
    
    .clean-filter-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-xl);
        overflow: hidden;
    }
    
    .clean-filter-header {
        background: var(--gray-50);
        padding: var(--space-4);
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .clean-filter-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }
    
    .clean-filter-close {
        display: none;
        width: 2rem;
        height: 2rem;
        border: none;
        background: var(--gray-200);
        color: var(--gray-600);
        cursor: pointer;
        border-radius: 50%;
        transition: var(--transition);
    }
    
    .clean-filter-close:hover {
        background: var(--gray-300);
    }
    
    .clean-filter-body {
        padding: var(--space-4);
    }
    
    .clean-filter-group {
        margin-bottom: var(--space-6);
        padding-bottom: var(--space-4);
        border-bottom: 1px solid var(--gray-200);
    }
    
    .clean-filter-group:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .clean-filter-group-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: var(--space-3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .clean-filter-option {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2);
        cursor: pointer;
        border-radius: var(--radius-md);
        transition: var(--transition);
    }
    
    .clean-filter-option:hover {
        background: var(--gray-50);
    }
    
    .clean-filter-checkbox,
    .clean-filter-radio {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
        cursor: pointer;
    }
    
    .clean-filter-label {
        flex: 1;
        font-size: 0.875rem;
        color: var(--gray-700);
        font-weight: 400;
        cursor: pointer;
    }
    
    .clean-filter-count {
        background: var(--gray-100);
        color: var(--gray-600);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0 var(--space-2);
        border-radius: var(--radius-sm);
        min-width: 1.25rem;
        text-align: center;
    }
    
    /* ===== RESULTS HEADER ===== */
    .clean-results-header {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        margin-bottom: var(--space-6);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .clean-results-info {
        display: flex;
        align-items: baseline;
        gap: var(--space-2);
    }
    
    .clean-results-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
    }
    
    .clean-results-text {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    .clean-loading-indicator {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        color: var(--gray-600);
        font-size: 0.875rem;
    }
    
    .clean-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid var(--gray-300);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* ===== GRANTS GRID ===== */
    .clean-grants-container {
        position: relative;
        min-height: 400px;
    }
    
    .clean-grants-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: var(--space-6);
        margin-bottom: var(--space-8);
    }
    
    .clean-grants-list {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
        margin-bottom: var(--space-8);
    }
    
    /* Grant Card */
    .clean-grant-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: var(--transition-slow);
        cursor: pointer;
    }
    
    .clean-grant-card:hover {
        border-color: var(--gray-300);
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }
    
    .clean-grant-card-header {
        padding: var(--space-4);
        border-bottom: 1px solid var(--gray-200);
    }
    
    .clean-grant-card-body {
        padding: var(--space-4);
    }
    
    .clean-grant-card-footer {
        padding: var(--space-3) var(--space-4);
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* ===== PAGINATION ===== */
    .clean-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: var(--space-1);
        margin-top: var(--space-8);
    }
    
    .clean-page-btn {
        min-width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--white);
        border: 1px solid var(--gray-300);
        color: var(--gray-700);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        border-radius: var(--radius-md);
        transition: var(--transition);
        text-decoration: none;
    }
    
    .clean-page-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .clean-page-btn.current {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }
    
    /* ===== NO RESULTS ===== */
    .clean-no-results {
        text-align: center;
        padding: var(--space-12);
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
    }
    
    .clean-no-results-icon {
        font-size: 3rem;
        color: var(--gray-400);
        margin-bottom: var(--space-4);
    }
    
    .clean-no-results-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: var(--space-2);
    }
    
    .clean-no-results-text {
        color: var(--gray-600);
        margin-bottom: var(--space-6);
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .clean-reset-button {
        padding: var(--space-3) var(--space-6);
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: var(--radius-md);
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .clean-reset-button:hover {
        background: var(--primary-dark);
    }
    
    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 1024px) {
        .clean-layout {
            grid-template-columns: 1fr;
        }
        
        .clean-sidebar {
            position: static;
            margin-bottom: var(--space-6);
        }
        
        .clean-grants-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .clean-container {
            padding: 0 var(--space-4);
        }
        
        .clean-title {
            font-size: 1.875rem;
        }
        
        .clean-subtitle {
            font-size: 1rem;
        }
        
        .clean-search-section {
            padding: var(--space-4) 0;
        }
        
        .clean-filters {
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: var(--space-2);
        }
        
        .clean-controls-inner {
            flex-direction: column;
            align-items: stretch;
        }
        
        .clean-controls-left,
        .clean-controls-right {
            width: 100%;
            justify-content: space-between;
        }
        
        .clean-view-toggle {
            display: none;
        }
        
        .clean-grants-grid {
            grid-template-columns: 1fr;
        }
        
        /* Mobile Sidebar */
        .clean-sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--white);
            z-index: 1000;
            transition: left 0.3s ease;
            overflow-y: auto;
        }
        
        .clean-sidebar.active {
            left: 0;
        }
        
        .clean-filter-close {
            display: flex;
        }
    }
    
    @media (max-width: 480px) {
        .clean-header {
            padding: var(--space-6) 0;
        }
        
        .clean-title {
            font-size: 1.5rem;
        }
        
        .clean-subtitle {
            font-size: 0.9375rem;
        }
        
        .clean-search-input {
            padding: var(--space-3) var(--space-3) var(--space-3) 2.5rem;
            font-size: 0.9375rem;
        }
        
        .clean-search-icon {
            left: var(--space-3);
        }
        
        .clean-search-clear {
            right: var(--space-3);
        }
        
        .clean-filter-pill {
            font-size: 0.8125rem;
            padding: var(--space-2) var(--space-3);
        }
    }
    
    /* ===== LOADING OVERLAY ===== */
    .clean-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: var(--radius-lg);
    }
    
    .clean-loading-overlay .clean-spinner {
        width: 2rem;
        height: 2rem;
    }
    
    /* ===== UTILITY CLASSES ===== */
    .clean-hidden {
        display: none !important;
    }
    
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    
    .clean-filter-count-active {
        animation: pulse 0.5s ease-in-out;
    }
    
    /* ===== FILTER MORE BUTTON ===== */
    .clean-filter-more-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
        transition: var(--transition);
        padding: var(--space-2);
        border-radius: var(--radius-md);
        width: 100%;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: var(--primary);
        font-weight: 500;
    }
    
    .clean-filter-more-btn:hover {
        background: var(--gray-50);
        color: var(--primary-dark);
    }
    
    .clean-filter-more-item {
        transition: opacity 0.3s ease, max-height 0.3s ease;
    }
    
    .clean-filter-more-item.hidden {
        display: none;
    }
    
    /* ===== FOCUS STYLES ===== */
    button:focus-visible,
    input:focus-visible,
    select:focus-visible,
    a:focus-visible {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    
    /* ===== FILTER ACTIONS ===== */
    .clean-filter-actions {
        padding: var(--space-4) 0;
        border-top: 1px solid var(--gray-200);
        margin-top: var(--space-4);
    }
    
    .clean-reset-button {
        width: 100%;
        padding: var(--space-3) var(--space-4);
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
    }
    
    .clean-reset-button:hover {
        background: var(--gray-200);
        border-color: var(--gray-400);
    }
    
    .clean-reset-button:active {
        transform: translateY(1px);
    }
    
    /* ===== LOADING STATES ===== */
    .clean-filter-loading {
        opacity: 0.6;
        pointer-events: none;
        position: relative;
    }
    
    .clean-filter-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        border: 2px solid var(--gray-300);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translate(-50%, -50%);
    }
    
    /* ===== ENHANCED ANIMATIONS ===== */
    .clean-filter-pill {
        animation: fadeInUp 0.3s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .clean-grant-card {
        animation: slideIn 0.4s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* ===== ENHANCED RESPONSIVE DESIGN ===== */
    @media (max-width: 640px) {
        .clean-filters {
            gap: var(--space-2);
            padding: var(--space-2) 0;
        }
        
        .clean-filter-pill {
            font-size: 0.8125rem;
            padding: var(--space-2) var(--space-3);
        }
        
        .clean-filter-pill i {
            font-size: 0.6875rem;
        }
        
        .clean-controls-inner {
            gap: var(--space-3);
        }
    }
    
    /* ===== ACCESSIBILITY ENHANCEMENTS ===== */
    @media (prefers-reduced-motion: reduce) {
        .clean-filter-pill,
        .clean-grant-card,
        .clean-loading-overlay .clean-spinner {
            animation: none;
        }
        
        .clean-filter-pill::before {
            display: none;
        }
        
        * {
            transition: none !important;
        }
    }
    
    @media (prefers-color-scheme: dark) {
        :root {
            --gray-50: #0f0f0f;
            --gray-100: #1a1a1a;
            --gray-200: #2a2a2a;
            --gray-300: #3a3a3a;
            --white: #1f1f1f;
        }
    }
    
    /* ===== FILTER COUNT INDICATOR ===== */
    .clean-filter-count {
        background: var(--primary);
        color: var(--white);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0 var(--space-2);
        border-radius: var(--radius-sm);
        min-width: 1.25rem;
        text-align: center;
        animation: pulse 0.5s ease-in-out;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .clean-filter-pill.active .clean-filter-count {
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* ===== FILTER LIST CONTAINER WITH SCROLL ===== */
    .clean-filter-list-container {
        max-height: 300px;
        overflow-y: auto;
        margin-bottom: var(--space-3);
        padding-right: var(--space-1);
    }
    
    .clean-filter-list-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .clean-filter-list-container::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: var(--radius-sm);
    }
    
    .clean-filter-list-container::-webkit-scrollbar-thumb {
        background: var(--gray-400);
        border-radius: var(--radius-sm);
        transition: var(--transition);
    }
    
    .clean-filter-list-container::-webkit-scrollbar-thumb:hover {
        background: var(--gray-600);
    }
    
    /* Firefox scrollbar */
    .clean-filter-list-container {
        scrollbar-width: thin;
        scrollbar-color: var(--gray-400) var(--gray-100);
    }
    
    /* Scroll fade indicators */
    .clean-filter-group {
        position: relative;
    }
    
    .clean-filter-group::after {
        content: '';
        position: absolute;
        bottom: 60px;
        left: 0;
        right: 20px;
        height: 20px;
        background: linear-gradient(transparent, var(--white));
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .clean-filter-group.has-scroll::after {
        opacity: 1;
    }
    
    /* Enhanced filter options for better scrolling */
    .clean-filter-option {
        padding: var(--space-3) var(--space-2);
        margin-bottom: 2px;
        border-radius: var(--radius-md);
    }
    
    .clean-filter-option:hover {
        background: var(--gray-50);
    }
    
    /* ===== カーセンサー風 地域階層フィルター ===== */
    .region-hierarchy-filter {
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        margin-bottom: var(--space-6);
    }
    
    .region-tabs-container {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-2);
        margin-bottom: var(--space-4);
        padding-bottom: var(--space-4);
        border-bottom: 1px solid var(--gray-200);
    }
    
    .region-tab {
        padding: var(--space-2) var(--space-3);
        background: var(--white);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--gray-700);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    .region-tab:hover {
        background: var(--gray-100);
        border-color: var(--gray-400);
        transform: translateY(-1px);
    }
    
    .region-tab.active {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
        font-weight: 600;
    }
    
    .filter-sub-title {
        font-size: 0.6875rem;
        font-weight: 700;
        color: var(--gray-700);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: var(--space-3);
        margin-top: var(--space-4);
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    
    .filter-hint {
        font-size: 0.625rem;
        font-weight: 400;
        color: var(--gray-500);
        text-transform: none;
        letter-spacing: normal;
        margin-left: auto;
    }
    
    .no-municipality-message {
        padding: var(--space-3);
        text-align: center;
        color: var(--gray-500);
        font-size: 0.75rem;
        background: var(--gray-50);
        border-radius: var(--radius-md);
        margin-top: var(--space-2);
    }
    
    .filter-sub-title::before {
        content: '';
        width: 3px;
        height: 12px;
        background: var(--primary);
        border-radius: 2px;
    }
    
    .prefecture-selection-container,
    .municipality-selection-container {
        margin-top: var(--space-3);
    }
    
    .prefecture-list,
    .municipality-list {
        max-height: 200px;
        overflow-y: auto;
        padding-right: var(--space-2);
    }
    
    .prefecture-option,
    .municipality-option {
        padding: var(--space-2);
        margin-bottom: 2px;
        border-radius: var(--radius-sm);
        transition: all 0.15s ease;
    }
    
    .prefecture-option:hover,
    .municipality-option:hover {
        background: var(--gray-100);
    }
    
    .prefecture-option input:checked + .clean-filter-label,
    .municipality-option input:checked + .clean-filter-label {
        font-weight: 600;
        color: var(--primary);
    }
    
    /* スクロールバーのスタイリング */
    .prefecture-list::-webkit-scrollbar,
    .municipality-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .prefecture-list::-webkit-scrollbar-track,
    .municipality-list::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }
    
    .prefecture-list::-webkit-scrollbar-thumb,
    .municipality-list::-webkit-scrollbar-thumb {
        background: var(--gray-400);
        border-radius: 3px;
    }
    
    .prefecture-list::-webkit-scrollbar-thumb:hover,
    .municipality-list::-webkit-scrollbar-thumb:hover {
        background: var(--gray-600);
    }
    
    /* 選択中の地域のハイライト */
    .region-tab.active::before {
        content: '✓ ';
        margin-right: 2px;
    }
    
    /* アニメーション */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .prefecture-selection-container,
    .municipality-selection-container {
        animation: slideIn 0.3s ease-out;
    }
    
    /* レスポンシブ対応 */
    @media (max-width: 768px) {
        .region-tabs-container {
            gap: var(--space-1);
        }
        
        .region-tab {
            font-size: 0.75rem;
            padding: var(--space-1) var(--space-2);
        }
        
        .prefecture-list,
        .municipality-list {
            max-height: 150px;
        }
    }
    
    /* ===== Font Awesome カテゴリチップ（白黒スタイリッシュデザイン） ===== */
    .category-filter-group {
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
        border-radius: var(--radius-xl);
        padding: var(--space-5);
        margin-bottom: var(--space-6);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    
    .category-grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: var(--space-3);
        margin-bottom: var(--space-3);
    }
    
    .category-chip-fa {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: var(--space-4);
        background: var(--white);
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-lg);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
        min-height: 100px;
        position: relative;
        overflow: hidden;
    }
    
    /* ホバー時のグラデーションエフェクト */
    .category-chip-fa::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent, 
            rgba(0, 0, 0, 0.02), 
            transparent
        );
        transition: left 0.6s ease;
    }
    
    .category-chip-fa:hover::before {
        left: 100%;
    }
    
    /* 通常状態のホバー */
    .category-chip-fa:hover {
        border-color: var(--gray-900);
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    
    .category-chip-fa:hover .category-icon-wrapper {
        transform: scale(1.1);
    }
    
    /* 選択状態 */
    .category-chip-fa.selected {
        background: linear-gradient(135deg, var(--gray-900) 0%, var(--black) 100%);
        border-color: var(--gray-900);
        color: var(--white);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    
    .category-chip-fa.selected::before {
        background: linear-gradient(90deg, 
            transparent, 
            rgba(255, 255, 255, 0.1), 
            transparent
        );
    }
    
    /* アイコンラッパー */
    .category-icon-wrapper {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gray-100);
        border-radius: 50%;
        margin-bottom: var(--space-3);
        transition: all 0.3s ease;
    }
    
    .category-chip-fa.selected .category-icon-wrapper {
        background: rgba(255, 255, 255, 0.15);
        animation: pulse 0.6s ease;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }
    
    /* Font Awesome アイコン */
    .category-fa-icon {
        font-size: 1.5rem;
        color: var(--gray-900);
        transition: all 0.3s ease;
    }
    
    .category-chip-fa.selected .category-fa-icon {
        color: var(--white);
    }
    
    /* カテゴリ名 */
    .category-chip-fa .category-name {
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: var(--space-1);
        color: var(--gray-900);
        transition: all 0.3s ease;
    }
    
    .category-chip-fa.selected .category-name {
        color: var(--white);
        font-weight: 700;
    }
    
    /* カウントバッジ */
    .category-count-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: var(--gray-900);
        color: var(--white);
        font-size: 0.6875rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
        min-width: 24px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .category-chip-fa.selected .category-count-badge {
        background: rgba(255, 255, 255, 0.25);
        color: var(--white);
        backdrop-filter: blur(4px);
    }
    
    /* フォーカス状態（アクセシビリティ） */
    .category-chip-fa:focus-within {
        outline: 3px solid var(--gray-900);
        outline-offset: 2px;
    }
    
    /* AI検索ヒント */
    .ai-search-hint {
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* レスポンシブ: カテゴリグリッド */
    @media (max-width: 1024px) {
        .category-grid-container {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: var(--space-2);
        }
    }
    
    @media (max-width: 768px) {
        .category-filter-group {
            padding: var(--space-4);
        }
        
        .category-grid-container {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: var(--space-2);
        }
        
        .category-chip-fa {
            min-height: 85px;
            padding: var(--space-3);
        }
        
        .category-icon-wrapper {
            width: 40px;
            height: 40px;
            margin-bottom: var(--space-2);
        }
        
        .category-fa-icon {
            font-size: 1.25rem;
        }
        
        .category-chip-fa .category-name {
            font-size: 0.8125rem;
        }
        
        .category-count-badge {
            top: 6px;
            right: 6px;
            font-size: 0.625rem;
            padding: 2px 6px;
        }
    }
    
    @media (max-width: 480px) {
        .category-grid-container {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: var(--space-1);
        }
        
        .category-chip-fa {
            min-height: 75px;
            padding: var(--space-2);
        }
        
        .category-icon-wrapper {
            width: 36px;
            height: 36px;
        }
        
        .category-fa-icon {
            font-size: 1.125rem;
        }
        
        .category-chip-fa .category-name {
            font-size: 0.75rem;
        }
    }
    </style>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body <?php body_class('clean-archive-page'); ?>>

<!-- Header Section -->
<section class="clean-header">
    <div class="clean-container">
        <div class="clean-header-content">
            <h1 class="clean-title">助成金・補助金検索</h1>
            <p class="clean-subtitle">
                <?php 
                if (!empty($search_params['search']) || !empty($search_params['category']) || !empty($search_params['prefecture'])) {
                    echo '検索条件に該当する助成金・補助金を表示しています。最適な制度を見つけてビジネスを成長させましょう。';
                } else {
                    echo '全国の助成金・補助金を簡単検索。あなたにピッタリの制度を見つけてビジネスの成長を支援します。';
                }
                ?>
            </p>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="clean-search-section">
    <div class="clean-container">
        <!-- Search Box -->
        <div class="clean-search-wrapper">
            <div class="clean-search-box">
                <input type="text" 
                       id="clean-search-input" 
                       name="search" 
                       placeholder="🤖 AI検索：「DXを推進したい」「環境に優しい事業」など自然な言葉で検索..." 
                       class="clean-search-input"
                       value="<?php echo esc_attr($search_params['search']); ?>"
                       autocomplete="off">
                <button type="button" id="clean-search-clear" class="clean-search-clear" <?php echo empty($search_params['search']) ? 'style="display:none"' : ''; ?>>
                    ×
                </button>
            </div>
            <div class="ai-search-hint" style="text-align: center; margin-top: 0.5rem; font-size: 0.75rem; color: var(--gray-600);">
                AI機能強化検索：意図を理解して最適な補助金を提案します
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="clean-filters" role="group" aria-label="クイックフィルター">
            <button class="clean-filter-pill <?php echo empty($search_params['status']) && empty($search_params['is_featured']) && empty($search_params['amount']) ? 'active' : ''; ?>" data-filter="all" aria-pressed="<?php echo empty($search_params['status']) && empty($search_params['is_featured']) && empty($search_params['amount']) ? 'true' : 'false'; ?>">
                すべて
            </button>
            <button class="clean-filter-pill <?php echo $search_params['is_featured'] === '1' ? 'active' : ''; ?>" data-filter="featured" aria-pressed="<?php echo $search_params['is_featured'] === '1' ? 'true' : 'false'; ?>">
                おすすめ
            </button>
            <button class="clean-filter-pill <?php echo $search_params['status'] === 'active' ? 'active' : ''; ?>" data-filter="active" aria-pressed="<?php echo $search_params['status'] === 'active' ? 'true' : 'false'; ?>">
                募集中
                <?php if ($stats['active_grants'] > 0): ?>
                <span class="clean-filter-count" aria-label="<?php echo $stats['active_grants']; ?>件"><?php echo $stats['active_grants']; ?></span>
                <?php endif; ?>
            </button>
            <button class="clean-filter-pill <?php echo $search_params['amount'] === '1000-3000' || $search_params['amount'] === '3000+' ? 'active' : ''; ?>" data-filter="large-amount" aria-pressed="<?php echo $search_params['amount'] === '1000-3000' || $search_params['amount'] === '3000+' ? 'true' : 'false'; ?>">
                高額助成金
            </button>
            <button class="clean-filter-pill <?php echo $search_params['amount'] === '0-100' ? 'active' : ''; ?>" data-filter="small-medium" aria-pressed="<?php echo $search_params['amount'] === '0-100' ? 'true' : 'false'; ?>">
                中小規模
            </button>
            <button class="clean-filter-pill" data-filter="upcoming" aria-pressed="false">
                募集予定
            </button>
            <button class="clean-filter-pill" data-filter="deadline-soon" aria-pressed="false">
                締切間近
            </button>
            
            <!-- 提案6: AI Filter Optimization Button -->
            <button class="clean-filter-pill ai-optimize-filter-btn" onclick="openFilterOptimization()" aria-pressed="false" style="background: linear-gradient(135deg, #000 0%, #1a1a1a 100%); color: #fff; border: 2px solid #000;">
                AI最適化
            </button>
        </div>
    </div>
</section>

<!-- Controls Section -->
<section class="clean-controls">
    <div class="clean-container">
        <div class="clean-controls-inner">
            <div class="clean-controls-left">
                <select id="clean-sort-select" class="clean-select">
                    <option value="date_desc" <?php selected($search_params['sort'], 'date_desc'); ?>>新着順</option>
                    <option value="featured_first" <?php selected($search_params['sort'], 'featured_first'); ?>>おすすめ順</option>
                    <option value="amount_desc" <?php selected($search_params['sort'], 'amount_desc'); ?>>金額が高い順</option>
                    <option value="deadline_asc" <?php selected($search_params['sort'], 'deadline_asc'); ?>>締切が近い順</option>
                    <option value="success_rate_desc" <?php selected($search_params['sort'], 'success_rate_desc'); ?>>採択率順</option>
                </select>

                <button id="clean-filter-toggle" class="clean-filter-button" aria-expanded="false" aria-controls="clean-filter-sidebar">
                    詳細フィルター
                    <span id="clean-filter-count" class="clean-filter-count" style="display:none" aria-label="フィルター適用数">0</span>
                </button>
                
                <button id="clean-clear-all-filters" class="clean-filter-button" style="display:none" aria-label="すべてのフィルターをクリア">
                    クリア
                </button>
            </div>

            <div class="clean-controls-right">
                <div class="clean-view-toggle">
                    <button id="clean-grid-view" 
                            class="clean-view-btn <?php echo $search_params['view'] === 'grid' ? 'active' : ''; ?>" 
                            data-view="grid" 
                            title="グリッド表示">
                        グリッド
                    </button>
                    <button id="clean-list-view" 
                            class="clean-view-btn <?php echo $search_params['view'] === 'list' ? 'active' : ''; ?>" 
                            data-view="list" 
                            title="リスト表示">
                        リスト
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="clean-main">
    <div class="clean-container">
        <div class="clean-layout">
            
            <!-- Sidebar Filters -->
            <aside id="clean-filter-sidebar" class="clean-sidebar" role="complementary" aria-labelledby="filter-title">
                <div class="clean-filter-card">
                    <div class="clean-filter-header">
                        <h3 id="filter-title" class="clean-filter-title">詳細フィルター</h3>
                        <button id="clean-filter-close" class="clean-filter-close" aria-label="フィルターパネルを閉じる">
                            ×
                        </button>
                    </div>

                    <div class="clean-filter-body">
                        
                        <!-- Special Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">特別条件</h4>
                            <label class="clean-filter-option">
                                <input type="checkbox" 
                                       name="is_featured" 
                                       value="1" 
                                       class="clean-filter-checkbox featured-checkbox"
                                       <?php checked($search_params['is_featured'], '1'); ?>>
                                <span class="clean-filter-label">おすすめの助成金のみ</span>
                            </label>
                        </div>
                        
                        <!-- 地域階層フィルター（カーセンサー風） -->
                        <div class="clean-filter-group region-hierarchy-filter">
                            <h4 class="clean-filter-group-title">対象地域</h4>
                            
                            <!-- 地方選択（タブ式） -->
                            <div class="region-tabs-container">
                                <button type="button" class="region-tab <?php echo empty($search_params['region']) ? 'active' : ''; ?>" data-region="all">
                                    全国
                                </button>
                                <?php foreach ($region_mapping as $region_name => $prefectures): ?>
                                <button type="button" class="region-tab <?php echo $search_params['region'] === $region_name ? 'active' : ''; ?>" data-region="<?php echo esc_attr($region_name); ?>">
                                    <?php echo esc_html($region_name); ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- 都道府県選択（選択した地方に応じて表示） -->
                            <?php if (!empty($all_prefectures) && !is_wp_error($all_prefectures)): ?>
                            <div class="prefecture-selection-container">
                                <h5 class="filter-sub-title">都道府県</h5>
                                <div class="clean-filter-list-container prefecture-list">
                                    <?php 
                                    $selected_prefectures = array_filter(explode(',', $search_params['prefecture']));
                                    foreach ($all_prefectures as $prefecture): 
                                        $is_selected = in_array($prefecture->slug, $selected_prefectures);
                                        // 都道府県がどの地方に属するか判定
                                        $pref_region = '';
                                        foreach ($region_mapping as $region => $region_prefs) {
                                            if (in_array($prefecture->name, $region_prefs)) {
                                                $pref_region = $region;
                                                break;
                                            }
                                        }
                                    ?>
                                    <label class="clean-filter-option prefecture-option" data-region="<?php echo esc_attr($pref_region); ?>" style="<?php echo !empty($search_params['region']) && $search_params['region'] !== $pref_region ? 'display:none;' : ''; ?>">
                                        <input type="checkbox" 
                                               name="prefectures[]" 
                                               value="<?php echo esc_attr($prefecture->slug); ?>" 
                                               class="clean-filter-checkbox prefecture-checkbox"
                                               data-prefecture-name="<?php echo esc_attr($prefecture->name); ?>"
                                               <?php checked($is_selected); ?>>
                                        <span class="clean-filter-label"><?php echo esc_html($prefecture->name); ?></span>
                                        <?php if ($prefecture->count > 0): ?>
                                        <span class="clean-filter-count"><?php echo esc_html($prefecture->count); ?></span>
                                        <?php endif; ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- 市町村選択（選択した都道府県に応じて表示） -->
                            <?php if (!empty($all_municipalities) && !is_wp_error($all_municipalities)): ?>
                            <div class="municipality-selection-container" style="<?php echo empty($selected_prefectures) ? 'display:none;' : ''; ?>">
                                <h5 class="filter-sub-title">
                                    市町村
                                    <span class="filter-hint">（都道府県を選択してください）</span>
                                </h5>
                                <div class="clean-filter-list-container municipality-list">
                                    <?php 
                                    $selected_municipalities = array_filter(explode(',', $search_params['municipality']));
                                    foreach ($all_municipalities as $municipality): 
                                        $is_selected = in_array($municipality->slug, $selected_municipalities);
                                        $pref_slug = $municipality_prefecture_map[$municipality->slug] ?? '';
                                    ?>
                                    <label class="clean-filter-option municipality-option" 
                                           data-municipality-slug="<?php echo esc_attr($municipality->slug); ?>"
                                           data-prefecture="<?php echo esc_attr($pref_slug); ?>"
                                           style="display:none;">
                                        <input type="checkbox" 
                                               name="municipalities[]" 
                                               value="<?php echo esc_attr($municipality->slug); ?>" 
                                               class="clean-filter-checkbox municipality-checkbox"
                                               <?php checked($is_selected); ?>>
                                        <span class="clean-filter-label"><?php echo esc_html($municipality->name); ?></span>
                                        <?php if ($municipality->count > 0): ?>
                                        <span class="clean-filter-count"><?php echo esc_html($municipality->count); ?></span>
                                        <?php endif; ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <p class="no-municipality-message" style="display:none;">選択した都道府県に市町村データがありません</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Category Filters (アイコンなし白黒デザイン) -->
                        <?php if (!empty($all_categories) && !is_wp_error($all_categories)): ?>
                        <div class="clean-filter-group category-filter-group">
                            <h4 class="clean-filter-group-title">
                                カテゴリ
                            </h4>
                            <div class="category-grid-container">
                                <?php 
                                $category_limit = 12;
                                $selected_categories = explode(',', $search_params['category']);
                                $category_count = count($all_categories);
                                
                                $has_selected_cat = !empty(array_filter($selected_categories));
                                $show_all_cat_initially = $has_selected_cat;
                                
                                foreach ($all_categories as $index => $category): 
                                    $is_selected_cat = in_array($category->slug, $selected_categories);
                                    $is_hidden = !$show_all_cat_initially && $index >= $category_limit;
                                ?>
                                <label class="category-chip-fa <?php echo $is_selected_cat ? 'selected' : ''; ?> <?php echo $is_hidden ? 'clean-filter-more-item hidden' : ''; ?>" title="<?php echo esc_attr($category->name); ?>">
                                    <input type="checkbox" 
                                           name="categories[]" 
                                           value="<?php echo esc_attr($category->slug); ?>" 
                                           class="clean-filter-checkbox category-checkbox"
                                           <?php checked($is_selected_cat); ?>
                                           style="display: none;">
                                    <span class="category-name"><?php echo esc_html($category->name); ?></span>
                                    <?php if ($category->count > 0): ?>
                                    <span class="category-count-badge"><?php echo esc_html($category->count); ?></span>
                                    <?php endif; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($category_count > $category_limit): ?>
                            <button type="button" class="clean-filter-more-btn" data-target="category">
                                <span class="show-more-text <?php echo $show_all_cat_initially ? 'hidden' : ''; ?>">さらに表示 (+<?php echo $category_count - $category_limit; ?>)</span>
                                <span class="show-less-text <?php echo !$show_all_cat_initially ? 'hidden' : ''; ?>">表示を減らす</span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Amount Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">助成金額</h4>
                            <?php
                            $amount_ranges = [
                                '' => 'すべての金額',
                                '0-100' => '〜100万円',
                                '100-500' => '100〜500万円',
                                '500-1000' => '500〜1000万円',
                                '1000-3000' => '1000〜3000万円',
                                '3000+' => '3000万円以上'
                            ];
                            foreach ($amount_ranges as $value => $label):
                            ?>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="radio" 
                                       name="amount" 
                                       value="<?php echo esc_attr($value); ?>" 
                                       class="clean-filter-radio amount-radio"
                                       <?php checked($search_params['amount'], $value); ?>
                                       aria-describedby="amount-<?php echo esc_attr($value ?: 'all'); ?>-desc">
                                <span class="clean-filter-label" id="amount-<?php echo esc_attr($value ?: 'all'); ?>-desc"><?php echo esc_html($label); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Status Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">募集状況</h4>
                            <?php
                            $status_options = [
                                'active' => '募集中',
                                'upcoming' => '募集予定',
                                'closed' => '募集終了'
                            ];
                            foreach ($status_options as $value => $label):
                                $selected_statuses = explode(',', $search_params['status']);
                                $is_selected = in_array($value, $selected_statuses);
                            ?>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="status[]" 
                                       value="<?php echo esc_attr($value); ?>" 
                                       class="clean-filter-checkbox status-checkbox"
                                       <?php checked($is_selected); ?>
                                       aria-describedby="status-<?php echo esc_attr($value); ?>-desc">
                                <span class="clean-filter-label" id="status-<?php echo esc_attr($value); ?>-desc"><?php echo esc_html($label); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Application Method Filters -->
                        <div class="clean-filter-group">
                            <h4 class="clean-filter-group-title">申請方法</h4>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="application_method[]" 
                                       value="online" 
                                       class="clean-filter-checkbox method-checkbox"
                                       aria-describedby="method-online-desc">
                                <span class="clean-filter-label" id="method-online-desc">
                                    オンライン申請
                                </span>
                            </label>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="application_method[]" 
                                       value="mail" 
                                       class="clean-filter-checkbox method-checkbox"
                                       aria-describedby="method-mail-desc">
                                <span class="clean-filter-label" id="method-mail-desc">
                                    郵送申請
                                </span>
                            </label>
                            <label class="clean-filter-option" tabindex="0">
                                <input type="checkbox" 
                                       name="application_method[]" 
                                       value="direct" 
                                       class="clean-filter-checkbox method-checkbox"
                                       aria-describedby="method-direct-desc">
                                <span class="clean-filter-label" id="method-direct-desc">
                                    持参申請
                                </span>
                            </label>
                        </div>

                        <!-- Reset Filters -->
                        <div class="clean-filter-actions">
                            <button type="button" id="clean-reset-filters" class="clean-reset-button">
                                フィルターをリセット
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="clean-content">
                <!-- Results Header -->
                <div class="clean-results-header">
                    <div class="clean-results-info">
                        <span id="clean-results-count" class="clean-results-number"><?php echo number_format($grants_query->found_posts); ?></span>
                        <span class="clean-results-text">件の助成金</span>
                    </div>
                    <div id="clean-loading" class="clean-loading-indicator clean-hidden">
                        <div class="clean-spinner"></div>
                        <span>更新中...</span>
                    </div>
                </div>

                <!-- Grants Container -->
                <div id="clean-grants-container" class="clean-grants-container">
                    <div id="clean-grants-display">
                        <?php if ($grants_query->have_posts()): ?>
                            <div class="<?php echo $search_params['view'] === 'grid' ? 'clean-grants-grid' : 'clean-grants-list'; ?>">
                                <?php
                                while ($grants_query->have_posts()):
                                    $grants_query->the_post();
                                    
                                    $GLOBALS['current_view'] = $search_params['view'];
                                    $GLOBALS['user_favorites'] = $user_favorites;
                                    
                                    get_template_part('template-parts/grant-card-unified');
                                endwhile;
                                ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($grants_query->max_num_pages > 1): ?>
                            <div class="clean-pagination">
                                <?php
                                $pagination_args = [
                                    'total' => $grants_query->max_num_pages,
                                    'current' => max(1, $search_params['page']),
                                    'format' => '?paged=%#%',
                                    'prev_text' => '&laquo;',
                                    'next_text' => '&raquo;',
                                    'type' => 'array',
                                    'end_size' => 2,
                                    'mid_size' => 2
                                ];
                                
                                $pagination_links = paginate_links($pagination_args);
                                if ($pagination_links) {
                                    foreach ($pagination_links as $link) {
                                        $link = str_replace('class="page-numbers', 'class="clean-page-btn page-numbers', $link);
                                        $link = str_replace('class="clean-page-btn page-numbers current', 'class="clean-page-btn page-numbers current', $link);
                                        echo $link;
                                    }
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="clean-no-results">
                                <div class="clean-no-results-icon">
                                    ×
                                </div>
                                <h3 class="clean-no-results-title">該当する助成金が見つかりませんでした</h3>
                                <p class="clean-no-results-text">検索条件を変更して再度お試しください。</p>
                                <button id="clean-reset-search" class="clean-reset-button">
                                    検索をリセット
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<!-- JavaScript -->
<script>
/**
 * Clean Grant Archive JavaScript
 * シンプルでクリーンなデザイン用の機能
 */
(function() {
    'use strict';
    
    // Configuration
    const config = {
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('gi_ajax_nonce'); ?>',
        debounceDelay: 300,
        searchDelay: 500
    };
    
    // State Management
    const state = {
        currentView: '<?php echo $search_params['view']; ?>',
        currentPage: <?php echo $search_params['page']; ?>,
        isLoading: false,
        filters: {
            search: '<?php echo esc_js($search_params['search']); ?>',
            categories: <?php echo json_encode(array_filter(explode(',', $search_params['category']))); ?>,
            prefectures: <?php echo json_encode(array_filter(explode(',', $search_params['prefecture']))); ?>,
            municipalities: <?php echo json_encode(array_filter(explode(',', $search_params['municipality']))); ?>,
            region: '<?php echo esc_js($search_params['region']); ?>',
            amount: '<?php echo esc_js($search_params['amount']); ?>',
            status: <?php echo json_encode(array_filter(explode(',', $search_params['status']))); ?>,
            is_featured: '<?php echo esc_js($search_params['is_featured']); ?>',
            sort: '<?php echo esc_js($search_params['sort']); ?>'
        }
    };
    
    // DOM Elements
    const elements = {};
    
    // Timers
    let debounceTimer = null;
    let searchTimer = null;
    
    /**
     * Initialize
     */
    function init() {
        cacheElements();
        bindEvents();
        updateFilterCount();
        initializeCardInteractions();
    }
    
    /**
     * Cache DOM elements
     */
    function cacheElements() {
        const ids = [
            'clean-search-input', 'clean-search-clear',
            'clean-sort-select', 'clean-filter-toggle', 'clean-filter-sidebar',
            'clean-filter-close', 'clean-grid-view', 'clean-list-view',
            'clean-reset-search', 'clean-results-count', 'clean-loading',
            'clean-grants-container', 'clean-grants-display', 'clean-filter-count'
        ];
        
        ids.forEach(id => {
            elements[id.replace(/-/g, '_')] = document.getElementById(id);
        });
        
        elements.quickFilters = document.querySelectorAll('.clean-filter-pill');
        elements.filterCheckboxes = document.querySelectorAll('.clean-filter-checkbox');
        elements.filterRadios = document.querySelectorAll('.clean-filter-radio');
    }
    
    /**
     * Bind events
     */
    function bindEvents() {
        // Search
        if (elements.clean_search_input) {
            elements.clean_search_input.addEventListener('input', handleSearchInput);
            elements.clean_search_input.addEventListener('keypress', handleSearchKeypress);
        }
        
        if (elements.clean_search_clear) {
            elements.clean_search_clear.addEventListener('click', handleSearchClear);
        }
        
        // Sort
        if (elements.clean_sort_select) {
            elements.clean_sort_select.addEventListener('change', handleSortChange);
        }
        
        // Filter toggle
        if (elements.clean_filter_toggle) {
            elements.clean_filter_toggle.addEventListener('click', toggleFilterSidebar);
        }
        
        if (elements.clean_filter_close) {
            elements.clean_filter_close.addEventListener('click', closeFilterSidebar);
        }
        
        // View switcher
        if (elements.clean_grid_view) {
            elements.clean_grid_view.addEventListener('click', () => switchView('grid'));
        }
        
        if (elements.clean_list_view) {
            elements.clean_list_view.addEventListener('click', () => switchView('list'));
        }
        
        // Quick filters
        elements.quickFilters.forEach(filter => {
            filter.addEventListener('click', handleQuickFilter);
        });
        
        // Filter inputs
        [...elements.filterCheckboxes, ...elements.filterRadios].forEach(input => {
            input.addEventListener('change', handleFilterChange);
        });
        
        // Reset
        if (elements.clean_reset_search) {
            elements.clean_reset_search.addEventListener('click', resetAllFilters);
        }
        
        // Clear all filters button
        const clearAllBtn = document.getElementById('clean-clear-all-filters');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', resetAllFilters);
        }
        
        // Reset filters button in sidebar
        const resetFiltersBtn = document.getElementById('clean-reset-filters');
        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', resetAllFilters);
        }
        
        // Pagination
        document.addEventListener('click', handlePaginationClick);
        
        // 地域階層フィルター
        document.querySelectorAll('.region-tab').forEach(tab => {
            tab.addEventListener('click', handleRegionTabClick);
        });
        
        document.querySelectorAll('.prefecture-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', handlePrefectureChange);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', handleKeyboardShortcuts);
        
        // More filters toggle
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.clean-filter-more-btn');
            if (btn) {
                handleMoreFiltersToggle(e);
            }
        });
        
        // Initialize scroll indicators
        initScrollIndicators();
    }
    
    /**
     * Handle search input
     */
    function handleSearchInput(e) {
        state.filters.search = e.target.value;
        elements.clean_search_clear.style.display = e.target.value ? 'block' : 'none';
        
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            loadGrants();
        }, config.searchDelay);
    }
    
    /**
     * Handle search keypress
     */
    function handleSearchKeypress(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadGrants();
        }
    }
    
    /**
     * Handle search clear
     */
    function handleSearchClear() {
        elements.clean_search_input.value = '';
        elements.clean_search_clear.style.display = 'none';
        state.filters.search = '';
        loadGrants();
    }
    
    /**
     * Handle sort change
     */
    function handleSortChange(e) {
        state.filters.sort = e.target.value;
        state.currentPage = 1;
        loadGrants();
    }
    
    /**
     * Handle quick filter
     */
    function handleQuickFilter(e) {
        const filter = e.currentTarget.dataset.filter;
        
        elements.quickFilters.forEach(f => f.classList.remove('active'));
        e.currentTarget.classList.add('active');
        
        resetFiltersState();
        
        switch(filter) {
            case 'featured':
                state.filters.is_featured = '1';
                break;
            case 'active':
                state.filters.status = ['active'];
                break;
            case 'large-amount':
                state.filters.amount = '1000-3000';
                break;
            case 'small-medium':
                state.filters.amount = '0-100';
                break;
            case 'upcoming':
                state.filters.status = ['upcoming'];
                break;
            case 'deadline-soon':
                // 締切間近のロジックを追加（例：1週間以内）
                state.filters.deadline_range = '1week';
                break;
            default:
                break;
        }
        
        state.currentPage = 1;
        updateFilterCount();
        loadGrants();
    }
    
    /**
     * Handle filter change
     */
    function handleFilterChange() {
        updateFiltersFromForm();
        updateFilterCount();
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.currentPage = 1;
            loadGrants();
        }, config.debounceDelay);
    }
    
    /**
     * Update filters from form
     */
    function updateFiltersFromForm() {
        state.filters.categories = Array.from(
            document.querySelectorAll('.category-checkbox:checked')
        ).map(cb => cb.value);
        
        state.filters.prefectures = Array.from(
            document.querySelectorAll('.prefecture-checkbox:checked')
        ).map(cb => cb.value);
        
        state.filters.municipalities = Array.from(
            document.querySelectorAll('.municipality-checkbox:checked')
        ).map(cb => cb.value);
        
        const featuredCheckbox = document.querySelector('.featured-checkbox:checked');
        state.filters.is_featured = featuredCheckbox ? '1' : '';
        
        const amountRadio = document.querySelector('.amount-radio:checked');
        state.filters.amount = amountRadio ? amountRadio.value : '';
    }
    
    /**
     * Update filter count
     */
    function updateFilterCount() {
        const count = 
            state.filters.categories.length +
            state.filters.prefectures.length +
            state.filters.municipalities.length +
            (state.filters.region && state.filters.region !== 'all' ? 1 : 0) +
            (state.filters.amount ? 1 : 0) +
            state.filters.status.length +
            (state.filters.is_featured ? 1 : 0);
        
        if (elements.clean_filter_count) {
            elements.clean_filter_count.textContent = count;
            elements.clean_filter_count.style.display = count > 0 ? 'inline-block' : 'none';
            elements.clean_filter_count.setAttribute('aria-label', `${count}個のフィルターが適用中`);
        }
        
        if (elements.clean_filter_toggle) {
            elements.clean_filter_toggle.classList.toggle('has-filters', count > 0);
            elements.clean_filter_toggle.setAttribute('aria-expanded', 
                elements.clean_filter_sidebar?.classList.contains('active') ? 'true' : 'false');
        }
        
        // Clear all button visibility
        const clearAllBtn = document.getElementById('clean-clear-all-filters');
        if (clearAllBtn) {
            clearAllBtn.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        
        // Add visual feedback
        if (count > 0) {
            elements.clean_filter_count?.classList.add('clean-filter-count-active');
        } else {
            elements.clean_filter_count?.classList.remove('clean-filter-count-active');
        }
    }
    
    /**
     * Switch view
     */
    function switchView(view) {
        if (state.currentView === view) return;
        
        state.currentView = view;
        
        elements.clean_grid_view.classList.toggle('active', view === 'grid');
        elements.clean_list_view.classList.toggle('active', view === 'list');
        
        loadGrants();
    }
    
    /**
     * Toggle filter sidebar
     */
    function toggleFilterSidebar() {
        const isActive = elements.clean_filter_sidebar.classList.contains('active');
        
        if (isActive) {
            closeFilterSidebar();
        } else {
            elements.clean_filter_sidebar.classList.add('active');
            elements.clean_filter_toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            
            // フォーカス管理
            const firstInput = elements.clean_filter_sidebar.querySelector('input, button');
            if (firstInput) {
                firstInput.focus();
            }
        }
    }
    
    /**
     * Close filter sidebar
     */
    function closeFilterSidebar() {
        elements.clean_filter_sidebar.classList.remove('active');
        elements.clean_filter_toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        
        // フォーカスを戻す
        elements.clean_filter_toggle.focus();
    }
    
    /**
     * Reset all filters
     */
    function resetAllFilters() {
        resetFiltersState();
        
        elements.filterCheckboxes.forEach(cb => cb.checked = false);
        elements.filterRadios.forEach(rb => rb.checked = rb.value === '');
        
        elements.quickFilters.forEach(f => f.classList.remove('active'));
        document.querySelector('.clean-filter-pill[data-filter="all"]')?.classList.add('active');
        
        if (elements.clean_search_input) {
            elements.clean_search_input.value = '';
        }
        if (elements.clean_search_clear) {
            elements.clean_search_clear.style.display = 'none';
        }
        
        state.currentPage = 1;
        updateFilterCount();
        loadGrants();
    }
    
    /**
     * Reset filters state
     */
    function resetFiltersState() {
        state.filters = {
            search: '',
            categories: [],
            prefectures: [],
            municipalities: [],
            region: '',
            amount: '',
            status: [],
            is_featured: '',
            sort: state.filters.sort
        };
        
        // 地域タブをリセット
        document.querySelectorAll('.region-tab').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.region === 'all') {
                tab.classList.add('active');
            }
        });
        
        // 市町村コンテナを非表示
        const municipalityContainer = document.querySelector('.municipality-selection-container');
        if (municipalityContainer) {
            municipalityContainer.style.display = 'none';
        }
    }
    
    /**
     * Handle pagination
     */
    function handlePaginationClick(e) {
        if (e.target.classList.contains('clean-page-btn') || e.target.closest('.clean-page-btn')) {
            e.preventDefault();
            
            const btn = e.target.classList.contains('clean-page-btn') ? e.target : e.target.closest('.clean-page-btn');
            const href = btn.getAttribute('href');
            
            if (href) {
                const url = new URL(href, window.location.origin);
                const page = parseInt(url.searchParams.get('paged')) || 1;
                
                if (page !== state.currentPage) {
                    state.currentPage = page;
                    loadGrants();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        }
    }
    
    /**
     * Handle keyboard shortcuts
     */
    function handleKeyboardShortcuts(e) {
        if (e.key === 'Escape') {
            closeFilterSidebar();
        }
    }
    
    /**
     * Handle region tab click (カーセンサー風の地方選択)
     */
    function handleRegionTabClick(e) {
        const tab = e.currentTarget;
        const region = tab.dataset.region;
        
        // すべてのタブから active を削除
        document.querySelectorAll('.region-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        // 都道府県の表示を更新
        const prefectureOptions = document.querySelectorAll('.prefecture-option');
        
        if (region === 'all') {
            // 全国選択時：すべての都道府県を表示
            prefectureOptions.forEach(option => {
                option.style.display = '';
            });
        } else {
            // 特定地方選択時：その地方の都道府県のみ表示
            prefectureOptions.forEach(option => {
                const optionRegion = option.dataset.region;
                option.style.display = optionRegion === region ? '' : 'none';
            });
        }
        
        // 状態を更新
        state.filters.region = region === 'all' ? '' : region;
        updateFilterCount();
    }
    
    /**
     * Handle prefecture change (都道府県選択時に市町村を更新)
     */
    function handlePrefectureChange(e) {
        const checkbox = e.currentTarget;
        const prefectureName = checkbox.dataset.prefectureName;
        
        // 選択された都道府県を取得
        const selectedPrefectures = Array.from(
            document.querySelectorAll('.prefecture-checkbox:checked')
        ).map(cb => cb.dataset.prefectureName);
        
        // 市町村コンテナの表示/非表示と絞り込み
        const municipalityContainer = document.querySelector('.municipality-selection-container');
        const noMunicipalityMessage = municipalityContainer?.querySelector('.no-municipality-message');
        
        if (municipalityContainer) {
            if (selectedPrefectures.length > 0) {
                municipalityContainer.style.display = '';
                
                // 選択された都道府県のslugを取得
                const selectedPrefSlugs = Array.from(
                    document.querySelectorAll('.prefecture-checkbox:checked')
                ).map(cb => cb.value);
                
                // 市町村を絞り込んで表示
                const municipalityOptions = document.querySelectorAll('.municipality-option');
                let visibleCount = 0;
                
                municipalityOptions.forEach(option => {
                    const prefSlug = option.dataset.prefecture;
                    if (selectedPrefSlugs.includes(prefSlug)) {
                        option.style.display = '';
                        visibleCount++;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                // 市町村が1つも見つからない場合のメッセージ
                if (noMunicipalityMessage) {
                    if (visibleCount === 0) {
                        noMunicipalityMessage.style.display = 'block';
                    } else {
                        noMunicipalityMessage.style.display = 'none';
                    }
                }
            } else {
                municipalityContainer.style.display = 'none';
            }
        }
        
        updateFiltersFromForm();
        updateFilterCount();
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.currentPage = 1;
            loadGrants();
        }, config.debounceDelay);
    }
    
    /**
     * Load grants via AJAX
     */
    async function loadGrants() {
        if (state.isLoading) return;
        
        state.isLoading = true;
        showLoading();
        
        try {
            const response = await fetch(config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'gi_load_grants',
                    nonce: config.nonce,
                    search: state.filters.search,
                    categories: JSON.stringify(state.filters.categories),
                    prefectures: JSON.stringify(state.filters.prefectures),
                    municipalities: JSON.stringify(state.filters.municipalities),
                    region: state.filters.region,
                    amount: state.filters.amount,
                    status: JSON.stringify(state.filters.status),
                    only_featured: state.filters.is_featured,
                    sort: state.filters.sort,
                    view: state.currentView,
                    page: state.currentPage
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                renderGrants(data.data);
                updateURL();
            } else {
                showNoResults();
            }
        } catch (error) {
            console.error('Error loading grants:', error);
            showError();
        } finally {
            state.isLoading = false;
            hideLoading();
        }
    }
    
    /**
     * Render grants
     */
    function renderGrants(data) {
        const { grants, pagination, stats } = data;
        
        if (elements.clean_results_count) {
            elements.clean_results_count.textContent = stats?.total_found ? number_format(stats.total_found) : '0';
        }
        
        if (grants && grants.length > 0) {
            const containerClass = state.currentView === 'grid' ? 'clean-grants-grid' : 'clean-grants-list';
            elements.clean_grants_display.innerHTML = `
                <div class="${containerClass}">
                    ${grants.map(grant => grant.html).join('')}
                </div>
            `;
            
            initializeCardInteractions();
        } else {
            showNoResults();
        }
    }
    
    /**
     * Initialize card interactions
     */
    function initializeCardInteractions() {
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', handleFavoriteClick);
        });
    }
    
    /**
     * Handle favorite click
     */
    async function handleFavoriteClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = e.currentTarget;
        const postId = btn.dataset.postId;
        
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';
        
        try {
            const response = await fetch(config.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'gi_toggle_favorite',
                    nonce: config.nonce,
                    post_id: postId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                btn.textContent = data.data.is_favorite ? '♥' : '♡';
                btn.style.color = data.data.is_favorite ? '#dc2626' : '#6b7280';
                
                showNotification(data.data.message, 'success');
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
            showNotification('エラーが発生しました', 'error');
        } finally {
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
    }
    
    /**
     * Show loading
     */
    function showLoading() {
        if (elements.clean_loading) {
            elements.clean_loading.classList.remove('clean-hidden');
        }
        
        const container = elements.clean_grants_container;
        if (container && !container.querySelector('.clean-loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'clean-loading-overlay';
            overlay.innerHTML = `
                <div class="clean-spinner" role="status" aria-label="検索中"></div>
                <span class="sr-only">助成金を検索しています...</span>
            `;
            container.appendChild(overlay);
        }
        
        // フィルターボタンにローディング状態を追加
        elements.quickFilters.forEach(btn => {
            btn.classList.add('clean-filter-loading');
        });
    }
    
    /**
     * Hide loading
     */
    function hideLoading() {
        if (elements.clean_loading) {
            elements.clean_loading.classList.add('clean-hidden');
        }
        
        const overlay = document.querySelector('.clean-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
        
        // フィルターボタンからローディング状態を削除
        elements.quickFilters.forEach(btn => {
            btn.classList.remove('clean-filter-loading');
        });
    }
    
    /**
     * Show no results
     */
    function showNoResults() {
        elements.clean_grants_display.innerHTML = `
            <div class="clean-no-results">
                <div class="clean-no-results-icon">
                    ×
                </div>
                <h3 class="clean-no-results-title">該当する助成金が見つかりませんでした</h3>
                <p class="clean-no-results-text">検索条件を変更して再度お試しください。</p>
                <button class="clean-reset-button" onclick="CleanGrants.resetAllFilters()">
                    検索をリセット
                </button>
            </div>
        `;
    }
    
    /**
     * Show error
     */
    function showError() {
        elements.clean_grants_display.innerHTML = `
            <div class="clean-no-results">
                <div class="clean-no-results-icon">
                    !
                </div>
                <h3 class="clean-no-results-title">エラーが発生しました</h3>
                <p class="clean-no-results-text">しばらく時間をおいて再度お試しください</p>
                <button class="clean-reset-button" onclick="window.location.reload()">
                    ページを再読み込み
                </button>
            </div>
        `;
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: ${type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#000000'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05);
            z-index: 10000;
            font-weight: 500;
            max-width: 300px;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    /**
     * Update URL
     */
    function updateURL() {
        const params = new URLSearchParams();
        
        if (state.filters.search) params.set('s', state.filters.search);
        if (state.filters.categories.length) params.set('category', state.filters.categories.join(','));
        if (state.filters.prefectures.length) params.set('prefecture', state.filters.prefectures.join(','));
        if (state.filters.municipalities.length) params.set('municipality', state.filters.municipalities.join(','));
        if (state.filters.region && state.filters.region !== 'all') params.set('region', state.filters.region);
        if (state.filters.amount) params.set('amount', state.filters.amount);
        if (state.filters.status.length) params.set('status', state.filters.status.join(','));
        if (state.filters.is_featured) params.set('featured', '1');
        if (state.filters.sort !== 'date_desc') params.set('sort', state.filters.sort);
        if (state.currentView !== 'grid') params.set('view', state.currentView);
        if (state.currentPage > 1) params.set('paged', state.currentPage);
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newURL);
    }
    
    /**
     * Handle more filters toggle
     */
    function handleMoreFiltersToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = e.target.closest('.clean-filter-more-btn');
        if (!btn) return;
        
        const filterGroup = btn.closest('.clean-filter-group');
        if (!filterGroup) return;
        
        const moreItems = filterGroup.querySelectorAll('.clean-filter-more-item');
        const showMoreText = btn.querySelector('.show-more-text');
        const showLessText = btn.querySelector('.show-less-text');
        const showMoreIcon = btn.querySelector('.show-more-icon');
        const showLessIcon = btn.querySelector('.show-less-icon');
        
        const isCollapsed = showMoreText && !showMoreText.classList.contains('hidden');
        
        if (isCollapsed) {
            moreItems.forEach(item => item.classList.remove('hidden'));
            if (showMoreText) showMoreText.classList.add('hidden');
            if (showLessText) showLessText.classList.remove('hidden');
            if (showMoreIcon) showMoreIcon.classList.add('hidden');
            if (showLessIcon) showLessIcon.classList.remove('hidden');
        } else {
            moreItems.forEach(item => item.classList.add('hidden'));
            if (showMoreText) showMoreText.classList.remove('hidden');
            if (showLessText) showLessText.classList.add('hidden');
            if (showMoreIcon) showMoreIcon.classList.remove('hidden');
            if (showLessIcon) showLessIcon.classList.add('hidden');
        }
    }
    
    /**
     * Initialize scroll indicators for filter lists
     */
    function initScrollIndicators() {
        document.querySelectorAll('.clean-filter-list-container').forEach(container => {
            const filterGroup = container.closest('.clean-filter-group');
            
            function updateScrollIndicator() {
                const hasScroll = container.scrollHeight > container.clientHeight;
                const isScrolledToBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 5;
                
                if (filterGroup) {
                    filterGroup.classList.toggle('has-scroll', hasScroll && !isScrolledToBottom);
                }
            }
            
            // Initial check
            updateScrollIndicator();
            
            // Update on scroll
            container.addEventListener('scroll', updateScrollIndicator);
            
            // Update on resize
            window.addEventListener('resize', updateScrollIndicator);
            
            // Update when filter items are shown/hidden
            const observer = new MutationObserver(updateScrollIndicator);
            observer.observe(container, { 
                childList: true, 
                subtree: true, 
                attributes: true, 
                attributeFilter: ['class', 'style'] 
            });
        });
    }
    
    /**
     * Format number
     */
    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    // Public API
    window.CleanGrants = {
        resetAllFilters,
        loadGrants,
        switchView
    };
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

// ============================================
// 提案6: AI Filter Optimization
// ============================================

/**
 * AI フィルター最適化モーダルを開く
 */
function openFilterOptimization() {
    // ユーザーの閲覧履歴を分析
    const userHistory = JSON.parse(localStorage.getItem('gi_view_history') || '[]');
    const searchHistory = JSON.parse(localStorage.getItem('gi_search_history') || '[]');
    
    // AIによる推奨フィルター設定を生成
    const recommendations = analyzeUserPatterns(userHistory, searchHistory);
    
    // モーダルを作成
    const modal = document.createElement('div');
    modal.className = 'ai-filter-modal';
    modal.style.cssText = `
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        animation: fadeIn 0.3s ease;
    `;
    
    modal.innerHTML = `
        <div class="ai-filter-content" style="background: #fff; border-radius: 1rem; max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); animation: slideUp 0.3s ease;">
            <div style="padding: 2rem; border-bottom: 2px solid #000;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 2rem;">🤖</span>
                        AIフィルター最適化
                    </h2>
                    <button onclick="closeFilterOptimization()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s;">
                        ✕
                    </button>
                </div>
                <p style="color: #666; margin: 0; font-size: 0.875rem;">
                    あなたの検索パターンを分析し、最適なフィルター設定を提案します
                </p>
            </div>
            
            <div style="padding: 2rem;">
                ${recommendations.length > 0 ? `
                    <div style="margin-bottom: 2rem;">
                        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem;">
                            推奨フィルター設定
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            ${recommendations.map((rec, index) => `
                                <div class="filter-recommendation" style="background: #fafafa; padding: 1.5rem; border-radius: 0.75rem; border: 2px solid #e5e5e5; transition: all 0.3s; cursor: pointer;" onclick="applyRecommendation(${index})">
                                    <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1rem;">
                                        <div style="background: #000; color: #fff; width: 2rem; height: 2rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                                            ${index + 1}
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0 0 0.5rem 0; font-weight: 700; font-size: 0.9375rem;">
                                                ${rec.title}
                                            </h4>
                                            <p style="margin: 0; font-size: 0.8125rem; color: #666; line-height: 1.5;">
                                                ${rec.description}
                                            </p>
                                        </div>
                                        <div style="background: #fbbf24; color: #000; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">
                                            ${rec.confidence}% マッチ
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                        ${rec.filters.map(f => `
                                            <span style="background: #fff; border: 1px solid #000; padding: 0.375rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 0.375rem;">
                                                <i class="fas ${f.icon}"></i>
                                                ${f.label}
                                            </span>
                                        `).join('')}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : `
                    <div style="text-align: center; padding: 3rem 1rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem; font-weight: 700;">検索</div>
                        <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0.5rem;">
                            まだ十分なデータがありません
                        </h3>
                        <p style="color: #666; font-size: 0.875rem; margin-bottom: 2rem;">
                            助成金を検索・閲覧すると、AIが最適な設定を提案できるようになります
                        </p>
                        <button onclick="closeFilterOptimization()" style="background: #000; color: #fff; border: none; padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 700; cursor: pointer;">
                            検索を開始する
                        </button>
                    </div>
                `}
                
                <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f9ff; border-radius: 0.75rem; border-left: 4px solid #2563eb;">
                    <h4 style="margin: 0 0 0.75rem 0; font-weight: 700; font-size: 0.875rem;">
                        分析に基づく推奨理由
                    </h4>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8125rem; color: #333; line-height: 1.6;">
                        <li>閲覧回数: ${userHistory.length}件</li>
                        <li>検索回数: ${searchHistory.length}回</li>
                        <li>よく見るカテゴリ: ${getMostFrequentCategory(userHistory)}</li>
                        <li>平均助成金額: ${getAverageAmount(userHistory)}</li>
                    </ul>
                </div>
            </div>
            
            <div style="padding: 1.5rem 2rem; border-top: 1px solid #e5e5e5; display: flex; gap: 1rem; justify-content: flex-end;">
                <button onclick="closeFilterOptimization()" style="background: #fff; border: 2px solid #000; color: #000; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 700; cursor: pointer; transition: all 0.3s;">
                    キャンセル
                </button>
                <button onclick="clearHistoryAndRecommendations()" style="background: #000; color: #fff; border: 2px solid #000; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 700; cursor: pointer; transition: all 0.3s;">
                    履歴をクリア
                </button>
            </div>
        </div>
        
        <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .filter-recommendation:hover {
            border-color: #000 !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        </style>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Store recommendations globally for apply function
    window.aiFilterRecommendations = recommendations;
}

/**
 * モーダルを閉じる
 */
function closeFilterOptimization() {
    const modal = document.querySelector('.ai-filter-modal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';
        }, 300);
    }
}

/**
 * ユーザーのパターンを分析
 */
function analyzeUserPatterns(history, searches) {
    if (history.length === 0 && searches.length === 0) {
        return [];
    }
    
    const recommendations = [];
    
    // パターン1: 頻繁に見るカテゴリー
    const categoryFreq = {};
    history.forEach(item => {
        if (item.category) {
            categoryFreq[item.category] = (categoryFreq[item.category] || 0) + 1;
        }
    });
    
    const topCategory = Object.keys(categoryFreq).sort((a, b) => categoryFreq[b] - categoryFreq[a])[0];
    
    if (topCategory) {
        recommendations.push({
            title: `${topCategory}に特化した検索`,
            description: `あなたは「${topCategory}」カテゴリを${categoryFreq[topCategory]}回閲覧しています。このカテゴリに絞り込むことをお勧めします。`,
            confidence: 85,
            filters: [
                { icon: 'fa-tag', label: topCategory },
                { icon: 'fa-circle-dot', label: '募集中' }
            ],
            params: { category: topCategory, status: 'active' }
        });
    }
    
    // パターン2: 高額助成金への関心
    const avgAmount = history.reduce((sum, item) => sum + (item.amount || 0), 0) / history.length;
    if (avgAmount > 1000000) {
        recommendations.push({
            title: '高額助成金を優先表示',
            description: `平均${Math.floor(avgAmount / 10000)}万円の助成金を閲覧しています。1000万円以上の高額助成金に絞り込みます。`,
            confidence: 78,
            filters: [
                { icon: 'fa-coins', label: '高額助成金' },
                { icon: 'fa-sort-amount-down', label: '金額順' }
            ],
            params: { amount: '1000-3000', sort: 'amount_desc' }
        });
    }
    
    // パターン3: 締切間近を優先
    if (searches.some(s => s.includes('締切') || s.includes('期限'))) {
        recommendations.push({
            title: '締切間近の助成金を優先',
            description: '締切に関する検索が多いため、期限が迫っている助成金を優先的に表示します。',
            confidence: 72,
            filters: [
                { icon: 'fa-clock', label: '締切間近' },
                { icon: 'fa-calendar-alt', label: '締切順' }
            ],
            params: { deadline: 'soon', sort: 'deadline_asc' }
        });
    }
    
    return recommendations;
}

/**
 * 推奨設定を適用
 */
function applyRecommendation(index) {
    const rec = window.aiFilterRecommendations[index];
    if (!rec) return;
    
    // URLパラメータを構築
    const params = new URLSearchParams(rec.params);
    
    // ページをリロード
    window.location.href = window.location.pathname + '?' + params.toString();
}

/**
 * 履歴をクリア
 */
function clearHistoryAndRecommendations() {
    if (confirm('閲覧履歴と検索履歴をすべて削除しますか？')) {
        localStorage.removeItem('gi_view_history');
        localStorage.removeItem('gi_search_history');
        closeFilterOptimization();
        
        // トースト通知
        const toast = document.createElement('div');
        toast.textContent = '✓ 履歴をクリアしました';
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #000;
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 700;
            z-index: 10001;
            animation: slideUp 0.3s ease;
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}

/**
 * ヘルパー関数
 */
function getMostFrequentCategory(history) {
    const freq = {};
    history.forEach(item => {
        if (item.category) freq[item.category] = (freq[item.category] || 0) + 1;
    });
    const top = Object.keys(freq).sort((a, b) => freq[b] - freq[a])[0];
    return top || '未分析';
}

function getAverageAmount(history) {
    const amounts = history.filter(item => item.amount > 0).map(item => item.amount);
    if (amounts.length === 0) return '未分析';
    const avg = amounts.reduce((sum, val) => sum + val, 0) / amounts.length;
    return Math.floor(avg / 10000) + '万円';
}
</script>

<?php get_footer(); ?>