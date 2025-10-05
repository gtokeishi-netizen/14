<?php
/**
 * 404 Error Page - Monochrome Stylish Design
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0-monochrome
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
    /* モノクロームカラーパレット */
    --black: #000000;
    --gray-900: #1a1a1a;
    --gray-800: #2d2d2d;
    --gray-700: #404040;
    --gray-600: #525252;
    --gray-500: #737373;
    --gray-400: #a3a3a3;
    --gray-300: #d4d4d4;
    --gray-200: #e5e5e5;
    --gray-100: #f5f5f5;
    --white: #ffffff;
    --yellow: #fbbf24;
    
    /* Spacing */
    --spacing-xs: 8px;
    --spacing-sm: 16px;
    --spacing-md: 24px;
    --spacing-lg: 32px;
    --spacing-xl: 48px;
    --spacing-2xl: 64px;
    
    /* Typography */
    --font-size-xs: 12px;
    --font-size-sm: 14px;
    --font-size-base: 16px;
    --font-size-lg: 18px;
    --font-size-xl: 20px;
    --font-size-2xl: 24px;
    --font-size-3xl: 30px;
    --font-size-4xl: 36px;
    --font-size-5xl: 48px;
    --font-size-6xl: 60px;
    --font-size-7xl: 72px;
}

.error-404-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--white);
    position: relative;
    overflow: hidden;
    padding: var(--spacing-2xl) var(--spacing-md);
}

/* 背景エフェクト */
.error-bg-effects {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.error-grid-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        linear-gradient(var(--gray-200) 1px, transparent 1px),
        linear-gradient(90deg, var(--gray-200) 1px, transparent 1px);
    background-size: 50px 50px;
    opacity: 0.3;
}

.error-gradient-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 50%, transparent 0%, var(--white) 70%);
}

.error-floating-shapes {
    position: absolute;
    inset: 0;
}

.error-shape {
    position: absolute;
    opacity: 0.05;
}

.error-shape-1 {
    top: 10%;
    left: 10%;
    width: 200px;
    height: 200px;
    background: var(--black);
    border-radius: 50%;
    animation: float 20s ease-in-out infinite;
}

.error-shape-2 {
    bottom: 15%;
    right: 15%;
    width: 150px;
    height: 150px;
    background: var(--gray-900);
    transform: rotate(45deg);
    animation: float 25s ease-in-out infinite reverse;
}

.error-shape-3 {
    top: 50%;
    right: 10%;
    width: 100px;
    height: 100px;
    border: 3px solid var(--black);
    border-radius: 50%;
    animation: float 30s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-30px) rotate(180deg);
    }
}

/* メインコンテンツ */
.error-content {
    max-width: 800px;
    text-align: center;
    position: relative;
    z-index: 1;
}

/* 404ナンバー */
.error-number-container {
    position: relative;
    margin-bottom: var(--spacing-xl);
}

.error-404-number {
    font-size: clamp(80px, 15vw, 180px);
    font-weight: 900;
    line-height: 1;
    color: transparent;
    -webkit-text-stroke: 3px var(--black);
    text-stroke: 3px var(--black);
    letter-spacing: -0.05em;
    margin: 0;
    position: relative;
    display: inline-block;
}

.error-404-number::before {
    content: '404';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--black) 0%, var(--gray-700) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    opacity: 0.15;
    animation: glitch 3s ease-in-out infinite;
}

@keyframes glitch {
    0%, 100% {
        transform: translate(0);
    }
    33% {
        transform: translate(-2px, 2px);
    }
    66% {
        transform: translate(2px, -2px);
    }
}

/* アイコン */
.error-icon-container {
    margin-bottom: var(--spacing-lg);
    display: flex;
    justify-content: center;
    gap: var(--spacing-md);
}

.error-icon {
    width: 60px;
    height: 60px;
    background: var(--gray-100);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--black);
    animation: bounce 2s ease-in-out infinite;
}

.error-icon:nth-child(2) {
    animation-delay: 0.2s;
}

