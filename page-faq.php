<?php
/**
 * Template Name: FAQ Page (よくある質問)
 * 
 * このファイルはWordPressテンプレート階層のためのルートファイルです
 * 実際のテンプレートは pages/templates/page-faq.php にあります
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// pages/templates内の実際のテンプレートファイルを読み込み
$template_path = get_template_directory() . '/pages/templates/page-faq.php';

if (file_exists($template_path)) {
    include $template_path;
} else {
    // フォールバック: 基本的なページテンプレート
    get_header();
    ?>
    <div class="container">
        <h1>FAQ Page</h1>
        <p>FAQ page template not found. Please check pages/templates/page-faq.php</p>
    </div>
    <?php
    get_footer();
}
?>