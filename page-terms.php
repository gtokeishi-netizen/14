<?php
/**
 * Template Name: Terms of Service Page (利用規約)
 * 
 * このファイルはWordPressテンプレート階層のためのルートファイルです
 * 実際のテンプレートは pages/templates/page-terms.php にあります
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// pages/templates内の実際のテンプレートファイルを読み込み
$template_path = get_template_directory() . '/pages/templates/page-terms.php';

if (file_exists($template_path)) {
    include $template_path;
} else {
    // フォールバック: 基本的なページテンプレート
    get_header();
    ?>
    <div class="container">
        <h1>Terms of Service Page</h1>
        <p>Terms page template not found. Please check pages/templates/page-terms.php</p>
    </div>
    <?php
    get_footer();
}
?>