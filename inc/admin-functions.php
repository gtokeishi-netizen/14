<?php
/**
 * Grant Insight Perfect - Admin Functions (Consolidated)
 * 
 * Consolidated admin functionality including customization, metaboxes, and admin UI.
 * 
 * @package Grant_Insight_Perfect  
 * @version 9.0.0 (Consolidated Edition)
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =============================================================================
 * 1. 管理画面カスタマイズ（基本機能）
 * =============================================================================
 */

/**
 * 管理画面カスタマイズ（強化版）
 */
function gi_admin_init() {
    // 管理画面でのjQuery読み込み
    add_action('admin_enqueue_scripts', function() {
        wp_enqueue_script('jquery');
    });
    
    // 管理画面スタイル
    add_action('admin_head', function() {
        echo '<style>
        .gi-admin-notice {
            border-left: 4px solid #10b981;
            background: #ecfdf5;
            padding: 12px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .gi-admin-notice h3 {
            color: #047857;
            margin: 0 0 8px 0;
            font-size: 16px;
        }
        .gi-admin-notice p {
            color: #065f46;
            margin: 0;
        }
        .notice.inline {
            margin: 15px 0;
        }
        .gi-progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .gi-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }
        </style>';
    });
    
    // 投稿一覧カラム追加
    add_filter('manage_grant_posts_columns', 'gi_add_grant_columns');
    add_action('manage_grant_posts_custom_column', 'gi_grant_column_content', 10, 2);
}
add_action('admin_init', 'gi_admin_init');

/**
 * 助成金一覧にカスタムカラムを追加
 */
function gi_add_grant_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['gi_prefecture'] = '都道府県';
            $new_columns['gi_amount'] = '金額';
            $new_columns['gi_organization'] = '実施組織';
            $new_columns['gi_status'] = 'ステータス';
        }
    }
    return $new_columns;
}

/**
 * カスタムカラムに内容を表示
 */
function gi_grant_column_content($column, $post_id) {
    switch ($column) {
        case 'gi_prefecture':
            $prefecture_terms = get_the_terms($post_id, 'grant_prefecture');
            if ($prefecture_terms && !is_wp_error($prefecture_terms)) {
                echo gi_safe_escape($prefecture_terms[0]->name);
            } else {
                echo '－';
            }
            break;
        case 'gi_amount':
            $amount = gi_safe_get_meta($post_id, 'max_amount');
            echo $amount ? gi_safe_escape($amount) . '万円' : '－';
            break;
        case 'gi_organization':
            echo gi_safe_escape(gi_safe_get_meta($post_id, 'organization', '－'));
            break;
        case 'gi_status':
            $status = gi_map_application_status_ui(gi_safe_get_meta($post_id, 'application_status', 'open'));
            $status_labels = array(
                'active' => '<span style="color: #059669;">募集中</span>',
                'upcoming' => '<span style="color: #d97706;">募集予定</span>',
                'closed' => '<span style="color: #dc2626;">募集終了</span>'
            );
            echo $status_labels[$status] ?? $status;
            break;
    }
}



/**
 * =============================================================================
 * 4. 管理メニューの追加
 * =============================================================================
 */

/**
 * 管理メニューの追加（修正版）
 */
function gi_add_admin_menu() {
    
    // AI設定メニュー追加
    add_menu_page(
        'AI検索設定',
        'AI検索設定',
        'manage_options',
        'gi-ai-settings',
        'gi_ai_settings_page',
        'dashicons-search',
        30
    );
    
    // AI検索統計サブメニュー
    add_submenu_page(
        'gi-ai-settings',
        'AI検索統計',
        '統計・レポート',
        'manage_options',
        'gi-ai-statistics',
        'gi_ai_statistics_page'
    );
}
add_action('admin_menu', 'gi_add_admin_menu');

/**
 * Prefecture Debug Menu（修正版）
 */
function gi_add_prefecture_debug_menu() {
    add_submenu_page(
        'edit.php?post_type=grant',
        '都道府県デバッグ',
        '都道府県デバッグ',
        'manage_options',
        'gi-prefecture-debug',
        'gi_prefecture_debug_page'
    );
    
    // Excel管理とGoogle Sheets連携機能は完全削除済み
}
add_action('admin_menu', 'gi_add_prefecture_debug_menu');

