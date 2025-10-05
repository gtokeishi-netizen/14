<?php
/**
 * Grant Card Unified - Clean & Professional Edition
 * template-parts/grant-card-unified.php
 * 
 * シンプルでスタイリッシュな統一カードテンプレート
 * 機能は完全保持、デザインをクリーンに刷新
 * 
 * @package Grant_Insight_Clean
 * @version 10.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// グローバル変数から必要データを取得
global $post, $current_view, $display_mode;

$post_id = get_the_ID();
if (!$post_id) return;

// 表示モードの判定
$display_mode = $display_mode ?? (isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'card');
$view_class = 'grant-view-' . $display_mode;

// 基本データ取得
$title = get_the_title($post_id);
$permalink = get_permalink($post_id);
$excerpt = get_the_excerpt($post_id);

// 📋 完全31列対応 ACFフィールド取得（single-grant.phpと統一）
$grant_data = array(
    // 基本情報 (A-G列)
    'organization' => get_field('organization', $post_id) ?: '',
    'organization_type' => get_field('organization_type', $post_id) ?: '',
    
    // 金額情報 (H-I列)
    'max_amount' => get_field('max_amount', $post_id) ?: '',
    'max_amount_numeric' => intval(get_field('max_amount_numeric', $post_id)),
    'min_amount' => intval(get_field('min_amount', $post_id)),
    'amount_note' => get_field('amount_note', $post_id) ?: '',
    
    // 期間・締切情報 (J-K列)
    'deadline' => get_field('deadline', $post_id) ?: '',
    'deadline_date' => get_field('deadline_date', $post_id) ?: '',
    'application_period' => get_field('application_period', $post_id) ?: '',
    'deadline_note' => get_field('deadline_note', $post_id) ?: '',
    
    // 申請・組織情報 (L-Q列)
    'grant_target' => get_field('grant_target', $post_id) ?: '',
    'application_method' => get_field('application_method', $post_id) ?: '',
    'contact_info' => get_field('contact_info', $post_id) ?: '',
    'official_url' => get_field('official_url', $post_id) ?: '',
    
    // 地域・ステータス情報 (R-S列)
    'regional_limitation' => get_field('regional_limitation', $post_id) ?: '',
    'application_status' => get_field('application_status', $post_id) ?: 'open',
    
    // ★ 新規拡張フィールド (X-AD列) - 31列対応
    'external_link' => get_field('external_link', $post_id) ?: '',
    'region_notes' => get_field('region_notes', $post_id) ?: '',
    'required_documents' => get_field('required_documents', $post_id) ?: '',
    'adoption_rate' => floatval(get_field('adoption_rate', $post_id)),
    'application_difficulty' => get_field('application_difficulty', $post_id) ?: 'normal',
    'target_expenses' => get_field('target_expenses', $post_id) ?: '',
    'subsidy_rate' => get_field('subsidy_rate', $post_id) ?: '',
    
    // 管理・統計情報
    'is_featured' => get_field('is_featured', $post_id) ?: false,
    'views_count' => intval(get_field('views_count', $post_id)),
    'priority_order' => intval(get_field('priority_order', $post_id)) ?: 100,
    
    // AI関連
    'ai_summary' => get_field('ai_summary', $post_id) ?: get_post_meta($post_id, 'ai_summary', true),
);

// 個別変数に展開（後方互換性のため）
$ai_summary = $grant_data['ai_summary'];
$max_amount = $grant_data['max_amount'];
$max_amount_numeric = $grant_data['max_amount_numeric'];
$application_status = $grant_data['application_status'];
$organization = $grant_data['organization'];
$grant_target = $grant_data['grant_target'];
$subsidy_rate = $grant_data['subsidy_rate'];
$grant_difficulty = $grant_data['application_difficulty'];
$grant_success_rate = $grant_data['adoption_rate'];
$official_url = $grant_data['official_url'];
$eligible_expenses = $grant_data['target_expenses'];
$application_method = $grant_data['application_method'];
$required_documents = $grant_data['required_documents'];
$contact_info = $grant_data['contact_info'];
$is_featured = $grant_data['is_featured'];
$priority_order = $grant_data['priority_order'];
$application_period = $grant_data['application_period'];

// 締切日の計算（single-grant.phpと完全統一）
$deadline_info_text = '';
$deadline_class = '';
$days_remaining = 0;
$deadline_timestamp = 0;
$deadline_formatted = '';

if ($grant_data['deadline_date']) {
    $deadline_timestamp = strtotime($grant_data['deadline_date']);
    if ($deadline_timestamp && $deadline_timestamp > 0) {
        $deadline_formatted = date('Y年n月j日', $deadline_timestamp);
        $current_time = current_time('timestamp');
        $days_remaining = ceil(($deadline_timestamp - $current_time) / (60 * 60 * 24));
    }
} elseif ($grant_data['deadline']) {
    $deadline_formatted = $grant_data['deadline'];
    $deadline_timestamp = strtotime($grant_data['deadline']);
    if ($deadline_timestamp && $deadline_timestamp > 0) {
        $current_time = current_time('timestamp');
        $days_remaining = ceil(($deadline_timestamp - $current_time) / (60 * 60 * 24));
    }
}

// タクソノミーデータ（single-grant.phpと統一）
$taxonomies = array(
    'categories' => get_the_terms($post_id, 'grant_category'),
    'prefectures' => get_the_terms($post_id, 'grant_prefecture'),
    'municipalities' => get_the_terms($post_id, 'grant_municipality'),
    'tags' => get_the_tags($post_id),
);

$main_category = ($taxonomies['categories'] && !is_wp_error($taxonomies['categories'])) ? $taxonomies['categories'][0]->name : '';
$main_prefecture = ($taxonomies['prefectures'] && !is_wp_error($taxonomies['prefectures'])) ? $taxonomies['prefectures'][0] : null;
$prefecture = $main_prefecture ? $main_prefecture->name : '全国';
$main_industry = '';

// 金額フォーマット（single-grant.phpと完全同一）
$formatted_amount = '';
$max_amount_yen = $grant_data['max_amount_numeric'];
if ($max_amount_yen > 0) {
    if ($max_amount_yen >= 100000000) {
        $formatted_amount = number_format($max_amount_yen / 100000000, 1) . '億円';
    } elseif ($max_amount_yen >= 10000) {
        $formatted_amount = number_format($max_amount_yen / 10000) . '万円';
    } else {
        $formatted_amount = number_format($max_amount_yen) . '円';
    }
} elseif ($grant_data['max_amount']) {
    $formatted_amount = $grant_data['max_amount'];
}
$amount_display = $formatted_amount;

// ステータス表示
$status_labels = array(
    'open' => '募集中',
    'closed' => '募集終了',
    'planned' => '募集予定',
    'suspended' => '一時停止'
);
$status_display = $status_labels[$application_status] ?? '募集中';

// 締切日情報の処理（single-grant.phpと完全統一）
$deadline_info = array();
if ($deadline_timestamp > 0 && $days_remaining > 0) {
    if ($days_remaining <= 0) {
        $deadline_class = 'expired';
        $deadline_info_text = '募集終了';
        $deadline_info = array('class' => 'expired', 'text' => '募集終了');
    } elseif ($days_remaining <= 7) {
        $deadline_class = 'urgent';
        $deadline_info_text = 'あと' . $days_remaining . '日';
        $deadline_info = array('class' => 'urgent', 'text' => '残り'.$days_remaining.'日');
    } elseif ($days_remaining <= 30) {
        $deadline_class = 'warning';
        $deadline_info_text = 'あと' . $days_remaining . '日';
        $deadline_info = array('class' => 'warning', 'text' => '残り'.$days_remaining.'日');
    } else {
        $deadline_info = array('class' => 'normal', 'text' => $deadline_formatted);
    }
} elseif ($deadline_formatted) {
    $deadline_info = array('class' => 'normal', 'text' => $deadline_formatted);
}

// 難易度設定（single-grant.phpと完全統一、アイコン削除）
$difficulty_configs = array(
    'easy' => array('label' => '簡単', 'dots' => 1, 'color' => '#16a34a'),
    'normal' => array('label' => '普通', 'dots' => 2, 'color' => '#525252'),
    'hard' => array('label' => '難しい', 'dots' => 3, 'color' => '#d97706'),
    'very_hard' => array('label' => '非常に困難', 'dots' => 4, 'color' => '#dc2626')
);
$difficulty = $grant_data['application_difficulty'];
$difficulty_data = $difficulty_configs[$difficulty] ?? $difficulty_configs['normal'];

// CSS・JSの重複防止
static $assets_loaded = false;
?>

<?php if (!$assets_loaded): $assets_loaded = true; ?>

<style>
/* Clean Grant Card Design System - Full Monochrome Edition */

