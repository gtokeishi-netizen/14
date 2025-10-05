<?php
/**
 * Disable Auto Sync - Cleanup Script
 * 
 * テーマ有効化時に既存の自動同期設定を無効化
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 自動同期機能を完全に無効化する関数
 */
function gi_disable_auto_sync_completely() {
    // 既存のCronスケジュールをクリア
    wp_clear_scheduled_hook('gi_sheets_sync_cron');
    
    // 自動同期設定を無効に設定
    update_option('gi_sheets_auto_sync_enabled', false);
    update_option('gi_sheets_scheduled_sync_enabled', false);
    
    // 古いcronスケジュールも確認してクリア
    $cron_hooks = array(
        'gi_sheets_sync_cron',
        'gi_auto_sync_hook',
        'sheets_auto_sync',
        'gi_sheets_cron'
    );
    
    foreach ($cron_hooks as $hook) {
        wp_clear_scheduled_hook($hook);
    }
    
    // 設定オプションもクリア
    delete_option('gi_sheets_sync_interval');
    delete_option('gi_sheets_last_auto_sync');
    
    // ログ出力
    if (function_exists('gi_log_error')) {
        gi_log_error('Auto sync disabled completely', array(
            'cleared_crons' => $cron_hooks,
            'timestamp' => current_time('mysql')
        ));
    }
}

// テーマ有効化時に実行
add_action('after_switch_theme', 'gi_disable_auto_sync_completely');

// 管理画面初回アクセス時にも実行（念のため）
add_action('admin_init', function() {
    $disabled = get_option('gi_auto_sync_disabled_flag', false);
    if (!$disabled) {
        gi_disable_auto_sync_completely();
        update_option('gi_auto_sync_disabled_flag', true);
    }
}, 1);

// 手動実行用のWP-CLI コマンド（開発用）
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('gi disable-auto-sync', function() {
        gi_disable_auto_sync_completely();
        WP_CLI::success('Auto sync has been disabled completely.');
    });
}