/**
 * =============================================================================
 * 5. Prefecture Debug Page
 * =============================================================================
 */

/**
 * Prefecture Debug Page
 */
function gi_prefecture_debug_page() {
    if (!current_user_can('manage_options')) {
        wp_die('権限がありません。');
    }
    
    // Actions
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'refresh_counts' && wp_verify_nonce($_POST['_wpnonce'], 'gi_prefecture_debug')) {
            delete_transient('gi_prefecture_counts_v2');
            echo '<div class="notice notice-success"><p>カウンターキャッシュをクリアしました。</p></div>';
        }
        
        if ($_POST['action'] === 'ensure_terms' && wp_verify_nonce($_POST['_wpnonce'], 'gi_prefecture_debug')) {
            $missing_count = gi_ensure_prefecture_terms();
            if ($missing_count > 0) {
                echo "<div class='notice notice-success'><p>{$missing_count}個の都道府県タームを作成しました。</p></div>";
            } else {
                echo '<div class="notice notice-info"><p>すべての都道府県タームが存在します。</p></div>';
            }
        }
    }
    
    // Get data
    $prefecture_counts = gi_get_prefecture_counts();
    $assignment_stats = gi_check_grant_prefecture_assignments();
    
    ?>
    <div class="wrap">
        <h1>都道府県デバッグツール</h1>
        
        <div class="gi-admin-notice">
            <h3>統計情報</h3>
            <p><strong>総助成金投稿:</strong> <?php echo $assignment_stats['total_grants']; ?>件</p>
            <p><strong>都道府県設定済み:</strong> <?php echo $assignment_stats['assigned_grants']; ?>件 (<?php echo $assignment_stats['assignment_ratio']; ?>%)</p>
            <p><strong>都道府県未設定:</strong> <?php echo $assignment_stats['unassigned_grants']; ?>件</p>
        </div>
        
        <div class="postbox">
            <h2 class="hndle">🔧 管理ツール</h2>
            <div class="inside">
                <form method="post" style="display:inline-block;margin-right:10px;">
                    <?php wp_nonce_field('gi_prefecture_debug'); ?>
                    <input type="hidden" name="action" value="refresh_counts">
                    <input type="submit" class="button button-primary" value="🔄 カウンターを再計算">
                </form>
                
                <form method="post" style="display:inline-block;">
                    <?php wp_nonce_field('gi_prefecture_debug'); ?>
                    <input type="hidden" name="action" value="ensure_terms">
                    <input type="submit" class="button button-secondary" value="🏷️ 都道府県タームを確認・作成">
                </form>
            </div>
        </div>
        
        <?php if ($assignment_stats['assigned_grants'] > 0) : ?>
        <div class="postbox">
            <h2 class="hndle">都道府県別投稿数</h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:150px;">都道府県</th>
                            <th style="width:100px;">投稿数</th>
                            <th style="width:100px;">地域</th>
                            <th>アクション</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $all_prefectures = gi_get_all_prefectures();
                        foreach ($all_prefectures as $pref) :
                            $count = isset($prefecture_counts[$pref['slug']]) ? $prefecture_counts[$pref['slug']] : 0;
                            if ($count > 0) :
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($pref['name']); ?></strong></td>
                            <td>
                                <span class="badge" style="background:#007cba;color:white;padding:2px 6px;border-radius:3px;font-size:12px;">
                                    <?php echo $count; ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(ucfirst($pref['region'])); ?></td>
                            <td>
                                <?php
                                $prefecture_url = add_query_arg(
                                    array(
                                        'post_type' => 'grant',
                                        'grant_prefecture' => $pref['slug']
                                    ),
                                    admin_url('edit.php')
                                );
                                ?>
                                <a href="<?php echo esc_url($prefecture_url); ?>" class="button button-small">投稿を表示</a>
                            </td>
                        </tr>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else : ?>
        <div class="notice notice-warning">
            <h3>都道府県設定が必要です</h3>
            <p>助成金投稿に都道府県が設定されていません。以下の方法で設定してください：</p>
            <ol>
                <li><strong>手動設定:</strong> <a href="<?php echo admin_url('edit.php?post_type=grant'); ?>">助成金投稿一覧</a> で各投稿を編集し、都道府県を選択</li>
                <li><strong>一括編集:</strong> 投稿一覧で複数選択して一括編集機能を使用</li>
                <li><strong>インポート修正:</strong> インポート機能を使用している場合は、都道府県マッピングを確認</li>
            </ol>
        </div>
        <?php endif; ?>
        
        <div class="postbox">
            <h2 class="hndle">デバッグ情報</h2>
            <div class="inside">
                <p><strong>キャッシュ状態:</strong> <?php echo get_transient('gi_prefecture_counts_v2') !== false ? '有効' : '無効'; ?></p>
                <p><strong>都道府県タクソノミー:</strong> <?php echo taxonomy_exists('grant_prefecture') ? '存在' : '不存在'; ?></p>
                <p><strong>grant投稿タイプ:</strong> <?php echo post_type_exists('grant') ? '存在' : '不存在'; ?></p>
                <p><strong>Debug Mode:</strong> <?php echo defined('WP_DEBUG') && WP_DEBUG ? 'ON' : 'OFF'; ?></p>
            </div>
        </div>
    </div>
    <?php
}



