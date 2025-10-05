<?php
/**
 * Template Name: About Page (サービスについて)
 * 
 * このファイルはWordPressテンプレート階層のためのルートファイルです
 * 実際のテンプレートは pages/templates/page-about.php にあります
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// pages/templates内の実際のテンプレートファイルを読み込み
$template_path = get_template_directory() . '/pages/templates/page-about.php';

if (file_exists($template_path)) {
    include $template_path;
} else {
    // フォールバック: 基本的なページテンプレート
    get_header();
    ?>
    <div class="container">
        <h1>About Page</h1>
        <p>About page template not found. Please check pages/templates/page-about.php</p>
    </div>
    <?php
    get_footer();
}
?>