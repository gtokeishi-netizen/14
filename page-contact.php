<?php
/**
 * Template Name: Contact Page (お問い合わせ)
 * 
 * このファイルはWordPressテンプレート階層のためのルートファイルです
 * 実際のテンプレートは pages/templates/page-contact.php にあります
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// pages/templates内の実際のテンプレートファイルを読み込み
$template_path = get_template_directory() . '/pages/templates/page-contact.php';

if (file_exists($template_path)) {
    include $template_path;
} else {
    // フォールバック: 基本的なページテンプレート
    get_header();
    ?>
    <div class="container">
        <h1>Contact Page</h1>
        <p>Contact page template not found. Please check pages/templates/page-contact.php</p>
    </div>
    <?php
    get_footer();
}
?>