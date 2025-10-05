<?php
/**
 * Safe Sync Manager for WordPress
 * 
 * WordPress側の安全な同期管理システム
 * - レート制限・API制限対策
 * - エラー監視・復旧機能
 * - 段階的処理とバックプレッシャー制御
 * - 緊急停止機能
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

class SafeSyncManager {
    
    private static $instance = null;
    
    // 安全な同期設定
    const MAX_REQUESTS_PER_MINUTE = 50;    // 1分間の最大リクエスト数
    const MAX_REQUESTS_PER_HOUR = 1000;    // 1時間の最大リクエスト数
    const BATCH_SIZE = 10;                 // バッチ処理サイズ
    const RETRY_ATTEMPTS = 3;              // 最大リトライ回数
    const BACKOFF_DELAY = 2;               // バックオフ遅延（秒）
    const EMERGENCY_THRESHOLD = 10;        // 連続失敗時の緊急停止閾値
    const CLEANUP_INTERVAL = 3600;         // ログクリーンアップ間隔（1時間）
    
    // ステータス定数
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_RATE_LIMITED = 'rate_limited';
    const STATUS_EMERGENCY_STOP = 'emergency_stop';
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->init_rate_limiting();
    }
    
    /**
     * フックの初期化
     */
    private function init_hooks() {
        // REST APIリクエスト前のレート制限チェック
        add_action('rest_api_init', array($this, 'setup_rate_limiting'));
        
        // 定期的なクリーンアップ
        add_action('gi_cleanup_sync_logs', array($this, 'cleanup_sync_logs'));
        
        // WordPress cron設定
        if (!wp_next_scheduled('gi_cleanup_sync_logs')) {
            wp_schedule_event(time(), 'hourly', 'gi_cleanup_sync_logs');
        }
        
        // 管理画面メニュー追加
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * レート制限の初期化
     */
    private function init_rate_limiting() {
        // リクエスト履歴テーブルの作成（必要に応じて）
        $this->maybe_create_rate_limit_table();
    }
    
    /**
     * REST APIレート制限の設定
     */
    public function setup_rate_limiting() {
        // 特定のエンドポイントにレート制限を適用
        add_filter('rest_request_before_callbacks', array($this, 'check_rate_limit'), 10, 3);
    }
    
    /**
     * レート制限チェック
     */
    public function check_rate_limit($response, $handler, $request) {
        // Google Sheets関連エンドポイントのみチェック
        $route = $request->get_route();
        if (strpos($route, '/gi/v1/') !== 0) {
            return $response;
        }
        
        $client_ip = $this->get_client_ip();
        $current_time = current_time('timestamp');
        
        // 緊急停止チェック
        if ($this->is_emergency_stop_active()) {
            return new WP_Error(
                'emergency_stop_active',
                'System is in emergency stop mode',
                array('status' => 503)
            );
        }
        
        // レート制限チェック
        $rate_limit_result = $this->check_client_rate_limit($client_ip, $current_time);
        
        if ($rate_limit_result['limited']) {
            $this->log_rate_limit_violation($client_ip, $route, $rate_limit_result);
            
            return new WP_Error(
                'rate_limit_exceeded',
                'Rate limit exceeded. Please try again later.',
                array(
                    'status' => 429,
                    'retry_after' => $rate_limit_result['retry_after'],
                    'limit_type' => $rate_limit_result['type']
                )
            );
        }
        
        // リクエストを記録
        $this->record_request($client_ip, $route, $current_time);
        
        return $response;
    }
    
    /**
     * クライアントのレート制限チェック
     */
    private function check_client_rate_limit($client_ip, $current_time) {
        $minute_ago = $current_time - 60;
        $hour_ago = $current_time - 3600;
        
        // 1分間のリクエスト数チェック
        $requests_per_minute = $this->get_request_count($client_ip, $minute_ago);
        if ($requests_per_minute >= self::MAX_REQUESTS_PER_MINUTE) {
            return array(
                'limited' => true,
                'type' => 'per_minute',
                'retry_after' => 60,
                'current_count' => $requests_per_minute,
                'limit' => self::MAX_REQUESTS_PER_MINUTE
            );
        }
        
        // 1時間のリクエスト数チェック
        $requests_per_hour = $this->get_request_count($client_ip, $hour_ago);
        if ($requests_per_hour >= self::MAX_REQUESTS_PER_HOUR) {
            return array(
                'limited' => true,
                'type' => 'per_hour',
                'retry_after' => 3600,
                'current_count' => $requests_per_hour,
                'limit' => self::MAX_REQUESTS_PER_HOUR
            );
        }
        
        return array(
            'limited' => false,
            'requests_per_minute' => $requests_per_minute,
            'requests_per_hour' => $requests_per_hour
        );
    }
    
    /**
     * リクエスト数を取得
     */
    private function get_request_count($client_ip, $since_timestamp) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gi_rate_limit_log';
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} 
             WHERE client_ip = %s AND request_time >= %d",
            $client_ip,
            $since_timestamp
        ));
    }
    
    /**
     * リクエストを記録
     */
    private function record_request($client_ip, $route, $timestamp) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gi_rate_limit_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'client_ip' => $client_ip,
                'route' => $route,
                'request_time' => $timestamp,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'status' => 'allowed'
            ),
            array('%s', '%s', '%d', '%s', '%s')
        );
    }
    
    /**
     * レート制限違反をログに記録
     */
    private function log_rate_limit_violation($client_ip, $route, $rate_limit_info) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gi_rate_limit_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'client_ip' => $client_ip,
                'route' => $route,
                'request_time' => current_time('timestamp'),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'status' => 'blocked',
                'reason' => json_encode($rate_limit_info)
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        // 連続違反の監視
        $this->check_continuous_violations($client_ip);
    }
    
    /**
     * 連続違反のチェック
     */
    private function check_continuous_violations($client_ip) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gi_rate_limit_log';
        $five_minutes_ago = current_time('timestamp') - 300;
        
        $violation_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} 
             WHERE client_ip = %s AND status = 'blocked' AND request_time >= %d",
            $client_ip,
            $five_minutes_ago
        ));
        
        if ($violation_count >= self::EMERGENCY_THRESHOLD) {
            $this->trigger_emergency_stop($client_ip, $violation_count);
        }
    }
    
    /**
     * 緊急停止の発動
     */
    private function trigger_emergency_stop($client_ip, $violation_count) {
        // 緊急停止フラグを設定
        update_option('gi_emergency_stop_active', true);
        update_option('gi_emergency_stop_reason', array(
            'client_ip' => $client_ip,
            'violation_count' => $violation_count,
            'timestamp' => current_time('mysql'),
            'auto_triggered' => true
        ));
        
        // 管理者に通知
        $this->send_emergency_notification($client_ip, $violation_count);
        
        // ログ記録
        gi_log_error('Emergency stop triggered', array(
            'client_ip' => $client_ip,
            'violation_count' => $violation_count,
            'trigger_type' => 'automatic'
        ));
    }
    
    /**
     * 緊急停止状態のチェック
     */
    public function is_emergency_stop_active() {
        return get_option('gi_emergency_stop_active', false);
    }
    
    /**
     * 緊急停止の解除
     */
    public function deactivate_emergency_stop() {
        delete_option('gi_emergency_stop_active');
        delete_option('gi_emergency_stop_reason');
        
        gi_log_error('Emergency stop deactivated', array(
            'deactivated_by' => get_current_user_id(),
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * 安全な同期実行
     */
    public function safe_sync_execute($callback, $data, $options = array()) {
        $defaults = array(
            'max_retries' => self::RETRY_ATTEMPTS,
            'backoff_delay' => self::BACKOFF_DELAY,
            'timeout' => 30,
            'priority' => 'normal'
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // 緊急停止チェック
        if ($this->is_emergency_stop_active()) {
            return new WP_Error('emergency_stop', 'Emergency stop is active');
        }
        
        // レート制限チェック
        $client_ip = $this->get_client_ip();
        $rate_check = $this->check_client_rate_limit($client_ip, current_time('timestamp'));
        
        if ($rate_check['limited']) {
            return new WP_Error('rate_limited', 'Rate limit exceeded', $rate_check);
        }
        
        // リトライロジックで実行
        return $this->execute_with_retry($callback, $data, $options);
    }
    
    /**
     * リトライロジック付き実行
     */
    private function execute_with_retry($callback, $data, $options) {
        $last_error = null;
        
        for ($attempt = 1; $attempt <= $options['max_retries']; $attempt++) {
            try {
                $result = call_user_func($callback, $data, $attempt);
                
                if (!is_wp_error($result)) {
                    return $result;
                }
                
                $last_error = $result;
                
                // 特定のエラーではリトライしない
                if (in_array($last_error->get_error_code(), array('emergency_stop', 'rate_limited', 'forbidden'))) {
                    break;
                }
                
                // バックオフ遅延
                if ($attempt < $options['max_retries']) {
                    $delay = $options['backoff_delay'] * pow(2, $attempt - 1); // 指数バックオフ
                    sleep($delay);
                }
                
            } catch (Exception $e) {
                $last_error = new WP_Error('exception', $e->getMessage());
            }
        }
        
        return $last_error;
    }
    
    /**
     * クライアントIPを取得
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // カンマ区切りの場合は最初のIPを使用
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        
        return '127.0.0.1';
    }
    
    /**
     * レート制限テーブルの作成
     */
    private function maybe_create_rate_limit_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gi_rate_limit_log';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            client_ip varchar(45) NOT NULL,
            route varchar(255) NOT NULL,
            request_time int(10) NOT NULL,
            user_agent text,
            status varchar(20) NOT NULL DEFAULT 'allowed',
            reason text,
            PRIMARY KEY (id),
            KEY client_ip (client_ip),
            KEY request_time (request_time),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * 同期ログのクリーンアップ
     */
    public function cleanup_sync_logs() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gi_rate_limit_log';
        $cleanup_time = current_time('timestamp') - (7 * 24 * 3600); // 7日前
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE request_time < %d",
            $cleanup_time
        ));
        
        if ($deleted !== false) {
            gi_log_error('Sync logs cleanup completed', array(
                'deleted_records' => $deleted,
                'cleanup_threshold' => $cleanup_time
            ));
        }
    }
    
    /**
     * 緊急通知の送信
     */
    private function send_emergency_notification($client_ip, $violation_count) {
        $admin_email = get_option('admin_email');
        
        if (!$admin_email) {
            return;
        }
        
        $subject = '[Grant Insight] Emergency Stop Activated';
        $message = sprintf(
            "Emergency stop has been activated due to excessive rate limit violations.\n\n" .
            "Client IP: %s\n" .
            "Violation Count: %d\n" .
            "Time: %s\n\n" .
            "Please review the sync activity and deactivate emergency stop when safe.",
            $client_ip,
            $violation_count,
            current_time('mysql')
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * 管理メニューの追加
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'Safe Sync Manager',
            'Safe Sync Manager',
            'manage_options',
            'gi-safe-sync',
            array($this, 'admin_page')
        );
    }
    
    /**
     * 管理画面ページ
     */
    public function admin_page() {
        if (isset($_POST['action'])) {
            $this->handle_admin_actions();
        }
        
        $this->render_admin_page();
    }
    
    /**
     * 管理画面アクションの処理
     */
    private function handle_admin_actions() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'gi_safe_sync_action')) {
            wp_die('Security check failed');
        }
        
        switch ($_POST['action']) {
            case 'deactivate_emergency_stop':
                $this->deactivate_emergency_stop();
                add_settings_error('gi_safe_sync', 'success', 'Emergency stop deactivated', 'updated');
                break;
                
            case 'activate_emergency_stop':
                update_option('gi_emergency_stop_active', true);
                update_option('gi_emergency_stop_reason', array(
                    'manual_activation' => true,
                    'activated_by' => get_current_user_id(),
                    'timestamp' => current_time('mysql')
                ));
                add_settings_error('gi_safe_sync', 'success', 'Emergency stop activated', 'updated');
                break;
                
            case 'cleanup_logs':
                $this->cleanup_sync_logs();
                add_settings_error('gi_safe_sync', 'success', 'Logs cleaned up', 'updated');
                break;
        }
    }
    
    /**
     * 管理画面の描画
     */
    private function render_admin_page() {
        $emergency_active = $this->is_emergency_stop_active();
        $emergency_reason = get_option('gi_emergency_stop_reason', array());
        
        // 統計情報の取得
        $stats = $this->get_sync_statistics();
        
        ?>
        <div class="wrap">
            <h1>🛡️ Safe Sync Manager</h1>
            
            <?php settings_errors('gi_safe_sync'); ?>
            
            <div class="gi-sync-dashboard">
                <div class="gi-status-card <?php echo $emergency_active ? 'emergency' : 'normal'; ?>">
                    <h3>🚨 System Status</h3>
                    <p><strong>Emergency Stop:</strong> <?php echo $emergency_active ? '🔴 Active' : '🟢 Inactive'; ?></p>
                    
                    <?php if ($emergency_active && !empty($emergency_reason)): ?>
                    <div class="emergency-details">
                        <p><strong>Reason:</strong></p>
                        <pre><?php echo esc_html(json_encode($emergency_reason, JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('gi_safe_sync_action'); ?>
                        <?php if ($emergency_active): ?>
                            <input type="hidden" name="action" value="deactivate_emergency_stop">
                            <button type="submit" class="button button-primary">✅ Deactivate Emergency Stop</button>
                        <?php else: ?>
                            <input type="hidden" name="action" value="activate_emergency_stop">
                            <button type="submit" class="button button-secondary">🚨 Activate Emergency Stop</button>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="gi-stats-card">
                    <h3> Rate Limit Statistics</h3>
                    <table class="widefat">
                        <tr>
                            <td>Requests (Last Hour)</td>
                            <td><?php echo esc_html($stats['requests_last_hour']); ?></td>
                        </tr>
                        <tr>
                            <td>Blocked Requests (Last Hour)</td>
                            <td><?php echo esc_html($stats['blocked_last_hour']); ?></td>
                        </tr>
                        <tr>
                            <td>Rate Limit (Per Minute)</td>
                            <td><?php echo esc_html(self::MAX_REQUESTS_PER_MINUTE); ?></td>
                        </tr>
                        <tr>
                            <td>Rate Limit (Per Hour)</td>
                            <td><?php echo esc_html(self::MAX_REQUESTS_PER_HOUR); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="gi-actions-card">
                    <h3>🔧 Maintenance Actions</h3>
                    <form method="post">
                        <?php wp_nonce_field('gi_safe_sync_action'); ?>
                        <input type="hidden" name="action" value="cleanup_logs">
                        <button type="submit" class="button">🧹 Cleanup Old Logs</button>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
        .gi-sync-dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .gi-status-card, .gi-stats-card, .gi-actions-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            border-radius: 4px;
        }
        
        .gi-status-card.emergency {
            border-left: 4px solid #dc3232;
        }
        
        .gi-status-card.normal {
            border-left: 4px solid #46b450;
        }
        
        .emergency-details {
            background: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .emergency-details pre {
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
        </style>
        <?php
    }
    
    /**
     * 同期統計情報の取得
     */
    private function get_sync_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'gi_rate_limit_log';
        $hour_ago = current_time('timestamp') - 3600;
        
        $requests_last_hour = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE request_time >= %d",
            $hour_ago
        ));
        
        $blocked_last_hour = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE request_time >= %d AND status = 'blocked'",
            $hour_ago
        ));
        
        return array(
            'requests_last_hour' => $requests_last_hour ?: 0,
            'blocked_last_hour' => $blocked_last_hour ?: 0
        );
    }
}

// インスタンスを初期化
function gi_init_safe_sync_manager() {
    return SafeSyncManager::getInstance();
}
add_action('init', 'gi_init_safe_sync_manager', 1);