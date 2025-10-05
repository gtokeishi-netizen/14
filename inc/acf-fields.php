<?php
/**
 * Grant Insight Perfect - ACF Setup & Fields
 *
 * Advanced Custom Fields の設定とフィールド定義を統合管理
 * 
 * @package Grant_Insight_Perfect
 * @version 8.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =============================================================================
 * 1. ACF基本設定
 * =============================================================================
 */

/**
 * ACF Local JSON の設定
 */
add_filter('acf/settings/save_json', function($path) {
    $theme_path = get_stylesheet_directory() . '/acf-json';
    if (!file_exists($theme_path)) {
        wp_mkdir_p($theme_path);
    }
    return $theme_path;
});

add_filter('acf/settings/load_json', function($paths) {
    $theme_path = get_stylesheet_directory() . '/acf-json';
    if (!in_array($theme_path, $paths, true)) {
        $paths[] = $theme_path;
    }
    return $paths;
});

/**
 * ACFが無効の場合の代替関数を提供
 */
if (!function_exists('get_field')) {
    function get_field($field_name, $post_id = false) {
        if (!$post_id) $post_id = get_the_ID();
        return get_post_meta($post_id, $field_name, true);
    }
}

if (!function_exists('the_field')) {
    function the_field($field_name, $post_id = false) {
        echo get_field($field_name, $post_id);
    }
}

/**
 * =============================================================================
 * 2. フィールドグループ定義
 * =============================================================================
 */

/**
 * ACFフィールドグループをPHPで定義（ACFプラグイン非依存）
 */
