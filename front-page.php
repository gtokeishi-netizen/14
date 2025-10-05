<?php
/**
 * Grant Insight Perfect - Front Page Template
 * テンプレートパーツを活用したシンプル構成
 * 
 * @package Grant_Insight_Perfect
 * @version 7.0-simple
 */

get_header(); ?>

<style>
/* フロントページ専用スタイル */
.site-main {
    padding: 0;
    background: #ffffff;
}

/* セクション間のスペーシング調整 */
.front-page-section {
    position: relative;
}

.front-page-section + .front-page-section {
    margin-top: -1px; /* セクション間の隙間を削除 */
}

/* スムーススクロール */
html {
    scroll-behavior: smooth;
}

/* セクションアニメーション */
.section-animate {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}

.section-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

/* モバイル最適化 */
@media (max-width: 768px) {
    .site-main {
        overflow-x: hidden;
    }
}
</style>

<main id="main" class="site-main" role="main">

    <?php
    /**
     * 1. Hero Section
     * メインビジュアルとキャッチコピー
     */
    ?>
    <section class="front-page-section section-animate" id="hero-section">
        <?php get_template_part('template-parts/front-page/section', 'hero'); ?>
    </section>

    <?php
    /**
     * 2. Search Section  
     * 助成金検索システム
     */
    ?>
    <section class="front-page-section section-animate" id="search-section">
        <?php get_template_part('template-parts/front-page/section', 'search'); ?>
    </section>

    <?php
    /**
     * 3. Categories Section
     * カテゴリ別ナビゲーション
     */
    ?>
    <section class="front-page-section section-animate" id="categories-section">
        <?php get_template_part('template-parts/front-page/section', 'categories'); ?>
    </section>

    <?php
    /**
     * 4. Category Hierarchy Section
     * カテゴリー階層表示（親→子構造）
     * User Request: 大枠のカテゴリ→中枠の階層構造を実装
     */
    ?>
    <section class="front-page-section section-animate" id="category-hierarchy-section">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 48px;">
                <h2 class="section-title" style="font-size: 32px; font-weight: 700; color: #000000; margin-bottom: 16px;">カテゴリー別に探す</h2>
                <p class="section-description" style="font-size: 16px; color: #666666;">補助金・助成金を分野別に検索できます</p>
            </div>
            <?php get_template_part('template-parts/category', 'hierarchy'); ?>
        </div>
    </section>

</main>

<!-- フローティングナビゲーション削除済み -->

<!-- プログレスバー -->
<div class="scroll-progress" id="scroll-progress"></div>

<style>
/* スクロールプログレスバー */
.scroll-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981, #3b82f6);
    z-index: 9999;
    transition: width 0.1s ease;
    width: 0%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // セクションアニメーション
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                sectionObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // 全セクションを監視
    document.querySelectorAll('.section-animate').forEach(section => {
        sectionObserver.observe(section);
    });
    
    // スクロールプログレスバー
    const progressBar = document.getElementById('scroll-progress');
    
    function updateProgressBar() {
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrolled = window.scrollY;
        const progress = (scrolled / scrollHeight) * 100;
        
        if (progressBar) {
            progressBar.style.width = Math.min(progress, 100) + '%';
        }
    }
    
    // フローティングナビゲーション削除済み
    
    // スクロールイベント（最適化）
    let scrollTimer;
    window.addEventListener('scroll', function() {
        // デバウンス処理
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => {
            updateProgressBar();
        }, 10);
    });
    
    // 初期化
    updateProgressBar();
    
    // パフォーマンス監視
    if ('performance' in window) {
        window.addEventListener('load', function() {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (perfData) {
                console.log('[パフォーマンス] ページ読み込み時間:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
            }
        });
    }
    
    // ページ内リンクのスムーススクロール
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href !== '#' && href !== '#0') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offset = 80; // ヘッダーの高さ分調整
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // リサイズ時の処理（ナビゲーション削除により簡略化）
    // 必要に応じてリサイズ処理をここに追加
    
    console.log('[OK] Grant Insight Perfect - フロントページ初期化完了');
});
</script>

<?php get_footer(); ?>
