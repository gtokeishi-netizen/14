<?php
/**
 * Flush Permalinks Script
 * 
 * This script forces WordPress to flush the rewrite rules
 * to ensure the /grants/ URL works properly.
 * 
 * Upload this to your WordPress root directory and visit it in browser once.
 * Then delete the file for security.
 */

// Set up WordPress environment
$wp_root = dirname(__FILE__);
require_once($wp_root . '/wp-config.php');
require_once(ABSPATH . 'wp-settings.php');

// Check if user is logged in as admin (security check)
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. You must be logged in as an administrator.');
}

// Flush rewrite rules
flush_rewrite_rules(true);

echo '<h1>Permalinks Flushed Successfully!</h1>';
echo '<p>The rewrite rules have been flushed. The /grants/ URL should now work properly.</p>';
echo '<p><strong>Important:</strong> Please delete this file (flush-permalinks.php) from your server for security reasons.</p>';
echo '<p><a href="/grants/">Test the grants page</a></p>';
echo '<p><a href="/wp-admin/">Go to WordPress Admin</a></p>';

// Also update the flush flag
update_option('gi_permalinks_flushed_v2', true);
update_option('gi_permalinks_flushed_v3', current_time('mysql'));

echo '<p>Debug info:</p>';
echo '<ul>';
echo '<li>Post type "grant" exists: ' . (post_type_exists('grant') ? 'Yes' : 'No') . '</li>';
echo '<li>Archive link: <a href="' . get_post_type_archive_link('grant') . '">' . get_post_type_archive_link('grant') . '</a></li>';
echo '<li>Rewrite rules flushed at: ' . current_time('Y-m-d H:i:s') . '</li>';
echo '</ul>';
?>