/* グリッド親コンテナのサイズ制約 */
.grants-grid,
.grants-list,
.grant-cards-container {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
}

.grants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
    gap: 1.5rem;
    padding: 1.5rem;
}

@media (max-width: 1200px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
    }
}

@media (max-width: 900px) {
    .grants-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
        gap: 1.25rem;
        padding: 1.25rem;
    }
}

:root {
    /* Core Colors - Pure Monochrome System */
    --clean-primary: #000000;          /* Pure black for primary actions */
    --clean-primary-light: #262626;    /* Dark gray for light primary */
    --clean-primary-dark: #000000;     /* Pure black for dark primary */
    --clean-secondary: #525252;        /* Medium gray for secondary elements */
    --clean-accent: #171717;           /* Very dark gray for accents */
    
    /* Monochrome Base Colors */
    --clean-white: #ffffff;            /* Pure white */
    --clean-gray-50: #fafafa;          /* Lightest gray */
    --clean-gray-100: #f5f5f5;         /* Very light gray */
    --clean-gray-200: #e5e5e5;         /* Light gray */
    --clean-gray-300: #d4d4d4;         /* Medium light gray */
    --clean-gray-400: #a3a3a3;         /* Medium gray */
    --clean-gray-500: #737373;         /* Gray */
    --clean-gray-600: #525252;         /* Dark gray */
    --clean-gray-700: #404040;         /* Darker gray */
    --clean-gray-800: #262626;         /* Very dark gray */
    --clean-gray-900: #171717;         /* Almost black */
    
    /* Semantic Colors - Minimal Use */
    --clean-success: #22c55e;          /* Green (minimal use) */
    --clean-warning: #f59e0b;          /* Orange (minimal use) */
    --clean-danger: #ef4444;           /* Red (minimal use) */
    --clean-info: #000000;             /* Black for info */
    
    /* Gradients - Pure Monochrome */
    --clean-gradient-primary: linear-gradient(135deg, #000000 0%, #262626 100%);
    --clean-gradient-light: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
    --clean-gradient-secondary: linear-gradient(135deg, #f5f5f5 0%, #e5e5e5 100%);
    --clean-gradient-dark: linear-gradient(135deg, #262626 0%, #171717 100%);
    --clean-gradient-accent: linear-gradient(135deg, #171717 0%, #000000 100%);
    
    /* Monochrome Shadows */
    --clean-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --clean-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.05);
    --clean-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.05);
    --clean-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.15), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    
    /* Border Radius - Clean & Modern */
    --clean-radius-sm: 0.25rem;
    --clean-radius-md: 0.5rem;
    --clean-radius-lg: 0.75rem;
    --clean-radius-xl: 1rem;
    --clean-radius-2xl: 1.5rem;
    
    /* Transitions */
    --clean-transition: all 0.15s ease-in-out;
    --clean-transition-slow: all 0.3s ease-in-out;
    
    /* Typography Colors */
    --clean-text-primary: #171717;     /* Primary text (almost black) */
    --clean-text-secondary: #525252;   /* Secondary text */
    --clean-text-muted: #737373;       /* Muted text */
    --clean-text-light: #a3a3a3;       /* Light text */
    
    /* Border Colors */
    --clean-border-light: #e5e5e5;     /* Light border */
    --clean-border-medium: #d4d4d4;    /* Medium border */
    --clean-border-dark: #a3a3a3;      /* Dark border */
    --clean-border-primary: #000000;   /* Primary border (black) */
}

	

/* ============================================
   カード表示モード - 改良版
============================================ */
.grant-view-card .grant-card-unified {
    position: relative;
    width: 100%;
    max-width: 100%;
    min-height: 420px;
    background: var(--clean-white);
    border: 2px solid var(--clean-gray-300);
    border-radius: var(--clean-radius-xl);
    overflow: hidden;
    transition: var(--clean-transition-slow);
    cursor: default;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.grant-view-card .grant-card-unified:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: var(--clean-gray-500);
}

/* ============================================
   リスト表示モード
============================================ */
.grant-view-list .grant-card-unified {
    position: relative;
    width: 100%;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-lg);
    transition: var(--clean-transition);
    cursor: default;
    display: flex;
    flex-direction: row;
    align-items: stretch;
    min-height: 140px;
    margin-bottom: 1rem;
}

.grant-view-list .grant-card-unified:hover {
    box-shadow: var(--clean-shadow-lg);
    transform: translateX(4px);
    border-color: var(--clean-gray-800);
}

.grant-view-list .grant-status-header {
    width: 6px;
    height: auto;
    padding: 0;
    writing-mode: vertical-rl;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clean-gradient-primary);
}

.grant-view-list .grant-card-content {
    flex: 1;
    padding: 1.25rem;
    display: flex;
    flex-direction: row;
    gap: 1.5rem;
}

.grant-view-list .grant-main-info {
    flex: 1;
    min-width: 0;
}

.grant-view-list .grant-title {
    font-size: 1.125rem;
    margin-bottom: 0.75rem;
    -webkit-line-clamp: 2;
}

.grant-view-list .grant-ai-summary {
    display: block;
    max-height: 3.5rem;
}

.grant-view-list .grant-info-grid {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    flex-wrap: wrap;
}

.grant-view-list .grant-info-item {
    background: transparent;
    padding: 0.5rem 0.75rem;
    gap: 0.5rem;
    border-radius: var(--clean-radius-sm);
    background: var(--clean-gray-100);
}

.grant-view-list .grant-info-icon {
    width: 1.25rem;
    height: 1.25rem;
    font-size: 0.875rem;
}

.grant-view-list .grant-card-footer {
    padding: 1.25rem;
    background: transparent;
    border: none;
    border-left: 1px solid var(--clean-gray-200);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    min-width: 12rem;
    justify-content: center;
}

/* ============================================
   コンパクト表示モード
============================================ */
.grant-view-compact .grant-card-unified {
    position: relative;
    width: 100%;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-md);
    transition: var(--clean-transition);
    cursor: default;
    padding: 1rem;
    margin-bottom: 0.75rem;
}

.grant-view-compact .grant-card-unified:hover {
    background: var(--clean-gray-50);
    box-shadow: var(--clean-shadow-md);
    border-color: var(--clean-gray-800);
}

.grant-view-compact .grant-status-header {
    display: none;
}

.grant-view-compact .grant-card-content {
    padding: 0;
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 1rem;
}

.grant-view-compact .grant-title {
    font-size: 1rem;
    margin: 0;
    -webkit-line-clamp: 1;
}

.grant-view-compact .grant-ai-summary,
.grant-view-compact .grant-info-grid,
.grant-view-compact .grant-success-rate {
    display: none;
}

.grant-view-compact .grant-card-footer {
    padding: 0;
    background: transparent;
    border: none;
    flex-direction: row;
    gap: 0.75rem;
    min-width: auto;
}

.grant-view-compact .grant-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

/* ============================================
   共通スタイル
============================================ */

/* ステータスヘッダー */
.grant-status-header {
    position: relative;
    height: 3rem;
    background: var(--clean-gradient-primary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    overflow: hidden;
}

.grant-status-header.status--closed {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.grant-status-header.status--urgent {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.grant-status-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--clean-white);
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.025em;
}

.grant-deadline-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 1rem;
    color: var(--clean-white);
    font-size: 0.75rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* カードコンテンツ */
.grant-card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
    overflow: hidden;
}

/* タイトルセクション - 改良版 */
.grant-title-section {
    margin-bottom: 1rem;
    padding-right: 6rem;
    position: relative;
}

.grant-category-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    border-radius: var(--clean-radius-2xl);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.75rem;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 5;
    max-width: calc(100% - 120px);
}

.grant-title {
    font-size: 1.125rem;
    font-weight: 700;
    line-height: 1.4;
    color: var(--clean-gray-900);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: auto;
    max-height: 3.5rem;
}

.grant-title a {
    color: inherit;
    text-decoration: none;
    transition: var(--clean-transition);
}

.grant-title a:hover {
    color: var(--clean-gray-800);
}

/* AI要約セクション - 改良版 */
.grant-ai-summary {
    position: relative;
    padding: 1rem;
    background: var(--clean-gray-50);
    border: 2px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-lg);
    margin-bottom: 1rem;
    min-height: auto;
    max-height: 5.5rem;
    overflow: hidden;
    transition: var(--clean-transition);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
}

.grant-ai-summary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: var(--clean-gray-400);
    background: var(--clean-white);
}

.grant-ai-summary-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--clean-gray-800);
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.grant-ai-summary-text {
    color: var(--clean-gray-700);
    font-size: 0.9375rem;
    line-height: 1.6;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 情報グリッド - 改良版 */
.grant-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    padding: 0 0.5rem;
}