function gi_register_acf_field_groups() {
    
    // ACFが有効でない場合は処理しない
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    /**
     * 助成金詳細フィールドグループ
     */
    acf_add_local_field_group(array(
        'key' => 'group_grant_details',
        'title' => '助成金詳細情報',
        'fields' => array(
            
            // ========== 基本情報 ==========
            array(
                'key' => 'field_organization',
                'label' => '実施組織',
                'name' => 'organization',
                'type' => 'text',
                'instructions' => '助成金を実施する組織名を入力してください。',
                'required' => 1,
                'default_value' => '',
                'placeholder' => '例: 経済産業省、東京都、○○市',
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            array(
                'key' => 'field_organization_type',
                'label' => '組織タイプ',
                'name' => 'organization_type',
                'type' => 'select',
                'instructions' => '実施組織のタイプを選択してください。',
                'required' => 0,
                'choices' => array(
                    'national' => '国（省庁）',
                    'prefecture' => '都道府県',
                    'city' => '市区町村',
                    'public_org' => '公的機関',
                    'private_org' => '民間団体',
                    'other' => 'その他',
                ),
                'default_value' => 'national',
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            // ========== 金額情報 ==========
            array(
                'key' => 'field_max_amount',
                'label' => '最大助成額（テキスト）',
                'name' => 'max_amount',
                'type' => 'text',
                'instructions' => '「300万円」「上限なし」など、表示用の金額を入力してください。',
                'required' => 1,
                'default_value' => '',
                'placeholder' => '例: 300万円、1000万円、上限なし',
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            array(
                'key' => 'field_max_amount_numeric',
                'label' => '最大助成額（数値）',
                'name' => 'max_amount_numeric',
                'type' => 'number',
                'instructions' => '検索・ソート用の数値（円単位）を入力してください。',
                'required' => 1,
                'default_value' => 0,
                'min' => 0,
                'step' => 1000,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_min_amount',
                'label' => '最小助成額',
                'name' => 'min_amount',
                'type' => 'number',
                'instructions' => '最小助成額があれば入力してください。',
                'required' => 0,
                'default_value' => 0,
                'min' => 0,
                'step' => 1000,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            // [REMOVED] Unused fields: field_subsidy_rate and field_amount_note
            // Spreadsheet uses subsidy_rate_detailed (AD column) as source of truth
            
            // ========== 締切・ステータス ==========
            array(
                'key' => 'field_deadline',
                'label' => '締切（表示用）',
                'name' => 'deadline',
                'type' => 'text',
                'instructions' => '表示用の締切情報を入力してください。',
                'required' => 0,
                'default_value' => '',
                'placeholder' => '例: 令和6年3月31日、随時受付',
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            array(
                'key' => 'field_deadline_date',
                'label' => '締切日',
                'name' => 'deadline_date',
                'type' => 'date_picker',
                'instructions' => 'ソート・検索用の締切日を設定してください。',
                'required' => 0,
                'display_format' => 'Y年m月d日',
                'return_format' => 'Y-m-d',
                'first_day' => 0,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_application_status',
                'label' => '申請ステータス',
                'name' => 'application_status',
                'type' => 'select',
                'instructions' => '現在の申請状況を選択してください。',
                'required' => 1,
                'choices' => array(
                    'open' => '募集中',
                    'upcoming' => '募集予定',
                    'closed' => '募集終了',
                    'suspended' => '一時停止',
                ),
                'default_value' => 'open',
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_application_period',
                'label' => '申請期間',
                'name' => 'application_period',
                'type' => 'text',
                'instructions' => '申請期間の詳細情報を入力してください。',
                'required' => 0,
                'placeholder' => '例: 第1次締切 3月31日、第2次締切 6月30日',
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            // [REMOVED] Unused field: field_deadline_note
            // Not present in spreadsheet configuration
            
            // ========== 対象・条件 ==========
            array(
                'key' => 'field_grant_target',
                'label' => '対象者・対象事業',
                'name' => 'grant_target',
                'type' => 'wysiwyg',
                'instructions' => '助成金の対象となる事業者や事業内容を入力してください。',
                'required' => 1,
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ),
            
            array(
                'key' => 'field_eligible_expenses',
                'label' => '対象経費',
                'name' => 'eligible_expenses',
                'type' => 'wysiwyg',
                'instructions' => '助成対象となる経費を入力してください。',
                'required' => 0,
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ),
            
            array(
                'key' => 'field_grant_difficulty',
                'label' => '申請難易度',
                'name' => 'grant_difficulty',
                'type' => 'select',
                'instructions' => '申請の難易度を選択してください。',
                'required' => 0,
                'choices' => array(
                    'easy' => '易しい',
                    'normal' => '普通',
                    'hard' => '難しい',
                    'expert' => '専門的',
                ),
                'default_value' => 'normal',
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            // [REMOVED] Duplicate field: field_grant_success_rate
            // Spreadsheet uses field_adoption_rate (AA column) as source of truth
            
            array(
                'key' => 'field_required_documents',
                'label' => '必要書類',
                'name' => 'required_documents',
                'type' => 'wysiwyg',
                'instructions' => '申請に必要な書類を入力してください。',
                'required' => 0,
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            // ========== 地域情報 ==========
            
            array(
                'key' => 'field_regional_limitation',
                'label' => '地域制限',
                'name' => 'regional_limitation',
                'type' => 'select',
                'instructions' => '地域制限のタイプを選択してください。',
                'required' => 0,
                'choices' => array(
                    'nationwide' => '全国対象',
                    'prefecture_only' => '都道府県内限定',
                    'municipality_only' => '市町村限定',
                    'region_group' => '地域グループ限定',
                    'specific_area' => '特定地域限定',
                ),
                'default_value' => 'nationwide',
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_regional_note',
                'label' => '地域に関する備考（旧）',
                'name' => 'regional_note',
                'type' => 'textarea',
                'required' => 0,
                'rows' => 2,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            // ========== 申請・連絡先 ==========
            array(
                'key' => 'field_application_method',
                'label' => '申請方法',
                'name' => 'application_method',
                'type' => 'select',
                'instructions' => '申請方法を選択してください。',
                'required' => 0,
                'choices' => array(
                    'online' => 'オンライン申請',
                    'mail' => '郵送申請',
                    'visit' => '窓口申請',
                    'mixed' => 'オンライン・郵送併用',
                ),
                'default_value' => 'online',
                'wrapper' => array(
                    'width' => '33.33',
                ),
            ),
            
            array(
                'key' => 'field_contact_info',
                'label' => '問い合わせ先',
                'name' => 'contact_info',
                'type' => 'textarea',
                'instructions' => '問い合わせ先の情報を入力してください。',
                'required' => 0,
                'rows' => 4,
                'wrapper' => array(
                    'width' => '33.33',
                ),
            ),
            
            array(
                'key' => 'field_official_url',
                'label' => '公式URL',
                'name' => 'official_url',
                'type' => 'url',
                'instructions' => '公式サイトのURLを入力してください。',
                'required' => 0,
                'wrapper' => array(
                    'width' => '33.34',
                ),
            ),
            
            array(
                'key' => 'field_external_link',
                'label' => '外部リンク',
                'name' => 'external_link',
                'type' => 'url',
                'instructions' => '関連する外部リンクがあれば入力してください。',
                'required' => 0,
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            // ========== 新規追加フィールド（Z-AD列対応） ==========
            array(
                'key' => 'field_area_notes',
                'label' => '地域に関する備考',
                'name' => 'area_notes',
                'type' => 'textarea',
                'instructions' => '地域制限や対象地域に関する詳細な備考を入力してください。',
                'required' => 0,
                'rows' => 3,
                'placeholder' => '例: 本社または主要事業所が対象地域内にある事業者限定',
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            array(
                'key' => 'field_required_documents_detailed',
                'label' => '必要書類（詳細）',
                'name' => 'required_documents_detailed',
                'type' => 'wysiwyg',
                'instructions' => '申請に必要な書類の詳細なリストを入力してください。',
                'required' => 0,
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'wrapper' => array(
                    'width' => '50',
                ),
            ),
            
            array(
                'key' => 'field_adoption_rate',
                'label' => '採択率（%）',
                'name' => 'adoption_rate',
                'type' => 'number',
                'instructions' => '過去の実績に基づく採択率を入力してください（0-100の範囲）。',
                'required' => 0,
                'default_value' => 0,
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'append' => '%',
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_difficulty_level',
                'label' => '申請難易度',
                'name' => 'difficulty_level',
                'type' => 'select',
                'instructions' => '申請の難易度レベルを選択してください。',
                'required' => 0,
                'choices' => array(
                    '初級' => '初級（比較的簡単）',
                    '中級' => '中級（標準的）',
                    '上級' => '上級（難しい）',
                    '非常に高い' => '非常に高い（専門的）',
                ),
                'default_value' => '中級',
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_eligible_expenses_detailed',
                'label' => '対象経費（詳細）',
                'name' => 'eligible_expenses_detailed',
                'type' => 'wysiwyg',
                'instructions' => '助成対象となる経費の詳細を入力してください。',
                'required' => 0,
                'tabs' => 'visual',
                'toolbar' => 'basic',
                'media_upload' => 0,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_subsidy_rate_detailed',
                'label' => '補助率（詳細）',
                'name' => 'subsidy_rate_detailed',
                'type' => 'text',
                'instructions' => '補助率の詳細な説明を入力してください。',
                'required' => 0,
                'placeholder' => '例: 1/2以内（上限100万円）、定額50万円',
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            // ========== 管理設定 ==========
            array(
                'key' => 'field_is_featured',
                'label' => '注目の助成金',
                'name' => 'is_featured',
                'type' => 'true_false',
                'instructions' => 'トップページなどで優先表示する場合はチェックしてください。',
                'required' => 0,
                'message' => '注目の助成金として表示する',
                'default_value' => 0,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_priority_order',
                'label' => '表示優先度',
                'name' => 'priority_order',
                'type' => 'number',
                'instructions' => '数値が小さいほど優先表示されます。',
                'required' => 0,
                'default_value' => 100,
                'min' => 1,
                'max' => 999,
                'step' => 1,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_views_count',
                'label' => '表示回数',
                'name' => 'views_count',
                'type' => 'number',
                'instructions' => '自動で更新されます。手動での変更は推奨しません。',
                'required' => 0,
                'default_value' => 0,
                'readonly' => 1,
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_last_updated',
                'label' => '最終更新日',
                'name' => 'last_updated',
                'type' => 'date_time_picker',
                'instructions' => '情報の最終更新日を記録してください。',
                'required' => 0,
                'display_format' => 'Y年m月d日 H:i',
                'return_format' => 'Y-m-d H:i:s',
                'wrapper' => array(
                    'width' => '25',
                ),
            ),
            
            array(
                'key' => 'field_admin_notes',
                'label' => '管理者メモ',
                'name' => 'admin_notes',
                'type' => 'textarea',
                'instructions' => '管理者用のメモ（公開されません）。',
                'required' => 0,
                'rows' => 3,
            ),
        ),
        
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'grant',
                ),
            ),
        ),
        
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '助成金・補助金の詳細情報を管理するフィールドグループです。',
    ));
    
    /**
     * オプションページ用フィールドグループ
     */
    acf_add_local_field_group(array(
        'key' => 'group_theme_options',
        'title' => 'テーマ設定',
        'fields' => array(
            
            array(
                'key' => 'field_site_description',
                'label' => 'サイト説明',
                'name' => 'site_description',
                'type' => 'textarea',
                'instructions' => 'サイトの説明文を入力してください。',
                'required' => 0,
                'rows' => 4,
            ),
            
            array(
                'key' => 'field_contact_email',
                'label' => 'お問い合わせメール',
                'name' => 'contact_email',
                'type' => 'email',
                'instructions' => 'お問い合わせ用のメールアドレスを入力してください。',
                'required' => 0,
            ),
            
            array(
                'key' => 'field_analytics_code',
                'label' => 'アナリティクスコード',
                'name' => 'analytics_code',
                'type' => 'textarea',
                'instructions' => 'Google AnalyticsやGTMのコードを入力してください。',
                'required' => 0,
                'rows' => 5,
            ),
        ),
        
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'theme-settings',
                ),
            ),
        ),
        
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'テーマの設定を管理するフィールドグループです。',
    ));
}

// フィールドグループを登録
add_action('acf/init', 'gi_register_acf_field_groups');

/**
 * =============================================================================
 * 3. ACF設定の拡張
 * =============================================================================
 */

/**
 * オプションページの追加
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'テーマ設定',
        'menu_title' => 'テーマ設定',
        'menu_slug' => 'theme-settings',
        'capability' => 'edit_posts',
        'position' => '59.5',
        'icon_url' => 'dashicons-admin-generic',
        'redirect' => false
    ));
}

/**
 * フィールドの表示カスタマイズ
 */
add_filter('acf/load_field/name=max_amount_numeric', function($field) {
    $field['append'] = '円';
    return $field;
});

add_filter('acf/load_field/name=min_amount', function($field) {
    $field['append'] = '円';
    return $field;
});

add_filter('acf/load_field/name=grant_success_rate', function($field) {
    $field['append'] = '%';
    return $field;
});

/**
 * 投稿保存時の自動処理（新規フィールド対応）
 */
add_action('save_post', function($post_id) {
    // 助成金投稿タイプのみ対象
    if (get_post_type($post_id) !== 'grant') {
        return;
    }
    
    // 自動保存、リビジョンをスキップ
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    
    // 最終更新日を自動設定
    $last_updated = get_field('last_updated', $post_id);
    if (empty($last_updated)) {
        update_field('last_updated', current_time('Y-m-d H:i:s'), $post_id);
    }
    
    // 数値金額から表示用金額を自動生成（表示用が空の場合）
    $max_amount = get_field('max_amount', $post_id);
    $max_amount_numeric = get_field('max_amount_numeric', $post_id);
    
    if (empty($max_amount) && !empty($max_amount_numeric)) {
        $formatted_amount = gi_format_amount_unified($max_amount_numeric);
        update_field('max_amount', $formatted_amount, $post_id);
    }
    
    // 採択率の検証（0-100の範囲内に制限）
    $adoption_rate = get_field('adoption_rate', $post_id);
    if (!empty($adoption_rate)) {
        $adoption_rate = max(0, min(100, intval($adoption_rate)));
        update_field('adoption_rate', $adoption_rate, $post_id);
    }
    
    // 新規フィールドのデフォルト値設定
    if (empty(get_field('difficulty_level', $post_id))) {
        update_field('difficulty_level', '中級', $post_id);
    }
    
    // 完全連携対応：タクソノミーを優先し、重複ACFフィールドは削除
    // 都道府県・市町村はタクソノミーで管理（ACFフィールド不要）
    delete_field('prefecture_name', $post_id);
    delete_field('target_prefecture', $post_id); 
    delete_field('target_municipality', $post_id);
    
    // Google Sheets同期用のシート更新日を設定
    update_field('sheet_updated', current_time('Y-m-d H:i:s'), $post_id);
});

/**
 * 都道府県コードから名前を取得するヘルパー関数
 */
function gi_get_prefecture_name_by_code($code) {
    $prefectures = array(
        'hokkaido' => '北海道',
        'aomori' => '青森県',
        'iwate' => '岩手県',
        'miyagi' => '宮城県',
        'akita' => '秋田県',
        'yamagata' => '山形県',
        'fukushima' => '福島県',
        'ibaraki' => '茨城県',
        'tochigi' => '栃木県',
        'gunma' => '群馬県',
        'saitama' => '埼玉県',
        'chiba' => '千葉県',
        'tokyo' => '東京都',
        'kanagawa' => '神奈川県',
        'niigata' => '新潟県',
        'toyama' => '富山県',
        'ishikawa' => '石川県',
        'fukui' => '福井県',
        'yamanashi' => '山梨県',
        'nagano' => '長野県',
        'gifu' => '岐阜県',
        'shizuoka' => '静岡県',
        'aichi' => '愛知県',
        'mie' => '三重県',
        'shiga' => '滋賀県',
        'kyoto' => '京都府',
        'osaka' => '大阪府',
        'hyogo' => '兵庫県',
        'nara' => '奈良県',
        'wakayama' => '和歌山県',
        'tottori' => '鳥取県',
        'shimane' => '島根県',
        'okayama' => '岡山県',
        'hiroshima' => '広島県',
        'yamaguchi' => '山口県',
        'tokushima' => '徳島県',
        'kagawa' => '香川県',
        'ehime' => '愛媛県',
        'kochi' => '高知県',
        'fukuoka' => '福岡県',
        'saga' => '佐賀県',
        'nagasaki' => '長崎県',
        'kumamoto' => '熊本県',
        'oita' => '大分県',
        'miyazaki' => '宮崎県',
        'kagoshima' => '鹿児島県',
        'okinawa' => '沖縄県',
    );
    
    return isset($prefectures[$code]) ? $prefectures[$code] : '';
}



/**
 * =============================================================================
 * 4. 互換性関数
 * =============================================================================
 */

/**
 * ACFが無効の場合の代替処理
 */
function gi_ensure_acf_compatibility() {
    if (!function_exists('get_field')) {
        // ACF関数の代替実装
        function get_field($field_name, $post_id = false, $format_value = true) {
            if (!$post_id) $post_id = get_the_ID();
            return get_post_meta($post_id, $field_name, true);
        }
        
        function update_field($field_name, $value, $post_id = false) {
            if (!$post_id) $post_id = get_the_ID();
            return update_post_meta($post_id, $field_name, $value);
        }
        
        function have_rows($field_name, $post_id = false) {
            return false; // リピーターフィールドは使用していないため
        }
        
        function get_sub_field($field_name, $format_value = true) {
            return false; // サブフィールドは使用していないため
        }
    }
}
add_action('init', 'gi_ensure_acf_compatibility', 1);

/**
 * フィールド値の検証（新規フィールド対応）
 */
add_filter('acf/validate_value/name=max_amount_numeric', function($valid, $value, $field, $input) {
    if ($value < 0) {
        $valid = '金額は0以上で入力してください。';
    }
    return $valid;
}, 10, 4);

add_filter('acf/validate_value/name=grant_success_rate', function($valid, $value, $field, $input) {
    if ($value < 0 || $value > 100) {
        $valid = '採択率は0〜100の範囲で入力してください。';
    }
    return $valid;
}, 10, 4);

add_filter('acf/validate_value/name=adoption_rate', function($valid, $value, $field, $input) {
    if ($value < 0 || $value > 100) {
        $valid = '採択率は0〜100の範囲で入力してください。';
    }
    return $valid;
}, 10, 4);

/**
 * 新規フィールドの管理画面表示改善
 */
add_action('admin_head', function() {
    ?>
    <style>
        .acf-field[data-name="views_count"] input {
            background-color: #f5f5f5;
            color: #666;
        }
        .acf-field[data-name="admin_notes"] {
            border-left: 3px solid #2196F3;
            padding-left: 15px;
        }
        .acf-field[data-name="is_featured"] .acf-true-false {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
        }
        .acf-field[data-name="adoption_rate"] {
            background: #e8f5e8;
            padding: 10px;
            border-radius: 5px;
        }
        .acf-field[data-name="difficulty_level"] {
            background: #fff8dc;
            padding: 10px;
            border-radius: 5px;
        }
        .acf-field[data-name="area_notes"] {
            background: #f0f8ff;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <?php
}, 5);

/**
 * 新規フィールドのためのヘルパー関数
 */
function gi_get_difficulty_level_label($level) {
    $levels = array(
        '初級' => '初級（比較的簡単）',
        '中級' => '中級（標準的）',
        '上級' => '上級（難しい）',
        '非常に高い' => '非常に高い（専門的）'
    );
    
    return isset($levels[$level]) ? $levels[$level] : $level;
}

/**
 * 採択率に応じた表示スタイルを取得
 */
function gi_get_adoption_rate_style($rate) {
    $rate = intval($rate);
    
    if ($rate >= 80) {
        return 'high'; // 高採択率
    } elseif ($rate >= 50) {
        return 'medium'; // 中採択率
    } elseif ($rate >= 20) {
        return 'low'; // 低採択率
    } else {
        return 'very-low'; // 非常に低い採択率
    }
}