/**
 * =============================================================================
 * 7. AI設定ページ
 * =============================================================================
 */

/**
 * AI設定ページ（簡易版）
 */
function gi_ai_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // 設定の保存処理
    if (isset($_POST['save_ai_settings']) && wp_verify_nonce($_POST['ai_settings_nonce'], 'gi_ai_settings')) {
        $settings = [
            'enable_ai_search' => isset($_POST['enable_ai_search']) ? 1 : 0,
            'enable_voice_input' => isset($_POST['enable_voice_input']) ? 1 : 0,
            'enable_ai_chat' => isset($_POST['enable_ai_chat']) ? 1 : 0
        ];
        
        update_option('gi_ai_settings', $settings);
        
        // OpenAI APIキーの保存
        if (isset($_POST['openai_api_key'])) {
            $api_key = sanitize_text_field($_POST['openai_api_key']);
            gi_set_openai_api_key($api_key);
        }
        
        echo '<div class="notice notice-success"><p>設定を保存しました。</p></div>';
    }
    
    // API接続テスト
    $connection_status = '';
    if (isset($_POST['test_connection']) && wp_verify_nonce($_POST['ai_settings_nonce'], 'gi_ai_settings')) {
        $capabilities = gi_check_ai_capabilities();
        if ($capabilities['openai_configured']) {
            $connection_status = '<div class="notice notice-success"><p>OpenAI APIへの接続が正常です！</p></div>';
        } else {
            $connection_status = '<div class="notice notice-error"><p>OpenAI APIキーが設定されていないか、無効です。</p></div>';
        }
    }
    
    // 現在の設定を取得
    $settings = get_option('gi_ai_settings', [
        'enable_ai_search' => 1,
        'enable_voice_input' => 1,
        'enable_ai_chat' => 1
    ]);
    
    // OpenAI APIキーを取得
    $api_key = gi_get_openai_api_key();
    $api_key_display = !empty($api_key) ? str_repeat('*', 20) . substr($api_key, -4) : '';
    ?>
    <div class="wrap">
        <h1>AI検索設定</h1>
        
        <?php echo $connection_status; ?>
        
        <form method="post" action="">
            <?php wp_nonce_field('gi_ai_settings', 'ai_settings_nonce'); ?>
            
            <!-- OpenAI API設定セクション -->
            <h2>🤖 OpenAI API設定</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="openai_api_key">OpenAI APIキー</label>
                    </th>
                    <td>
                        <input type="password" id="openai_api_key" name="openai_api_key" 
                               value="<?php echo esc_attr($api_key); ?>" 
                               class="regular-text" 
                               placeholder="sk-..." />
                        <p class="description">
                            OpenAI APIキーを入力してください。
                            <?php if (!empty($api_key_display)): ?>
                                <br><strong>現在の設定:</strong> <code><?php echo esc_html($api_key_display); ?></code>
                            <?php endif; ?>
                            <br>APIキーの取得方法: <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">接続テスト</th>
                    <td>
                        <input type="submit" name="test_connection" class="button button-secondary" value="API接続をテスト">
                        <p class="description">OpenAI APIへの接続状況をテストします。</p>
                    </td>
                </tr>
            </table>
            
            <!-- AI機能有効化設定 -->
            <h2>🔧 AI機能設定</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">AI検索を有効化</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_ai_search" value="1" 
                                <?php checked($settings['enable_ai_search'], 1); ?>>
                            AIによる高度な検索機能を有効にする
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">音声入力を有効化</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_voice_input" value="1" 
                                <?php checked($settings['enable_voice_input'], 1); ?>>
                            音声による検索入力を有効にする
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">AIチャットを有効化</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_ai_chat" value="1" 
                                <?php checked($settings['enable_ai_chat'], 1); ?>>
                            AIアシスタントとのチャット機能を有効にする
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_ai_settings" class="button-primary" value="設定を保存">
            </p>
        </form>
        
        <!-- AI機能ステータス表示 -->
        <div class="gi-admin-notice" style="margin-top: 30px;">
            <h3>AI機能ステータス</h3>
            <?php
            $capabilities = gi_check_ai_capabilities();
            echo '<ul>';
            echo '<li><strong>OpenAI API:</strong> ' . ($capabilities['openai_configured'] ? '[OK] 設定済み' : '[NG] 未設定') . '</li>';
            echo '<li><strong>セマンティック検索:</strong> ' . ($capabilities['semantic_search_available'] ? '[OK] 利用可能' : '[NG] 利用不可') . '</li>';
            echo '<li><strong>音声認識:</strong> ' . ($capabilities['voice_recognition_available'] ? '[OK] 利用可能' : '[NG] OpenAI API必要') . '</li>';
            echo '<li><strong>AIチャット:</strong> ' . ($capabilities['chat_available'] ? '[OK] 利用可能' : '[NG] 利用不可') . '</li>';
            echo '</ul>';
            ?>
            <p><strong>注意:</strong> OpenAI APIキーが未設定の場合、基本的なフォールバック機能のみが動作します。</p>
        </div>
        
        <!-- 使用方法ガイド -->
        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px;">
            <h3>📖 使用方法ガイド</h3>
            <ol>
                <li><strong>OpenAI APIキーを取得:</strong> <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>でアカウント作成・APIキー生成</li>
                <li><strong>APIキーを入力:</strong> 上記フォームにAPIキーを入力して保存</li>
                <li><strong>接続テスト:</strong> 「API接続をテスト」ボタンで動作確認</li>
                <li><strong>機能有効化:</strong> 各AI機能のチェックボックスをONにして保存</li>
                <li><strong>フロントページで確認:</strong> サイトのトップページでAI検索機能をテスト</li>
            </ol>
        </div>
        
        <!-- AJAX接続テスト用JavaScript -->
        <script>
        jQuery(document).ready(function($) {
            // フォーム送信時の接続テスト処理
            $('input[name="test_connection"]').click(function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var $statusDiv = $('.gi-admin-notice').last();
                
                // ローディング表示
                $button.val('テスト中...').prop('disabled', true);
                $statusDiv.hide();
                
                // AJAX接続テスト実行
                $.post(ajaxurl, {
                    action: 'gi_test_connection',
                    nonce: '<?php echo wp_create_nonce("gi_ajax_nonce"); ?>'
                }, function(response) {
                    $button.val('API接続をテスト').prop('disabled', false);
                    
                    if (response.success) {
                        $statusDiv.html(
                            '<h3>[OK] API接続テスト成功</h3>' +
                            '<p><strong>メッセージ:</strong> ' + response.data.message + '</p>' +
                            '<p><strong>時刻:</strong> ' + response.data.time + '</p>'
                        ).removeClass('notice-error').addClass('notice-success').show();
                    } else {
                        $statusDiv.html(
                            '<h3>[NG] API接続テスト失敗</h3>' +
                            '<p><strong>エラー:</strong> ' + (response.data.message || response.data) + '</p>' +
                            '<p><strong>詳細:</strong> ' + (response.data.details || 'なし') + '</p>'
                        ).removeClass('notice-success').addClass('notice-error').show();
                    }
                }).fail(function() {
                    $button.val('API接続をテスト').prop('disabled', false);
                    $statusDiv.html(
                        '<h3>[ERROR] 接続エラー</h3>' +
                        '<p>AJAX リクエストに失敗しました。</p>'
                    ).removeClass('notice-success').addClass('notice-error').show();
                });
            });
            
            // APIキー入力時のマスク処理
            $('#openai_api_key').focus(function() {
                if ($(this).val().indexOf('*') === 0) {
                    $(this).val('');
                }
            });
        });
        </script>
    </div>
    <?php
}