.grant-info-item {
    display: flex;
    align-items: flex-start;
    gap: 0;
    padding: 0.875rem;
    background: var(--clean-gray-50);
    border: 2px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-lg);
    transition: var(--clean-transition);
    position: relative;
    overflow: hidden;
    min-height: 65px;
}

.grant-info-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: var(--clean-gray-400);
    background: var(--clean-white);
}

.grant-info-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.grant-info-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--clean-gray-600);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}

.grant-info-value {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--clean-gray-900);
    line-height: 1.3;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* アクションフッター - 改良版 */
.grant-card-footer {
    padding: 1.25rem;
    background: var(--clean-white);
    border-top: 2px solid var(--clean-gray-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    position: relative;
    z-index: 10;
    margin-top: auto;
}

.grant-actions {
    display: flex;
    gap: 0.5rem;
    flex: 1;
    flex-wrap: wrap;
}

.grant-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.75rem 1.25rem;
    min-height: 45px;
    border: 2px solid transparent;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--clean-transition-slow);
    text-decoration: none;
    white-space: nowrap;
    position: relative;
    overflow: hidden;
    z-index: 20;
    flex: 1;
    min-width: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* 詳細ボタン - 改良版 */
.grant-btn--primary {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 2px solid var(--clean-gray-800);
}

.grant-btn--primary:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    background: var(--clean-gradient-dark);
    border-color: var(--clean-gray-900);
}

.grant-btn--secondary {
    background: var(--clean-white);
    color: var(--clean-gray-900);
    border: 2px solid var(--clean-gray-400);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.grant-btn--secondary:hover {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: var(--clean-gray-800);
}

/* AI質問ボタン - 改良版 */
.grant-btn--ai {
    background: var(--clean-gradient-accent);
    color: var(--clean-white);
    border: 2px solid var(--clean-gray-800);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
}

.grant-btn--ai:hover {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    border-color: var(--clean-gray-900);
}

.grant-btn--ai:focus {
    outline: 2px solid var(--clean-gray-800);
    outline-offset: 2px;
}

/* AI Checklist Button - 改良版 */
.grant-btn--checklist {
    background: #fff;
    color: #000;
    border: 2px solid #000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.grant-btn--checklist:hover {
    background: #000;
    color: #fff;
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* AI Compare Button - 改良版 */
.grant-btn--compare {
    background: #fff;
    color: #000;
    border: 2px solid #000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.grant-btn--compare:hover {
    background: #000;
    color: #fff;
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.grant-btn--compare.active {
    background: #fbbf24;
    color: #000;
    border-color: #fbbf24;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
}

/* ============================================
   AI機能バッジ群（モノクローム）
============================================ */

/* AI適合度スコア - 配置調整 */
.grant-match-score {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #000;
    color: #fff;
    padding: 0.6rem 0.9rem;
    border-radius: 2rem;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    z-index: 15;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
    min-width: 80px;
    justify-content: center;
}

/* AIバッジモバイルコンテナ */
.grant-ai-badges-mobile {
    display: none;
}

/* バッジの重複防止 - デスクトップ用改良 */
.grant-badge-container {
    position: absolute;
    top: 1rem;
    right: 1rem;
    left: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    z-index: 20;
    pointer-events: none;
}

.grant-badge-left {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: flex-start;
}

.grant-badge-right {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: flex-end;
}

/* スマホ対応: PC版を非表示、モバイル版を表示 */
@media (max-width: 640px) {
    .grant-match-score,
    .grant-ai-difficulty,
    .grant-urgency-alert,
    .grant-badge-container {
        display: none !important;
    }
    
    .grant-ai-badges-mobile {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 0 0.5rem;
    }
    
    .grant-match-score-mobile,
    .grant-ai-difficulty-mobile,
    .grant-urgency-alert-mobile {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 0.75rem;
        border-radius: 1.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .grant-match-score-mobile {
        background: #000;
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .grant-ai-difficulty-mobile {
        background: #fff;
        color: #000;
        border: 2px solid #000;
    }
    
    .grant-urgency-alert-mobile {
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .grant-title-section {
        padding-right: 0;
    }
}

.grant-match-score:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.grant-match-score i {
    font-size: 0.875rem;
    animation: pulse-brain 2s ease-in-out infinite;
}

@keyframes pulse-brain {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* AI申請難易度 - 位置調整 */
.grant-ai-difficulty {
    position: absolute;
    bottom: 5.5rem;
    left: 1rem;
    background: #fff;
    border: 2px solid #000;
    padding: 0.6rem 0.9rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 10;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    min-width: 90px;
}

.grant-ai-difficulty:hover {
    background: #000;
    color: #fff;
}

.difficulty-stars {
    font-size: 0.875rem;
    letter-spacing: 0.1em;
    font-weight: 900;
}

.difficulty-label {
    font-weight: 600;
}

.grant-ai-difficulty[data-level="very-easy"] {
    border-color: #10b981;
}

.grant-ai-difficulty[data-level="easy"] {
    border-color: #6ee7b7;
}

.grant-ai-difficulty[data-level="normal"] {
    border-color: #000;
}

.grant-ai-difficulty[data-level="hard"] {
    border-color: #525252;
}

.grant-ai-difficulty[data-level="very-hard"] {
    border-color: #262626;
}

/* AI期限アラート - 位置調整 */
.grant-urgency-alert {
    position: absolute;
    top: 4.5rem;
    left: 1rem;
    color: #fff;
    padding: 0.6rem 1rem;
    border-radius: 2rem;
    font-size: 0.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 12;
    border: 2px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    min-width: 100px;
    justify-content: center;
}

/* スマホ対応: アラートを下に配置 */
@media (max-width: 640px) {
    .grant-urgency-alert {
        position: static;
        display: inline-flex;
        margin-bottom: 0.5rem;
        font-size: 0.7rem;
        padding: 0.4rem 0.7rem;
    }
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    animation: urgency-pulse 2s ease-in-out infinite;
}

@keyframes urgency-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.grant-urgency-alert[data-level="critical"] {
    animation: urgency-shake 0.5s ease-in-out infinite;
}

@keyframes urgency-shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

/* ホバー時の詳細表示 */
.grant-hover-details {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(248, 250, 252, 0.98);
    backdrop-filter: blur(20px);
    padding: 0;
    opacity: 0;
    visibility: hidden;
    transition: var(--clean-transition-slow);
    overflow: hidden;
    z-index: 5;
    border-radius: var(--clean-radius-xl);
    display: flex;
    flex-direction: column;
    pointer-events: none;
}

.grant-card-unified:hover .grant-hover-details {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

/* スクロール可能なコンテンツエリア */
.grant-hover-scrollable {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1.75rem;
    height: 100%;
}

/* スクロールバーのカスタマイズ */
.grant-hover-scrollable::-webkit-scrollbar {
    width: 6px;
}

.grant-hover-scrollable::-webkit-scrollbar-track {
    background: var(--clean-gray-200);
    border-radius: 3px;
}

.grant-hover-scrollable::-webkit-scrollbar-thumb {
    background: var(--clean-gray-800);
    border-radius: 3px;
}

.grant-hover-scrollable::-webkit-scrollbar-thumb:hover {
    background: var(--clean-gray-900);
}

.grant-hover-details::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--clean-gradient-primary);
    z-index: 10;
}

.grant-hover-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-top: 0.5rem;
    position: sticky;
    top: 0;
    background: rgba(248, 250, 252, 0.98);
    z-index: 10;
    padding-bottom: 1rem;
}

.grant-hover-title {
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--clean-gray-900);
    line-height: 1.3;
    flex: 1;
    padding-right: 1rem;
}

.grant-hover-close {
    width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clean-gray-200);
    border-radius: 50%;
    color: var(--clean-gray-600);
    cursor: pointer;
    flex-shrink: 0;
    transition: var(--clean-transition);
    border: none;
    pointer-events: auto;
}

.grant-hover-close:hover {
    background: var(--clean-gray-800);
    color: var(--clean-white);
    transform: rotate(90deg);
}

/* クイック情報バー */
.grant-quick-stats {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-lg);
    margin-bottom: 1.25rem;
}

.grant-stat-item {
    flex: 1;
    text-align: center;
    padding: 0.75rem;
    border-right: 1px solid var(--clean-gray-200);
    position: relative;
}

.grant-stat-item:last-child {
    border-right: none;
}

.grant-stat-value {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--clean-gray-900);
    margin-bottom: 0.375rem;
}

.grant-stat-label {
    display: block;
    font-size: 0.6875rem;
    color: var(--clean-gray-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.grant-detail-sections {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    flex: 1;
}

.grant-detail-section {
    padding: 1rem;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-md);
    transition: var(--clean-transition);
}

.grant-detail-section:hover {
    box-shadow: var(--clean-shadow-md);
    transform: translateY(-1px);
    border-color: var(--clean-gray-800);
}

.grant-detail-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--clean-gray-800);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.grant-detail-value {
    font-size: 0.875rem;
    color: var(--clean-gray-700);
    line-height: 1.5;
}

/* ステータスインジケーター */
.grant-status-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background: var(--clean-success);
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
    z-index: 10;
}