.error-icon:nth-child(3) {
    animation-delay: 0.4s;
}

.error-icon i {
    font-size: 24px;
    color: var(--black);
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* テキストコンテンツ */
.error-title {
    font-size: var(--font-size-4xl);
    font-weight: 800;
    color: var(--black);
    margin-bottom: var(--spacing-md);
    line-height: 1.2;
}

.error-description {
    font-size: var(--font-size-lg);
    color: var(--gray-600);
    margin-bottom: var(--spacing-xl);
    line-height: 1.6;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* 検索ボックス */
.error-search-box {
    max-width: 500px;
    margin: 0 auto var(--spacing-xl);
    position: relative;
}

.error-search-input {
    width: 100%;
    padding: 16px 60px 16px 24px;
    font-size: var(--font-size-base);
    border: 2px solid var(--black);
    border-radius: 50px;
    outline: none;
    transition: all 0.3s ease;
    background: var(--white);
}

.error-search-input:focus {
    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
}

.error-search-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    background: var(--black);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.error-search-btn:hover {
    background: var(--gray-800);
    transform: translateY(-50%) scale(1.05);
}

.error-search-btn i {
    color: var(--white);
    font-size: 16px;
}

/* クイックリンク */
.error-quick-links {
    display: flex;
    justify-content: center;
    gap: var(--spacing-md);
    flex-wrap: wrap;
    margin-bottom: var(--spacing-xl);
}

.error-link-btn {
    padding: 14px 28px;
    background: transparent;
    border: 2px solid var(--black);
    color: var(--black);
    text-decoration: none;
    font-weight: 600;
    font-size: var(--font-size-sm);
    border-radius: 50px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.05em;
    position: relative;
    overflow: hidden;
}

.error-link-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--black);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
    z-index: -1;
}

.error-link-btn:hover::before {
    transform: scaleX(1);
}

.error-link-btn:hover {
    color: var(--white);
}

.error-link-btn.primary {
    background: var(--black);
    color: var(--white);
}

.error-link-btn.primary:hover {
    background: var(--gray-800);
}

.error-link-btn i {
    font-size: 16px;
}

/* 人気キーワード */
.error-suggestions {
    margin-top: var(--spacing-xl);
}