/**
 * =============================================================================
 * 8. AI統計ページ
 * =============================================================================
 */

/**
 * AI統計ページ（簡易版）
 */
function gi_ai_statistics_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wpdb;
    
    // テーブルが存在するかチェック
    $search_table = $wpdb->prefix . 'gi_search_history';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$search_table'") === $search_table;
    
    if (!$table_exists) {
        ?>
        <div class="wrap">
            <h1>AI検索統計</h1>
            <div class="notice notice-info">
                <p>統計データテーブルがまだ作成されていません。初回の検索実行時に自動的に作成されます。</p>
            </div>
        </div>
        <?php
        return;
    }
    
    // 統計データの取得
    $total_searches = $wpdb->get_var("SELECT COUNT(*) FROM $search_table") ?: 0;
    
    // チャット履歴テーブル
    $chat_table = $wpdb->prefix . 'gi_chat_history';
    $chat_exists = $wpdb->get_var("SHOW TABLES LIKE '$chat_table'") === $chat_table;
    $total_chats = $chat_exists ? $wpdb->get_var("SELECT COUNT(*) FROM $chat_table WHERE message_type = 'user'") : 0;
    
    // 人気の検索キーワード（直近30日）
    $popular_searches = $wpdb->get_results("
        SELECT search_query, COUNT(*) as count 
        FROM $search_table 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY search_query 
        ORDER BY count DESC 
        LIMIT 10
    ");
    
    // 時間帯別利用状況（直近7日）
    $hourly_stats = $wpdb->get_results("
        SELECT HOUR(created_at) as hour, COUNT(*) as count 
        FROM $search_table 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY HOUR(created_at) 
        ORDER BY hour
    ");
    
    // 日別利用状況（直近30日）
    $daily_stats = $wpdb->get_results("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM $search_table 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at) 
        ORDER BY date DESC
    ");
    
    // 平均検索結果数
    $avg_results = $wpdb->get_var("
        SELECT AVG(results_count) 
        FROM $search_table 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ") ?: 0;
    
    ?>
    <div class="wrap">
        <h1>AI検索統計</h1>
        
        <!-- 統計サマリー -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #333; font-size: 14px;">総検索数</h3>
                <p style="font-size: 32px; font-weight: bold; color: #10b981; margin: 10px 0;">
                    <?php echo number_format($total_searches); ?>
                </p>
                <p style="color: #666; font-size: 12px;">全期間</p>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #333; font-size: 14px;">チャット数</h3>
                <p style="font-size: 32px; font-weight: bold; color: #3b82f6; margin: 10px 0;">
                    <?php echo number_format($total_chats); ?>
                </p>
                <p style="color: #666; font-size: 12px;">AIとの対話数</p>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #333; font-size: 14px;">平均検索結果</h3>
                <p style="font-size: 32px; font-weight: bold; color: #f59e0b; margin: 10px 0;">
                    <?php echo number_format($avg_results, 1); ?>
                </p>
                <p style="color: #666; font-size: 12px;">件/検索</p>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #333; font-size: 14px;">本日の検索</h3>
                <p style="font-size: 32px; font-weight: bold; color: #8b5cf6; margin: 10px 0;">
                    <?php 
                    $today_searches = $wpdb->get_var("
                        SELECT COUNT(*) FROM $search_table 
                        WHERE DATE(created_at) = CURDATE()
                    ") ?: 0;
                    echo number_format($today_searches);
                    ?>
                </p>
                <p style="color: #666; font-size: 12px;"><?php echo date('Y年m月d日'); ?></p>
            </div>
        </div>
        
        <!-- 人気検索キーワード -->
        <?php if (!empty($popular_searches)): ?>
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="font-size: 18px; margin-top: 0;">人気の検索キーワード（過去30日）</h2>
            <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 50px;">順位</th>
                        <th>検索キーワード</th>
                        <th style="width: 100px;">検索回数</th>
                        <th style="width: 120px;">割合</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_month = array_sum(array_column($popular_searches, 'count'));
                    foreach ($popular_searches as $index => $search): 
                        $percentage = ($search->count / $total_month) * 100;
                    ?>
                    <tr>
                        <td><strong><?php echo $index + 1; ?></strong></td>
                        <td>
                            <?php echo esc_html($search->search_query); ?>
                            <?php if ($index < 3): ?>
                                <span style="color: #f59e0b;">[HOT]</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($search->count); ?>回</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <div style="background: #e5e5e5; height: 20px; flex: 1; border-radius: 3px; overflow: hidden;">
                                    <div style="background: #10b981; height: 100%; width: <?php echo $percentage; ?>%;"></div>
                                </div>
                                <span style="font-size: 12px;"><?php echo number_format($percentage, 1); ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- 時間帯別利用状況 -->
        <?php if (!empty($hourly_stats)): ?>
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="font-size: 18px; margin-top: 0;">時間帯別利用状況（過去7日間）</h2>
            <div style="display: flex; align-items: flex-end; height: 200px; gap: 2px; margin-top: 20px;">
                <?php 
                $max_hour = max(array_column($hourly_stats, 'count'));
                for ($h = 0; $h < 24; $h++):
                    $count = 0;
                    foreach ($hourly_stats as $stat) {
                        if ($stat->hour == $h) {
                            $count = $stat->count;
                            break;
                        }
                    }
                    $height = $max_hour > 0 ? ($count / $max_hour) * 100 : 0;
                ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                    <div style="background: <?php echo $height > 0 ? '#3b82f6' : '#e5e5e5'; ?>; 
                                width: 100%; 
                                height: <?php echo max($height, 2); ?>%; 
                                border-radius: 2px 2px 0 0;"
                         title="<?php echo $h; ?>時: <?php echo $count; ?>件"></div>
                    <?php if ($h % 3 == 0): ?>
                    <span style="font-size: 10px; margin-top: 5px;"><?php echo $h; ?>時</span>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- アクション -->
        <div style="margin-top: 30px;">
            <a href="<?php echo admin_url('admin.php?page=gi-ai-settings'); ?>" class="button button-primary">
                AI設定を確認
            </a>
            <button type="button" class="button" onclick="if(confirm('統計データをリセットしますか？')) location.href='?page=gi-ai-statistics&action=reset&nonce=<?php echo wp_create_nonce('reset_stats'); ?>'">
                統計をリセット
            </button>
        </div>
    </div>
    <?php
    
    // リセット処理
    if (isset($_GET['action']) && $_GET['action'] === 'reset' && wp_verify_nonce($_GET['nonce'], 'reset_stats')) {
        $wpdb->query("TRUNCATE TABLE $search_table");
        if ($chat_exists) {
            $wpdb->query("TRUNCATE TABLE $chat_table");
        }
        echo '<div class="notice notice-success"><p>統計データをリセットしました。</p></div>';
        echo '<script>setTimeout(function(){ location.href="?page=gi-ai-statistics"; }, 2000);</script>';
    }
}

/**
 * =============================================================================
 * 9. POST METABOXES - Custom Fields for Grant Posts
 * =============================================================================
 */

class GrantPostMetaboxes {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_grant_metaboxes'));
        add_action('save_post', array($this, 'save_grant_metadata'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_metabox_scripts'));
    }
    
    /**
     * 助成金投稿用メタボックスを追加
     */
    public function add_grant_metaboxes() {
        // WordPress標準のタクソノミーメタボックスを置き換え
        remove_meta_box('grant_categorydiv', 'grant', 'side');
        remove_meta_box('grant_prefecturediv', 'grant', 'side');
        remove_meta_box('grant_municipalitydiv', 'grant', 'side');
        
        // カスタムタクソノミーメタボックス
        add_meta_box(
            'grant-category-metabox',
            '📂 助成金カテゴリー',
            array($this, 'render_category_metabox'),
            'grant',
            'side',
            'high'
        );
        
        add_meta_box(
            'grant-prefecture-metabox',
            '対象都道府県',
            array($this, 'render_prefecture_metabox'),
            'grant',
            'side',
            'high'
        );
        
        add_meta_box(
            'grant-municipality-metabox',
            '🏛️ 対象市町村',
            array($this, 'render_municipality_metabox'),
            'grant',
            'side',
            'high'
        );
    }
    
    /**
     * 助成金カテゴリーメタボックス
     */
    public function render_category_metabox($post) {
        wp_nonce_field('grant_taxonomy_nonce', 'grant_taxonomy_nonce_field');
        
        $categories = get_terms(array(
            'taxonomy' => 'grant_category',
            'hide_empty' => false
        ));
        
        $post_categories = wp_get_post_terms($post->ID, 'grant_category', array('fields' => 'ids'));
        
        ?>
        <div class="grant-metabox-content">
            <div id="grant-category-selection">
                <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="checkbox" 
                                   name="grant_categories[]" 
                                   value="<?php echo esc_attr($category->term_id); ?>"
                                   <?php checked(in_array($category->term_id, $post_categories)); ?>>
                            <?php echo esc_html($category->name); ?>
                            <span style="color: #666;">（<?php echo $category->count; ?>件）</span>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666;">カテゴリーがありません。</p>
                <?php endif; ?>
                
                <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;">
                    <input type="text" id="new_grant_category" placeholder="新しいカテゴリー名" style="width: 70%;">
                    <button type="button" id="add_grant_category" class="button button-small">追加</button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 対象都道府県メタボックス
     */
    public function render_prefecture_metabox($post) {
        $prefectures = get_terms(array(
            'taxonomy' => 'grant_prefecture',
            'hide_empty' => false,
            'orderby' => 'name'
        ));
        
        $post_prefectures = wp_get_post_terms($post->ID, 'grant_prefecture', array('fields' => 'ids'));
        
        ?>
        <div class="grant-metabox-content">
            <div id="grant-prefecture-selection" style="max-height: 300px; overflow-y: auto;">
                <p>
                    <label>
                        <input type="checkbox" id="select_all_prefectures"> 
                        <strong>全国対象（全て選択）</strong>
                    </label>
                </p>
                <div style="border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px;">
                    <?php if (!empty($prefectures) && !is_wp_error($prefectures)): ?>
                        <?php foreach ($prefectures as $prefecture): ?>
                            <label style="display: block; margin-bottom: 6px;">
                                <input type="checkbox" 
                                       name="grant_prefectures[]" 
                                       value="<?php echo esc_attr($prefecture->term_id); ?>"
                                       class="prefecture-checkbox"
                                       <?php checked(in_array($prefecture->term_id, $post_prefectures)); ?>>
                                <?php echo esc_html($prefecture->name); ?>
                                <span style="color: #666;">（<?php echo $prefecture->count; ?>件）</span>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #666;">都道府県データがありません。</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * 対象市町村メタボックス
     */
    public function render_municipality_metabox($post) {
        $municipalities = get_terms(array(
            'taxonomy' => 'grant_municipality',
            'hide_empty' => false,
            'orderby' => 'name'
        ));
        
        $post_municipalities = wp_get_post_terms($post->ID, 'grant_municipality', array('fields' => 'ids'));
        
        ?>
        <div class="grant-metabox-content">
            <div style="margin-bottom: 10px;">
                <input type="text" id="municipality_search" placeholder="市町村を検索..." style="width: 100%;">
            </div>
            
            <div id="grant-municipality-selection" style="max-height: 250px; overflow-y: auto;">
                <?php if (!empty($municipalities) && !is_wp_error($municipalities)): ?>
                    <?php foreach ($municipalities as $municipality): ?>
                        <label style="display: block; margin-bottom: 6px;" class="municipality-option">
                            <input type="checkbox" 
                                   name="grant_municipalities[]" 
                                   value="<?php echo esc_attr($municipality->term_id); ?>"
                                   <?php checked(in_array($municipality->term_id, $post_municipalities)); ?>>
                            <?php echo esc_html($municipality->name); ?>
                            <span style="color: #666;">（<?php echo $municipality->count; ?>件）</span>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666;">市町村データがありません。</p>
                <?php endif; ?>
                
                <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd;">
                    <input type="text" id="new_municipality" placeholder="新しい市町村名" style="width: 70%;">
                    <button type="button" id="add_municipality" class="button button-small">追加</button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * メタボックス用のスクリプトを読み込み
     */
    public function enqueue_metabox_scripts($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        global $post_type;
        if ($post_type !== 'grant') {
            return;
        }
        
        wp_enqueue_script('grant-metaboxes', get_template_directory_uri() . '/assets/js/grant-metaboxes.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('grant-metaboxes', get_template_directory_uri() . '/assets/css/admin-metaboxes.css', array(), '1.0.0');
        
        wp_localize_script('grant-metaboxes', 'grantMetaboxes', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('grant_metaboxes_nonce')
        ));
    }
    
    /**
     * メタデータとタクソノミーの保存
     */
    public function save_grant_metadata($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if (get_post_type($post_id) !== 'grant') return;
        
        if (!isset($_POST['grant_taxonomy_nonce_field']) || 
            !wp_verify_nonce($_POST['grant_taxonomy_nonce_field'], 'grant_taxonomy_nonce')) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) return;
        
        // タクソノミーの保存
        if (isset($_POST['grant_categories'])) {
            $categories = array_map('intval', $_POST['grant_categories']);
            wp_set_post_terms($post_id, $categories, 'grant_category');
        } else {
            wp_set_post_terms($post_id, array(), 'grant_category');
        }
        
        if (isset($_POST['grant_prefectures'])) {
            $prefectures = array_map('intval', $_POST['grant_prefectures']);
            wp_set_post_terms($post_id, $prefectures, 'grant_prefecture');
        } else {
            wp_set_post_terms($post_id, array(), 'grant_prefecture');
        }
        
        if (isset($_POST['grant_municipalities'])) {
            $municipalities = array_map('intval', $_POST['grant_municipalities']);
            wp_set_post_terms($post_id, $municipalities, 'grant_municipality');
        } else {
            wp_set_post_terms($post_id, array(), 'grant_municipality');
        }
    }
}

// タクソノミータームを追加するAJAXハンドラー
add_action('wp_ajax_gi_add_taxonomy_term', function() {
    check_ajax_referer('grant_metaboxes_nonce', 'nonce');
    
    if (!current_user_can('manage_categories')) {
        wp_send_json_error('権限がありません');
        return;
    }
    
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $term_name = sanitize_text_field($_POST['term_name']);
    
    $allowed_taxonomies = array('grant_category', 'grant_municipality', 'grant_prefecture');
    if (!in_array($taxonomy, $allowed_taxonomies)) {
        wp_send_json_error('無効なタクソノミーです');
        return;
    }
    
    if (empty($term_name)) {
        wp_send_json_error('タerm名が入力されていません');
        return;
    }
    
    $existing_term = term_exists($term_name, $taxonomy);
    if ($existing_term) {
        wp_send_json_error('このタームは既に存在します');
        return;
    }
    
    $result = wp_insert_term($term_name, $taxonomy);
    
    if (is_wp_error($result)) {
        wp_send_json_error('タームの作成に失敗しました: ' . $result->get_error_message());
        return;
    }
    
    wp_send_json_success(array(
        'term_id' => $result['term_id'],
        'name' => $term_name,
        'taxonomy' => $taxonomy
    ));
});

// Initialize metaboxes
function gi_init_grant_metaboxes() {
    return GrantPostMetaboxes::getInstance();
}
add_action('init', 'gi_init_grant_metaboxes');



/**
 * =============================================================================
 * 11. デバッグ・ログ出力
 * =============================================================================
 */

// デバッグ情報の出力
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_footer', function() {
        echo '<!-- Admin Customization: Clean version loaded successfully -->';
        echo '<!-- Current User ID: ' . get_current_user_id() . ' -->';
    });
}