.grant-status-indicator.closed {
    background: var(--clean-gray-400);
    box-shadow: none;
}

/* 注目バッジ */
.grant-featured-badge {
    position: absolute;
    top: 4rem;
    right: -2.25rem;
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    padding: 0.375rem 2.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transform: rotate(45deg);
    box-shadow: var(--clean-shadow-md);
    z-index: 10;
}

/* 難易度インジケーター */
.grant-difficulty-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    padding: 0.375rem 0.75rem;
    background: var(--clean-white);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-sm);
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.6875rem;
    font-weight: 600;
    box-shadow: var(--clean-shadow-sm);
    z-index: 10;
}

/* プログレスバー（採択率） */
.grant-success-rate {
    margin-top: auto;
    padding-top: 1rem;
}

.grant-success-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: var(--clean-gray-500);
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.grant-success-bar {
    height: 0.375rem;
    background: var(--clean-gray-200);
    border-radius: 0.1875rem;
    overflow: hidden;
    position: relative;
}

.grant-success-fill {
    height: 100%;
    background: var(--clean-gradient-primary);
    border-radius: 0.1875rem;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

/* タグシステム */
.grant-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}

.grant-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    background: var(--clean-gray-100);
    color: var(--clean-gray-800);
    border: 1px solid var(--clean-gray-200);
    border-radius: var(--clean-radius-2xl);
    font-size: 0.6875rem;
    font-weight: 600;
    transition: var(--clean-transition);
}

.grant-tag:hover {
    background: var(--clean-gray-900);
    color: var(--clean-white);
    transform: scale(1.02);
}

/* アニメーション */
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

.grant-card-unified {
    animation: slideIn 0.5s ease-out;
    animation-fill-mode: both;
}

.grant-card-unified:nth-child(1) { animation-delay: 0.05s; }
.grant-card-unified:nth-child(2) { animation-delay: 0.1s; }
.grant-card-unified:nth-child(3) { animation-delay: 0.15s; }
.grant-card-unified:nth-child(4) { animation-delay: 0.2s; }
.grant-card-unified:nth-child(5) { animation-delay: 0.25s; }
.grant-card-unified:nth-child(6) { animation-delay: 0.3s; }

/* トースト通知 */
.grant-toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    padding: 1.25rem 1.5rem;
    background: var(--clean-gray-900);
    color: var(--clean-white);
    border-radius: var(--clean-radius-lg);
    box-shadow: var(--clean-shadow-xl);
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.9375rem;
    font-weight: 600;
    z-index: 9999;
    opacity: 0;
    transform: translateY(100%);
    transition: var(--clean-transition-slow);
}

.grant-toast.show {
    opacity: 1;
    transform: translateY(0);
}

.grant-toast-icon {
    width: 1.75rem;
    height: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--clean-white);
    border-radius: 50%;
    color: var(--clean-gray-900);
}

/* レスポンシブ対応 - 改良版 */
@media (max-width: 768px) {
    .grants-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
        gap: 1.25rem;
    }
    
    .grant-view-card .grant-card-unified {
        height: auto;
        min-height: auto;
        max-width: 100%;
        border-width: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .grant-info-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
        padding: 0;
    }
    
    .grant-info-item {
        min-height: 60px;
        padding: 0.75rem;
    }
    
    .grant-hover-details {
        display: none !important;
    }
    
    .grant-view-list .grant-card-unified {
        flex-direction: column;
        border-width: 2px;
    }
    
    .grant-view-list .grant-status-header {
        width: 100%;
        height: 3rem;
        writing-mode: initial;
    }
    
    .grant-view-list .grant-card-footer {
        border-left: none;
        border-top: 2px solid var(--clean-gray-200);
        min-width: auto;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1.25rem;
    }
    
    .grant-card-content {
        padding: 1.25rem;
    }
    
    .grant-title {
        font-size: 1.125rem;
        min-height: auto;
        line-height: 1.4;
    }
    
    .grant-btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        width: 100%;
        justify-content: center;
        min-height: 48px;
    }
    
    .grant-actions {
        flex-direction: column;
        width: 100%;
        gap: 0.75rem;
    }
    
    /* モバイルでタップで詳細表示 */
    .grant-card-unified {
        cursor: pointer;
    }
    
    /* AI要約セクションの高さ調整 */
    .grant-ai-summary {
        min-height: auto;
        max-height: none;
        padding: 1rem;
        border-width: 2px;
    }
    
    .grant-ai-summary-text {
        -webkit-line-clamp: 3;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    /* ステータスヘッダーをコンパクトに */
    .grant-status-header {
        height: 3rem;
        padding: 0 1.25rem;
    }
    
    .grant-status-badge,
    .grant-deadline-indicator {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }
    
    /* 情報グリッドのパディング調整 */
    .grant-info-item {
        padding: 0.875rem;
        border-width: 2px;
    }
    
    .grant-info-label {
        font-size: 0.7rem;
    }
    
    .grant-info-value {
        font-size: 0.85rem;
    }
    
    /* カテゴリータグ調整 */
    .grant-category-tag {
        padding: 0.5rem 0.875rem;
        font-size: 0.75rem;
        max-width: 100%;
        border-width: 2px;
    }
    
    /* カードの最小高さ調整 */
    .grant-view-card .grant-card-unified {
        min-height: auto;
    }
    
    /* フッターボタンの改良 */
    .grant-card-footer {
        border-top-width: 2px;
        padding: 1.25rem;
    }
}

/* さらに小さい画面向け（480px以下） */
@media (max-width: 480px) {
    .grants-grid {
        padding: 0.75rem;
        gap: 0.75rem;
    }
    
    .grant-view-card .grant-card-unified {
        min-height: auto;
    }
    
    .grant-card-content {
        padding: 0.875rem;
    }
    
    .grant-title {
        font-size: 1rem;
    }
    
    .grant-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        min-height: 38px;
    }
    
    .grant-card-footer {
        padding: 0.75rem;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    :root {
        --clean-white: #1e293b;
        --clean-gray-50: #0f172a;
        --clean-gray-100: #334155;
        --clean-gray-200: #475569;
        --clean-gray-300: #64748b;
        --clean-gray-400: #94a3b8;
        --clean-gray-500: #cbd5e1;
        --clean-gray-600: #e2e8f0;
        --clean-gray-700: #f1f5f9;
        --clean-gray-800: #f8fafc;
        --clean-gray-900: #ffffff;
    }
}

/* 印刷対応 */
@media print {
    .grant-card-unified {
        break-inside: avoid;
        page-break-inside: avoid;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
    
    .grant-hover-details,
    .grant-featured-badge {
        display: none !important;
    }
}

/* 高コントラストモード対応 */
@media (prefers-contrast: high) {
    .grant-card-unified {
        border-width: 2px;
        border-color: var(--clean-gray-800);
    }
    
    .grant-btn {
        border-width: 2px;
    }
    
    .grant-info-item {
        border-width: 2px;
    }
}

/* 減らされたモーション設定対応 */
@media (prefers-reduced-motion: reduce) {
    .grant-card-unified,
    .grant-btn,
    .grant-info-item {
        transition: none;
        animation: none;
    }
    
    .grant-card-unified:hover {
        transform: none;
    }
}

/* フォーカス管理 */
.grant-btn:focus,
.grant-hover-close:focus {
    outline: 2px solid var(--clean-gray-800);
    outline-offset: 2px;
}

/* セレクション色 */
::selection {
    background: rgba(0, 0, 0, 0.1);
    color: var(--clean-gray-900);
}

::-moz-selection {
    background: rgba(0, 0, 0, 0.1);
    color: var(--clean-gray-900);
}

/* スムーススクロール */
.grant-hover-scrollable {
    scroll-behavior: smooth;
}

/* ===== AI質問モーダル ===== */
.grant-ai-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.3s ease;
}

.grant-ai-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.grant-ai-modal-container {
    position: relative;
    width: 90%;
    max-width: 600px;
    height: 80vh;
    max-height: 600px;
    background: var(--clean-white);
    border-radius: var(--clean-radius-xl);
    box-shadow: var(--clean-shadow-xl);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(1);
    transition: transform 0.3s ease;
}

.grant-ai-modal-header {
    padding: var(--clean-radius-lg);
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    position: relative;
}

.grant-ai-modal-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: var(--space-1);
}

.grant-ai-modal-subtitle {
    font-size: 0.875rem;
    opacity: 0.9;
    font-weight: 400;
    max-width: 80%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.grant-ai-modal-close {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    width: 2rem;
    height: 2rem;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: var(--clean-white);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--clean-transition);
}