.error-suggestions-title {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    margin-bottom: var(--spacing-md);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.error-keywords {
    display: flex;
    justify-content: center;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.error-keyword {
    padding: 8px 16px;
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    color: var(--gray-700);
    text-decoration: none;
    font-size: var(--font-size-sm);
    border-radius: 20px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.error-keyword:hover {
    background: var(--black);
    color: var(--white);
    border-color: var(--black);
    transform: translateY(-2px);
}

.error-keyword i {
    font-size: 12px;
}

/* レスポンシブ */
@media (max-width: 768px) {
    .error-404-page {
        padding: var(--spacing-xl) var(--spacing-md);
    }
    
    .error-404-number {
        font-size: clamp(60px, 20vw, 120px);
        -webkit-text-stroke: 2px var(--black);
    }
    
    .error-icon-container {
        gap: var(--spacing-sm);
    }
    
    .error-icon {
        width: 50px;
        height: 50px;
    }
    
    .error-icon i {
        font-size: 20px;
    }
    
    .error-title {
        font-size: var(--font-size-2xl);
    }
    
    .error-description {
        font-size: var(--font-size-base);
    }
    
    .error-quick-links {
        flex-direction: column;
        align-items: stretch;
        max-width: 300px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .error-link-btn {
        justify-content: center;
    }
}

@media (max-width: 640px) {
    .error-404-page {
        padding: var(--spacing-lg) var(--spacing-sm);
    }
    
    .error-404-number {
        -webkit-text-stroke: 1.5px var(--black);
    }
    
    .error-icon {
        width: 40px;
        height: 40px;
    }
    
    .error-icon i {
        font-size: 16px;
    }
    
    .error-title {
        font-size: var(--font-size-xl);
    }
    
    .error-description {
        font-size: var(--font-size-sm);
    }
    
    .error-search-input {
        padding: 12px 50px 12px 20px;
        font-size: var(--font-size-sm);
    }
    
    .error-search-btn {
        width: 38px;
        height: 38px;
    }
    
    .error-link-btn {
        padding: 12px 24px;
        font-size: var(--font-size-xs);
    }
    
    .error-keyword {
        font-size: var(--font-size-xs);
        padding: 6px 12px;
    }
}

@media (max-width: 375px) {
    .error-404-page {
        padding: var(--spacing-md) var(--spacing-xs);
    }
    
    .error-title {
        font-size: var(--font-size-lg);
    }
    
    .error-description {
        font-size: 13px;
    }
}
</style>

<main class="error-404-page">
    <!-- 背景エフェクト -->
    <div class="error-bg-effects">
        <div class="error-grid-pattern"></div>
        <div class="error-gradient-overlay"></div>
        <div class="error-floating-shapes">
            <div class="error-shape error-shape-1"></div>
            <div class="error-shape error-shape-2"></div>
            <div class="error-shape error-shape-3"></div>
        </div>
    </div>

    <!-- メインコンテンツ -->
    <div class="error-content">
        <!-- 404ナンバー -->
        <div class="error-number-container">
            <h1 class="error-404-number">404</h1>
        </div>

        <!-- アイコン -->
        <div class="error-icon-container">
            <div class="error-icon">
                <i class="fas fa-search"></i>
            </div>
            <div class="error-icon">
                <i class="fas fa-exclamation"></i>
            </div>
            <div class="error-icon">
                <i class="fas fa-compass"></i>
            </div>
        </div>

        <!-- テキスト -->
        <h2 class="error-title">ページが見つかりませんでした</h2>
        <p class="error-description">
            お探しのページは移動または削除された可能性があります。<br>
            以下の方法をお試しください。
        </p>

        <!-- 検索ボックス -->
        <div class="error-search-box">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input 
                    type="search" 
                    class="error-search-input" 
                    placeholder="キーワードで検索..." 
                    name="s"
                    value="<?php echo get_search_query(); ?>"
                    aria-label="検索"
                >
                <button type="submit" class="error-search-btn" aria-label="検索実行">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- クイックリンク -->
        <div class="error-quick-links">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="error-link-btn primary">
                <i class="fas fa-home"></i>
                <span>トップページへ</span>
            </a>
            <a href="<?php echo esc_url(get_post_type_archive_link('grant')); ?>" class="error-link-btn">
                <i class="fas fa-list"></i>
                <span>助成金一覧</span>
            </a>
            <a href="<?php echo esc_url(home_url('/#ai-search-section')); ?>" class="error-link-btn">
                <i class="fas fa-robot"></i>
                <span>AI検索を使う</span>
            </a>
        </div>

        <!-- 人気キーワード -->
        <div class="error-suggestions">
            <p class="error-suggestions-title">人気のキーワード</p>
            <div class="error-keywords">
                <?php
                $popular_keywords = array(
                    array('text' => 'IT導入補助金', 'icon' => 'fa-laptop-code'),
                    array('text' => 'ものづくり補助金', 'icon' => 'fa-industry'),
                    array('text' => '創業支援', 'icon' => 'fa-rocket'),
                    array('text' => '小規模事業者', 'icon' => 'fa-store'),
                    array('text' => '事業再構築', 'icon' => 'fa-arrows-rotate'),
                    array('text' => '東京都', 'icon' => 'fa-location-dot'),
                );
                
                foreach ($popular_keywords as $keyword) :
                    $search_url = add_query_arg('s', urlencode($keyword['text']), home_url('/'));
                ?>
                    <a href="<?php echo esc_url($search_url); ?>" class="error-keyword">
                        <i class="fas <?php echo esc_attr($keyword['icon']); ?>"></i>
                        <span><?php echo esc_html($keyword['text']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
?>