.grant-ai-modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.grant-ai-modal-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.grant-ai-chat-messages {
    flex: 1;
    padding: var(--space-4);
    overflow-y: auto;
    background: var(--clean-gray-50);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.grant-ai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.grant-ai-chat-messages::-webkit-scrollbar-track {
    background: var(--clean-gray-200);
    border-radius: 3px;
}

.grant-ai-chat-messages::-webkit-scrollbar-thumb {
    background: var(--clean-gray-400);
    border-radius: 3px;
}

.grant-ai-chat-messages::-webkit-scrollbar-thumb:hover {
    background: var(--clean-gray-500);
}

.grant-ai-message {
    display: flex;
    gap: var(--space-3);
    max-width: 85%;
    animation: fadeInUp 0.3s ease-out;
}

.grant-ai-message--assistant {
    align-self: flex-start;
}

.grant-ai-message--user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.grant-ai-message--error {
    align-self: flex-start;
}

.grant-ai-message-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}

.grant-ai-message--assistant .grant-ai-message-avatar {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
}

.grant-ai-message--user .grant-ai-message-avatar {
    background: var(--clean-gray-300);
    color: var(--clean-gray-700);
}

.grant-ai-message--error .grant-ai-message-avatar {
    background: var(--clean-danger);
    color: var(--clean-white);
}

.grant-ai-message-content {
    background: var(--clean-white);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--clean-radius-lg);
    border: 1px solid var(--clean-gray-200);
    box-shadow: var(--clean-shadow-sm);
    font-size: 0.9375rem;
    line-height: 1.5;
    position: relative;
}

.grant-ai-message--user .grant-ai-message-content {
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    border-color: var(--clean-gray-800);
}

.grant-ai-message--error .grant-ai-message-content {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}

.grant-ai-typing {
    display: flex;
    gap: 4px;
    align-items: center;
    padding: var(--space-2) 0;
}

.grant-ai-typing span {
    width: 8px;
    height: 8px;
    background: var(--clean-gray-400);
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.grant-ai-typing span:nth-child(1) { animation-delay: 0.0s; }
.grant-ai-typing span:nth-child(2) { animation-delay: 0.2s; }
.grant-ai-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

.grant-ai-chat-input-container {
    padding: var(--space-4);
    background: var(--clean-white);
    border-top: 1px solid var(--clean-gray-200);
}

.grant-ai-chat-input-wrapper {
    display: flex;
    gap: var(--space-2);
    align-items: flex-end;
    margin-bottom: var(--space-3);
}

.grant-ai-chat-input {
    flex: 1;
    padding: var(--space-3);
    border: 2px solid var(--clean-gray-300);
    border-radius: var(--clean-radius-lg);
    font-family: inherit;
    font-size: 0.9375rem;
    line-height: 1.5;
    resize: none;
    transition: var(--clean-transition);
    background: var(--clean-white);
    min-height: 2.75rem;
    max-height: 6rem;
}

.grant-ai-chat-input:focus {
    outline: none;
    border-color: var(--clean-primary);
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}

.grant-ai-chat-send {
    width: 2.75rem;
    height: 2.75rem;
    background: var(--clean-gradient-primary);
    color: var(--clean-white);
    border: none;
    border-radius: var(--clean-radius-lg);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--clean-transition);
    flex-shrink: 0;
}

.grant-ai-chat-send:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: var(--clean-shadow-md);
}

.grant-ai-chat-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.grant-ai-chat-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

.grant-ai-suggestion {
    padding: var(--space-2) var(--space-3);
    background: var(--clean-gray-100);
    border: 1px solid var(--clean-gray-300);
    border-radius: var(--clean-radius-2xl);
    font-size: 0.8125rem;
    color: var(--clean-gray-700);
    cursor: pointer;
    transition: var(--clean-transition);
    white-space: nowrap;
}

.grant-ai-suggestion:hover {
    background: var(--clean-primary);
    color: var(--clean-white);
    border-color: var(--clean-primary);
    transform: translateY(-1px);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(1rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .grant-ai-modal-container {
        width: 95%;
        height: 90vh;
        margin: var(--space-4);
    }
    
    .grant-ai-modal-header {
        padding: var(--space-3);
    }
    
    .grant-ai-modal-title {
        font-size: 1rem;
    }
    
    .grant-ai-modal-subtitle {
        font-size: 0.8125rem;
    }
    
    .grant-ai-chat-messages {
        padding: var(--space-3);
    }
    
    .grant-ai-message {
        max-width: 95%;
    }
    
    .grant-ai-chat-suggestions {
        flex-direction: column;
    }
    
    .grant-ai-suggestion {
        white-space: normal;
        text-align: left;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // カードクリック処理（詳細ボタンのみでページ遷移）
    document.addEventListener('click', function(e) {
        // 詳細ボタンがクリックされた場合のみページ遷移
        if (e.target.closest('.grant-btn--primary')) {
            const btn = e.target.closest('.grant-btn--primary');
            const href = btn.getAttribute('href');
            if (href) {
                // クリックエフェクト
                btn.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    window.location.href = href;
                }, 100);
            }
        }
    });
    
    // ホバー詳細の表示・非表示制御（デスクトップのみ）
    function isDesktop() {
        return window.innerWidth > 768 && !('ontouchstart' in window);
    }
    
    // ホバーイベント（デスクトップのみ）
    document.querySelectorAll('.grant-card-unified').forEach(card => {
        let hoverTimeout;
        
        card.addEventListener('mouseenter', function() {
            if (!isDesktop()) return;
            
            clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                const details = this.querySelector('.grant-hover-details');
                if (details) {
                    details.classList.add('show-details');
                    details.style.opacity = '1';
                    details.style.visibility = 'visible';
                }
            }, 200);
        });
        
        card.addEventListener('mouseleave', function() {
            clearTimeout(hoverTimeout);
            const details = this.querySelector('.grant-hover-details');
            if (details) {
                details.classList.remove('show-details');
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
            }
        });
    });
    
    // モバイルでのタップ詳細表示
    let tapCount = 0;
    let tapTimeout;
    
    document.addEventListener('touchend', function(e) {
        if (!e.target.closest('.grant-card-unified')) return;
        if (e.target.closest('.grant-btn')) return;
        
        tapCount++;
        
        if (tapCount === 1) {
            tapTimeout = setTimeout(() => {
                tapCount = 0;
            }, 300);
        } else if (tapCount === 2) {
            clearTimeout(tapTimeout);
            tapCount = 0;
            
            // ダブルタップで詳細表示
            const card = e.target.closest('.grant-card-unified');
            const details = card.querySelector('.grant-hover-details');
            if (details) {
                if (details.style.opacity === '1') {
                    details.style.opacity = '0';
                    details.style.visibility = 'hidden';
                    details.classList.remove('show-details');
                } else {
                    details.classList.add('show-details');
                    details.style.opacity = '1';
                    details.style.visibility = 'visible';
                }
            }
        }
    });
    
    // ホバー詳細の閉じるボタン
    document.addEventListener('click', function(e) {
        if (e.target.closest('.grant-hover-close')) {
            e.preventDefault();
            e.stopPropagation();
            const details = e.target.closest('.grant-hover-details');
            if (details) {
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
                details.classList.remove('show-details');
            }
        }
    });
    
    // ESCキーで詳細を閉じる
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.grant-hover-details.show-details').forEach(details => {
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
                details.classList.remove('show-details');
            });
        }
    });
    
    // 詳細表示外をクリックで閉じる
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('grant-hover-details')) {
            e.target.style.opacity = '0';
            e.target.style.visibility = 'hidden';
            e.target.classList.remove('show-details');
        }
    });
    
    // 採択率バーのアニメーション
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bar = entry.target.querySelector('.grant-success-fill');
                if (bar && !bar.dataset.animated) {
                    const rate = parseFloat(bar.dataset.rate);
                    bar.dataset.animated = 'true';
                    
                    // アニメーション開始
                    let currentRate = 0;
                    const increment = rate / 40;
                    const timer = setInterval(() => {
                        currentRate += increment;
                        if (currentRate >= rate) {
                            currentRate = rate;
                            clearInterval(timer);
                        }
                        bar.style.width = currentRate + '%';
                    }, 25);
                }
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.grant-success-rate').forEach(el => {
        observer.observe(el);
    });
    
    // ボタンのフォーカス管理
    document.querySelectorAll('.grant-btn, .grant-hover-close').forEach(btn => {
        btn.addEventListener('focus', function() {
            this.style.outline = '2px solid var(--clean-gray-800)';
            this.style.outlineOffset = '2px';
        });
        
        btn.addEventListener('blur', function() {
            this.style.outline = '';
            this.style.outlineOffset = '';
        });
        
        // キーボードでのアクティベート
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // ウィンドウリサイズ対応
    window.addEventListener('resize', function() {
        // モバイル・デスクトップ切り替え時に詳細表示をリセット
        document.querySelectorAll('.grant-hover-details').forEach(details => {
            if (!isDesktop()) {
                details.style.opacity = '0';
                details.style.visibility = 'hidden';
                details.classList.remove('show-details');
            }
        });
    });
    
    // AI質問モーダル関数をグローバルに追加
    window.openGrantAIChat = function(button) {
        const postId = button.getAttribute('data-post-id');
        const grantTitle = button.getAttribute('data-grant-title');
        
        if (!postId) {
            console.error('Post ID not found');
            return;
        }
        
        // モーダルを作成または表示
        showAIChatModal(postId, grantTitle);
    };
    
    // AI質問モーダルの表示
    function showAIChatModal(postId, grantTitle) {
        // 既存のモーダルを削除
        const existingModal = document.querySelector('.grant-ai-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // モーダルHTML作成
        const modalHTML = `
            <div class="grant-ai-modal" id="grant-ai-modal">
                <div class="grant-ai-modal-overlay" onclick="closeAIChatModal()"></div>
                <div class="grant-ai-modal-container">
                    <div class="grant-ai-modal-header">
                        <div class="grant-ai-modal-title">
                            <span>AI質問チャット</span>
                        </div>
                        <div class="grant-ai-modal-subtitle">${grantTitle}</div>
                        <button class="grant-ai-modal-close" onclick="closeAIChatModal()">
                            閉じる
                        </button>
                    </div>
                    <div class="grant-ai-modal-body">
                        <div class="grant-ai-chat-messages" id="ai-chat-messages-${postId}">
                            <div class="grant-ai-message grant-ai-message--assistant">
                                <div class="grant-ai-message-content">
                                    こんにちは！この助成金について何でもお聞きください。申請方法、対象要件、必要書類など、詳しくお答えします。
                                </div>
                            </div>
                        </div>
                        <div class="grant-ai-chat-input-container">
                            <div class="grant-ai-chat-input-wrapper">
                                <textarea 
                                    class="grant-ai-chat-input" 
                                    id="ai-chat-input-${postId}"
                                    placeholder="この助成金について質問してください..."
                                    rows="3"></textarea>
                                <button 
                                    class="grant-ai-chat-send" 
                                    id="ai-chat-send-${postId}"
                                    onclick="sendAIQuestion('${postId}')">
                                    送信
                                </button>
                            </div>
                            <div class="grant-ai-chat-suggestions">
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', 'この助成金の申請条件を教えてください')">
                                    申請条件について
                                </button>
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', 'この助成金の申請方法を詳しく教えてください')">
                                    申請方法について  
                                </button>
                                <button class="grant-ai-suggestion" onclick="selectSuggestion('${postId}', 'どんな費用が対象になりますか？')">
                                    対象経費について
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // モーダルをDOMに追加
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // フォーカス設定
        setTimeout(() => {
            const input = document.getElementById(`ai-chat-input-${postId}`);
            if (input) {
                input.focus();
            }
        }, 100);
        
        // Enterキーでの送信
        const input = document.getElementById(`ai-chat-input-${postId}`);
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendAIQuestion(postId);
                }
            });
        }
    }
    
    // AI質問モーダルを閉じる
    window.closeAIChatModal = function() {
        const modal = document.querySelector('.grant-ai-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    };
    
    // 質問候補の選択
    window.selectSuggestion = function(postId, question) {
        const input = document.getElementById(`ai-chat-input-${postId}`);
        if (input) {
            input.value = question;
            input.focus();
        }
    };
    
    // AI質問送信
    window.sendAIQuestion = function(postId) {
        const input = document.getElementById(`ai-chat-input-${postId}`);
        const sendBtn = document.getElementById(`ai-chat-send-${postId}`);
        const messagesContainer = document.getElementById(`ai-chat-messages-${postId}`);
        
        if (!input || !messagesContainer) {
            console.error('Required elements not found');
            return;
        }
        
        const question = input.value.trim();
        if (!question) {
            return;
        }
        
        // 送信ボタンを無効化
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '送信中...';
        }
        
        // ユーザーメッセージを追加
        const userMessage = document.createElement('div');
        userMessage.className = 'grant-ai-message grant-ai-message--user';
        userMessage.innerHTML = `
            <div class="grant-ai-message-content">${escapeHtml(question)}</div>
        `;
        messagesContainer.appendChild(userMessage);
        
        // 入力をクリア
        input.value = '';
        
        // スクロールダウン
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // AJAX リクエスト
        const formData = new FormData();
        formData.append('action', 'handle_grant_ai_question');
        formData.append('post_id', postId);
        formData.append('question', question);
        formData.append('nonce', '<?php echo wp_create_nonce('gi_ajax_nonce'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // ローディング表示を追加
            const loadingMessage = document.createElement('div');
            loadingMessage.className = 'grant-ai-message grant-ai-message--assistant grant-ai-loading';
            loadingMessage.innerHTML = `
                <div class="grant-ai-message-content">
                    <div class="grant-ai-typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(loadingMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
            // 2秒後にレスポンスを表示
            setTimeout(() => {
                loadingMessage.remove();
                
                if (data.success) {
                    const assistantMessage = document.createElement('div');
                    assistantMessage.className = 'grant-ai-message grant-ai-message--assistant';
                    assistantMessage.innerHTML = `
                        <div class="grant-ai-message-content">${escapeHtml(data.data.response)}</div>
                    `;
                    messagesContainer.appendChild(assistantMessage);
                } else {
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'grant-ai-message grant-ai-message--error';
                    errorMessage.innerHTML = `
                        <div class="grant-ai-message-content">エラー: 申し訳ございません。エラーが発生しました。しばらく時間をおいて再度お試しください。</div>
                    `;
                    messagesContainer.appendChild(errorMessage);
                }
                
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 2000);
        })
        .catch(error => {
            console.error('Error sending AI question:', error);
            
            // エラーメッセージを表示
            const errorMessage = document.createElement('div');
            errorMessage.className = 'grant-ai-message grant-ai-message--error';
            errorMessage.innerHTML = `
                <div class="grant-ai-message-content">エラー: 通信エラーが発生しました。インターネット接続を確認して再度お試しください。</div>
            `;
            messagesContainer.appendChild(errorMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .finally(() => {
            // 送信ボタンを復活
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '送信';
            }
            input.focus();
        });
    };
    
    // HTMLエスケープ関数
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});

// パーティクルアニメーション用CSS追加
const grantCardStyles = document.createElement('style');
grantCardStyles.textContent = `
    @keyframes particle-float {
        0% {
            opacity: 1;
            transform: translateY(0) translateX(0) scale(1);
        }
        100% {
            opacity: 0;
            transform: translateY(-60px) translateX(${Math.random() * 60 - 30}px) scale(0.3);
        }
    }
    
    /* ドラッグ無効化 */
    .grant-card-unified * {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-user-drag: none;
        -khtml-user-drag: none;
        -moz-user-drag: none;
        -o-user-drag: none;
        user-drag: none;
    }
    
    /* テキストのみ選択可能 */
    .grant-title a,
    .grant-ai-summary-text,
    .grant-detail-value {
        -webkit-user-select: text;
        -moz-user-select: text;
        -ms-user-select: text;
        user-select: text;
    }
`;
document.head.appendChild(grantCardStyles);
</script>
<?php endif; ?>

<!-- クリーンカード本体 -->
<article class="grant-card-unified <?php echo esc_attr($view_class); ?>" 
         data-post-id="<?php echo esc_attr($post_id); ?>"
         data-priority="<?php echo esc_attr($priority_order); ?>"
         role="article"
         aria-label="助成金情報カード">
    
    <!-- ステータスヘッダー -->
    <header class="grant-status-header <?php echo $application_status === 'closed' ? 'status--closed' : ''; ?> <?php echo !empty($deadline_info) && $deadline_info['class'] === 'critical' ? 'status--urgent' : ''; ?>">
        <div class="grant-status-badge">
            <span><?php echo esc_html($status_display); ?></span>
        </div>
        <?php if (!empty($deadline_info)): ?>
        <div class="grant-deadline-indicator">
            <span><?php echo esc_html($deadline_info['text']); ?></span>
        </div>
        <?php endif; ?>
    </header>
    
    <!-- ステータスインジケーター -->
    <div class="grant-status-indicator <?php echo $application_status === 'closed' ? 'closed' : ''; ?>" 
         aria-label="<?php echo $application_status === 'closed' ? '募集終了' : '募集中'; ?>"></div>
    
    <!-- 注目バッジ -->
    <?php if ($is_featured): ?>
    <div class="grant-featured-badge" aria-label="注目の助成金">FEATURED</div>
    <?php endif; ?>
    
    <!-- 難易度バッジ -->
    <?php if ($grant_difficulty && $grant_difficulty !== 'normal'): ?>
    <div class="grant-difficulty-badge" style="color: <?php echo esc_attr($difficulty_data['color']); ?>">
        <span><?php echo esc_html($difficulty_data['label']); ?></span>
    </div>
    <?php endif; ?>

    
    <!-- カードコンテンツ -->
    <div class="grant-card-content">
        <div class="grant-main-info">

            
            <!-- タイトルセクション -->
            <div class="grant-title-section">
                <?php if ($main_category): ?>
                <div class="grant-category-tag">
                    <span><?php echo esc_html($main_category); ?></span>
                </div>
                <?php endif; ?>
                <h3 class="grant-title">
                    <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>の詳細ページ" tabindex="-1">
                        <?php echo esc_html($title); ?>
                    </a>
                </h3>
            </div>
            
            <!-- AI要約 -->
            <?php if ($ai_summary || $excerpt): ?>
            <div class="grant-ai-summary">
                <div class="grant-ai-summary-label">
                    <span>AI要約</span>
                </div>
                <p class="grant-ai-summary-text">
                    <?php echo esc_html(wp_trim_words($ai_summary ?: $excerpt, 40, '...')); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <!-- 情報グリッド -->
            <div class="grant-info-grid">
                <!-- 助成金額 -->
                <?php if ($amount_display): ?>
                <div class="grant-info-item grant-info-item--amount">
                    <div class="grant-info-content">
                        <span class="grant-info-label">助成額</span>
                        <span class="grant-info-value"><?php echo esc_html($amount_display); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 対象者 -->
                <?php if ($grant_target): ?>
                <div class="grant-info-item grant-info-item--target">
                    <div class="grant-info-content">
                        <span class="grant-info-label">対象</span>
                        <span class="grant-info-value"><?php echo esc_html($grant_target); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 地域 -->
                <div class="grant-info-item grant-info-item--location">
                    <div class="grant-info-content">
                        <span class="grant-info-label">地域</span>
                        <span class="grant-info-value"><?php echo esc_html($prefecture); ?></span>
                    </div>
                </div>
                
                <!-- 補助率 -->
                <?php if ($subsidy_rate): ?>
                <div class="grant-info-item grant-info-item--rate">
                    <div class="grant-info-content">
                        <span class="grant-info-label">補助率</span>
                        <span class="grant-info-value"><?php echo esc_html($subsidy_rate); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- タグ -->
            <?php if ($main_industry || $application_period): ?>
            <div class="grant-tags">
                <?php if ($main_industry): ?>
                <span class="grant-tag">
                    <?php echo esc_html($main_industry); ?>
                </span>
                <?php endif; ?>
                <?php if ($application_period): ?>
                <span class="grant-tag">
                    <?php echo esc_html($application_period); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- 採択率プログレスバー -->
            <?php if ($grant_success_rate > 0): ?>
            <div class="grant-success-rate">
                <div class="grant-success-label">
                    <span>採択率</span>
                    <span><?php echo esc_html($grant_success_rate); ?>%</span>
                </div>
                <div class="grant-success-bar" role="progressbar" aria-valuenow="<?php echo esc_attr($grant_success_rate); ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="grant-success-fill" data-rate="<?php echo esc_attr($grant_success_rate); ?>" style="width: 0;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- アクションフッター -->
    <footer class="grant-card-footer">
        <div class="grant-actions">
            <a href="<?php echo esc_url($permalink); ?>" class="grant-btn grant-btn--primary" role="button">
                <span>詳細を見る</span>
            </a>
            <button class="grant-btn grant-btn--ai" 
                    data-post-id="<?php echo esc_attr($post_id); ?>" 
                    data-grant-title="<?php echo esc_attr($title); ?>"
                    onclick="openGrantAIChat(this)" 
                    role="button">
                <span>AIに質問</span>
            </button>
            <?php if ($official_url): ?>
            <a href="<?php echo esc_url($official_url); ?>" class="grant-btn grant-btn--secondary grant-btn--icon" target="_blank" rel="noopener noreferrer" role="button" title="公式サイト">
                <svg class="grant-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m9 18 6-6-6-6"/>
                    <path d="M14 5h7v7"/>
                </svg>
            </a>
            <?php endif; ?>
            
            <!-- AI機能ボタン群 -->
            <button class="grant-btn grant-btn--checklist grant-btn--icon" 
                    data-post-id="<?php echo esc_attr($post_id); ?>" 
                    data-grant-title="<?php echo esc_attr($title); ?>"
                    onclick="openGrantChecklist(this)" 
                    title="チェックリスト"
                    role="button">
                <svg class="grant-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3 8-8"/>
                    <path d="m21 12-8 8-3-3"/>
                    <rect x="3" y="5" width="6" height="6" rx="1"/>
                </svg>
            </button>
            
            <button class="grant-btn grant-btn--compare grant-btn--icon" 
                    data-post-id="<?php echo esc_attr($post_id); ?>" 
                    data-grant-title="<?php echo esc_attr($title); ?>"
                    onclick="addToCompare(this)" 
                    title="比較機能"
                    role="button">
                <svg class="grant-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18"/>
                    <path d="M7 12h10"/>
                    <path d="M10 18h4"/>
                </svg>
            </button>
        </div>
    </footer>
    
    <!-- ホバー時の詳細表示 -->
    <div class="grant-hover-details" aria-hidden="true">
        <div class="grant-hover-scrollable">
            <div class="grant-hover-header">
                <h3 class="grant-hover-title"><?php echo esc_html($title); ?></h3>
                <button class="grant-hover-close grant-btn--icon" aria-label="詳細を閉じる">
                    <svg class="grant-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            
            <!-- クイック統計 -->
            <div class="grant-quick-stats">
                <?php if ($amount_display): ?>
                <div class="grant-stat-item">
                    <span class="grant-stat-value"><?php echo esc_html($amount_display); ?></span>
                    <span class="grant-stat-label">最大助成額</span>
                </div>
                <?php endif; ?>
                <?php if ($grant_success_rate > 0): ?>
                <div class="grant-stat-item">
                    <span class="grant-stat-value"><?php echo esc_html($grant_success_rate); ?>%</span>
                    <span class="grant-stat-label">採択率</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="grant-detail-sections">
                <?php if ($ai_summary): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <span>AI要約（完全版）</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($ai_summary); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($application_period): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <span>申請期間</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($application_period); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($eligible_expenses): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <span>対象経費</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($eligible_expenses); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($required_documents): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <span>必要書類</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($required_documents); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($application_method): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <span>申請方法</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($application_method); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($contact_info): ?>
                <div class="grant-detail-section">
                    <div class="grant-detail-label">
                        <span>お問い合わせ</span>
                    </div>
                    <div class="grant-detail-value">
                        <?php echo esc_html($contact_info); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>

<?php
// JavaScriptを一度だけ出力
static $ai_features_js_loaded = false;
if (!$ai_features_js_loaded):
    $ai_features_js_loaded = true;
?>
<script>
// ============================================================================
// AI機能JavaScript（モノクローム対応）
// ============================================================================

// グローバル比較リスト
window.compareList = window.compareList || [];

/**
 * AI申請チェックリスト表示
 */
function openGrantChecklist(button) {
    const postId = button.dataset.postId;
    const grantTitle = button.dataset.grantTitle;
    
    // モーダル作成
    const modal = document.createElement('div');
    modal.className = 'ai-checklist-modal';
    modal.innerHTML = `
        <div class="ai-modal-overlay" onclick="this.parentElement.remove()"></div>
        <div class="ai-modal-content">
            <div class="ai-modal-header">
                <h3>AI申請チェックリスト</h3>
                <button class="ai-modal-close grant-btn--icon" onclick="this.closest('.ai-checklist-modal').remove()">
                    <svg class="grant-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="ai-modal-body">
                <p class="ai-grant-title">${grantTitle}</p>
                <div class="ai-checklist-loading">
                    チェックリスト生成中...
                </div>
                <div class="ai-checklist-items" style="display:none;"></div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // AJAX でチェックリスト取得
    fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'gi_generate_checklist',
            post_id: postId,
            nonce: '<?php echo wp_create_nonce("gi_ai_search_nonce"); ?>'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const checklistHtml = data.data.checklist.map(item => `
                <label class="ai-checklist-item" data-priority="${item.priority}">
                    <input type="checkbox" ${item.checked ? 'checked' : ''}>
                    <span class="ai-check-mark"></span>
                    <span class="ai-check-text">${item.text}</span>
                    <span class="ai-check-priority">${item.priority === 'high' ? '重要' : ''}</span>
                </label>
            `).join('');
            
            modal.querySelector('.ai-checklist-loading').style.display = 'none';
            modal.querySelector('.ai-checklist-items').style.display = 'block';
            modal.querySelector('.ai-checklist-items').innerHTML = checklistHtml;
        }
    });
}

/**
 * AI比較機能に追加
 */
function addToCompare(button) {
    const postId = button.dataset.postId;
    const grantTitle = button.dataset.grantTitle;
    
    // 既に追加されているかチェック
    if (window.compareList.some(item => item.id === postId)) {
        button.classList.remove('active');
        window.compareList = window.compareList.filter(item => item.id !== postId);
        showToast('比較から削除しました');
        updateCompareButton();
        return;
    }
    
    // 最大3件まで
    if (window.compareList.length >= 3) {
        showToast('比較は最大3件までです', 'warning');
        return;
    }
    
    window.compareList.push({ id: postId, title: grantTitle });
    button.classList.add('active');
    showToast('比較に追加しました');
    updateCompareButton();
}

/**
 * 比較ボタン更新
 */
function updateCompareButton() {
    // 固定比較ボタンを表示/更新
    let compareBtn = document.getElementById('ai-compare-floating-btn');
    
    if (window.compareList.length >= 2) {
        if (!compareBtn) {
            compareBtn = document.createElement('button');
            compareBtn.id = 'ai-compare-floating-btn';
            compareBtn.className = 'ai-floating-compare-btn';
            compareBtn.onclick = showCompareModal;
            document.body.appendChild(compareBtn);
        }
        compareBtn.innerHTML = `
            <span>${window.compareList.length}件を比較</span>
        `;
        compareBtn.style.display = 'flex';
    } else if (compareBtn) {
        compareBtn.style.display = 'none';
    }
}

/**
 * AI比較モーダル表示
 */
function showCompareModal() {
    if (window.compareList.length < 2) {
        showToast('比較するには2件以上選択してください', 'warning');
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'ai-compare-modal';
    modal.innerHTML = `
        <div class="ai-modal-overlay" onclick="this.parentElement.remove()"></div>
        <div class="ai-modal-content ai-modal-large">
            <div class="ai-modal-header">
                <h3>AI比較分析</h3>
                <button class="ai-modal-close grant-btn--icon" onclick="this.closest('.ai-compare-modal').remove()">
                    <svg class="grant-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="ai-modal-body">
                <div class="ai-compare-loading">
                    分析中...
                </div>
                <div class="ai-compare-result" style="display:none;"></div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // AJAX で比較データ取得
    fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'gi_compare_grants',
            grant_ids: window.compareList.map(g => g.id),
            nonce: '<?php echo wp_create_nonce("gi_ai_search_nonce"); ?>'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const comparison = data.data.comparison;
            const recommendation = data.data.recommendation;
            
            const tableHtml = `
                <div class="ai-recommend-box">
                    <strong>AIのおすすめ:</strong> ${recommendation.title}
                    <span class="recommend-score">適合度 ${recommendation.match_score}%</span>
                </div>
                <table class="ai-compare-table">
                    <thead>
                        <tr>
                            <th>項目</th>
                            ${comparison.map(g => `<th>${g.title}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>助成額</td>
                            ${comparison.map(g => `<td>${g.amount || '未定'}</td>`).join('')}
                        </tr>
                        <tr>
                            <td>AI適合度</td>
                            ${comparison.map(g => `<td><strong>${g.match_score}%</strong></td>`).join('')}
                        </tr>
                        <tr>
                            <td>採択率</td>
                            ${comparison.map(g => `<td>${g.rate || '-'}%</td>`).join('')}
                        </tr>
                        <tr>
                            <td>難易度</td>
                            ${comparison.map(g => `<td>${g.difficulty.label}</td>`).join('')}
                        </tr>
                    </tbody>
                </table>
            `;
            
            modal.querySelector('.ai-compare-loading').style.display = 'none';
            modal.querySelector('.ai-compare-result').style.display = 'block';
            modal.querySelector('.ai-compare-result').innerHTML = tableHtml;
        }
    });
}

/**
 * トースト通知
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `ai-toast ai-toast-${type}`;
    toast.innerHTML = `
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<!-- AI機能CSS -->
<style>
/* AI Modal Base */
.ai-checklist-modal,
.ai-compare-modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.ai-modal-content {
    position: relative;
    background: #fff;
    border-radius: 1rem;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalFadeIn 0.3s ease;
}

.ai-modal-large {
    max-width: 800px;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.ai-modal-header {
    padding: 1.5rem;
    border-bottom: 2px solid #000;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ai-modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ai-modal-close {
    background: #fff;
    border: 2px solid #000;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.ai-modal-close:hover {
    background: #000;
    color: #fff;
}

.ai-modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}

/* Checklist */
.ai-grant-title {
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e5e5;
}

.ai-checklist-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem;
    border: 2px solid #e5e5e5;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.3s;
}

.ai-checklist-item:hover {
    border-color: #000;
    background: #fafafa;
}

.ai-checklist-item input[type="checkbox"] {
    display: none;
}

.ai-check-mark {
    width: 1.5rem;
    height: 1.5rem;
    border: 2px solid #000;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ai-checklist-item input:checked + .ai-check-mark {
    background: #000;
}

.ai-checklist-item input:checked + .ai-check-mark::after {
    content: '✓';
    color: #fff;
    font-weight: bold;
}

.ai-check-text {
    flex: 1;
}

.ai-check-priority {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background: #fbbf24;
    color: #000;
    border-radius: 0.25rem;
    font-weight: 600;
}

/* Compare Table */
.ai-compare-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.ai-compare-table th,
.ai-compare-table td {
    padding: 0.875rem;
    border: 2px solid #000;
    text-align: center;
}

.ai-compare-table thead th {
    background: #000;
    color: #fff;
    font-weight: 700;
}

.ai-compare-table tbody td:first-child {
    background: #f5f5f5;
    font-weight: 600;
    text-align: left;
}

.ai-recommend-box {
    background: #fbbf24;
    color: #000;
    padding: 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.recommend-score {
    margin-left: auto;
    background: #000;
    color: #fff;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
}

/* Floating Compare Button */
.ai-floating-compare-btn {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: #000;
    color: #fff;
    border: none;
    padding: 1rem 1.5rem;
    border-radius: 2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    transition: all 0.3s;
    z-index: 9999;
    animation: bounce 2s ease-in-out infinite;
}

.ai-floating-compare-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* Toast */
.ai-toast {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: #000;
    color: #fff;
    padding: 1rem 1.5rem;
    border-radius: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    z-index: 10001;
    opacity: 0;
    transition: all 0.3s;
}

.ai-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.ai-toast-warning {
    background: #fbbf24;
    color: #000;
}

/* Icon Button Styles */
.grant-btn--icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    min-height: 2.5rem;
    padding: 0.5rem;
    border-radius: 0.375rem;
    background: transparent;
    border: 2px solid #000;
    color: #000;
    transition: all 0.2s;
    cursor: pointer;
}

.grant-btn--icon:hover {
    background: #000;
    color: #fff;
    transform: translateY(-2px);
}

.grant-btn--icon .grant-icon {
    width: 1.25rem;
    height: 1.25rem;
    stroke-width: 2.5;
}

/* Icon button specific colors */
.grant-btn--secondary.grant-btn--icon {
    border-color: #666;
    color: #666;
}

.grant-btn--secondary.grant-btn--icon:hover {
    background: #666;
    color: #fff;
}

.grant-btn--checklist.grant-btn--icon {
    border-color: #059669;
    color: #059669;
}

.grant-btn--checklist.grant-btn--icon:hover {
    background: #059669;
    color: #fff;
}

.grant-btn--compare.grant-btn--icon {
    border-color: #dc2626;
    color: #dc2626;
}

.grant-btn--compare.grant-btn--icon:hover {
    background: #dc2626;
    color: #fff;
}

.grant-btn--compare.grant-btn--icon.active {
    background: #dc2626;
    color: #fff;
}

/* Close button styles */
.grant-hover-close.grant-btn--icon,
.ai-modal-close.grant-btn--icon {
    border: 1px solid #ccc;
    color: #666;
    min-width: 2rem;
    min-height: 2rem;
    padding: 0.25rem;
}

.grant-hover-close.grant-btn--icon:hover,
.ai-modal-close.grant-btn--icon:hover {
    background: #f5f5f5;
    color: #000;
    border-color: #999;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grant-btn--icon {
        min-width: 2.25rem;
        min-height: 2.25rem;
        padding: 0.375rem;
    }
    
    .grant-btn--icon .grant-icon {
        width: 1.125rem;
        height: 1.125rem;
    }
}
</style>
<?php endif; ?>