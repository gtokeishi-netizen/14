<?php
/**
 * Template Name: About Page (サービスについて)
 * 
 * スタイリッシュな白黒ベース + イエローアクセントのサービス紹介ページ
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

get_header(); ?>

<style>
/* ========== About Page Styles ========== */

/* ベース設定 */
.about-page {
    background: #ffffff;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.8;
    color: #1a1a1a;
}

.about-section {
    padding: 80px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.about-section.dark {
    background: #1a1a1a;
    color: #ffffff;
}

.about-section.light {
    background: #ffffff;
    color: #1a1a1a;
}

.about-section.accent {
    background: #FFD500;
    color: #1a1a1a;
}

/* ヒーローセクション */
.hero-section {
    padding: 120px 20px 80px;
    text-align: center;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: #ffffff;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 900;
    margin-bottom: 24px;
    letter-spacing: -0.02em;
}

.hero-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.5rem);
    font-weight: 300;
    margin-bottom: 40px;
    opacity: 0.9;
}

.hero-description {
    font-size: 1.1rem;
    max-width: 700px;
    margin: 0 auto 40px;
    line-height: 1.8;
    opacity: 0.85;
}

/* セクションタイトル */
.section-title {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 800;
    margin-bottom: 16px;
    letter-spacing: -0.01em;
}

.section-subtitle {
    font-size: 1.1rem;
    opacity: 0.7;
    margin-bottom: 48px;
}

/* 特徴グリッド */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    margin-top: 60px;
}

.feature-card {
    text-align: center;
    padding: 40px 30px;
    border-radius: 16px;
    background: #ffffff;
    border: 2px solid #f0f0f0;
    transition: all 0.3s ease;
}

.dark .feature-card {
    background: #2d2d2d;
    border-color: #3d3d3d;
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    border-color: #FFD500;
}

.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    background: #FFD500;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.feature-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 16px;
}

.feature-description {
    font-size: 1rem;
    line-height: 1.7;
    opacity: 0.8;
}

/* ステップセクション */
.steps-container {
    display: flex;
    flex-direction: column;
    gap: 48px;
    margin-top: 60px;
}

.step-item {
    display: flex;
    gap: 40px;
    align-items: center;
}

.step-item:nth-child(even) {
    flex-direction: row-reverse;
}

.step-number {
    flex-shrink: 0;
    width: 100px;
    height: 100px;
    background: #FFD500;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 900;
    color: #1a1a1a;
}

.step-content {
    flex: 1;
}

.step-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 12px;
}

.step-description {
    font-size: 1.05rem;
    line-height: 1.8;
    opacity: 0.85;
}

/* CTAボタン */
.cta-button {
    display: inline-block;
    padding: 18px 48px;
    background: #FFD500;
    color: #1a1a1a;
    font-size: 1.1rem;
    font-weight: 700;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
    border: 3px solid #FFD500;
}

.cta-button:hover {
    background: transparent;
    color: #FFD500;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255, 213, 0, 0.3);
}

.cta-button.secondary {
    background: transparent;
    color: #1a1a1a;
    border-color: #1a1a1a;
}

.dark .cta-button.secondary {
    color: #ffffff;
    border-color: #ffffff;
}

.cta-button.secondary:hover {
    background: #1a1a1a;
    color: #ffffff;
}

.dark .cta-button.secondary:hover {
    background: #ffffff;
    color: #1a1a1a;
}

/* 統計セクション */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    margin-top: 60px;
    text-align: center;
}

.stat-item {
    padding: 30px 20px;
}

.stat-number {
    font-size: 3.5rem;
    font-weight: 900;
    color: #FFD500;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.8;
}

/* レスポンシブ */
@media (max-width: 768px) {
    .about-section {
        padding: 60px 20px;
    }
    
    .hero-section {
        padding: 80px 20px 60px;
    }
    
    .step-item,
    .step-item:nth-child(even) {
        flex-direction: column;
        text-align: center;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}

/* アニメーション */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}

/* 装飾要素 */
.decorative-line {
    width: 60px;
    height: 4px;
    background: #FFD500;
    margin: 0 auto 32px;
}

.accent-text {
    color: #FFD500;
    font-weight: 700;
}
</style>

<div class="about-page">
    
    <!-- ヒーローセクション -->
    <section class="hero-section fade-in-up">
        <h1 class="hero-title">助成金検索を、もっと<span class="accent-text">シンプル</span>に</h1>
        <p class="hero-subtitle">Grant Insight Perfectは、全国の助成金・補助金情報を一元管理する革新的なプラットフォームです</p>
        <p class="hero-description">
            複雑で分かりづらい助成金情報を、誰でも簡単に検索・比較できるサービスを目指して開発されました。
            Google Sheetsとの完全同期により、常に最新の情報をお届けします。
        </p>
        <div style="margin-top: 40px;">
            <a href="<?php echo home_url('/'); ?>" class="cta-button">助成金を探す</a>
        </div>
    </section>

    <!-- サービスの特徴 -->
    <section class="about-section light">
        <div class="decorative-line"></div>
        <h2 class="section-title">3つの特徴</h2>
        <p class="section-subtitle">使いやすさと正確性を両立したサービス設計</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3 class="feature-title">簡単検索</h3>
                <p class="feature-description">
                    地域・カテゴリ・金額など、複数の条件を組み合わせた詳細検索が可能。
                    あなたにぴったりの助成金が見つかります。
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">リアルタイム更新</h3>
                <p class="feature-description">
                    Google Sheetsとの双方向同期により、助成金情報は常に最新状態。
                    締切や募集状況をリアルタイムで確認できます。
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3 class="feature-title">一元管理</h3>
                <p class="feature-description">
                    全国の助成金情報を1つのプラットフォームに集約。
                    複数のサイトを回る必要はありません。
                </p>
            </div>
        </div>
    </section>

    <!-- 利用の流れ -->
    <section class="about-section dark">
        <div class="decorative-line"></div>
        <h2 class="section-title">ご利用の流れ</h2>
        <p class="section-subtitle">4ステップで簡単に助成金を探せます</p>
        
        <div class="steps-container">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3 class="step-title">条件を設定</h3>
                    <p class="step-description">
                        都道府県、市町村、カテゴリ、助成金額など、希望する条件を選択します。
                        複数の条件を組み合わせることで、より精度の高い検索が可能です。
                    </p>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3 class="step-title">検索結果を確認</h3>
                    <p class="step-description">
                        条件に合った助成金の一覧が表示されます。
                        金額順や締切順など、並べ替えも自由自在。
                    </p>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3 class="step-title">詳細情報を確認</h3>
                    <p class="step-description">
                        気になる助成金をクリックすると、対象者、必要書類、申請方法など、
                        詳細な情報をご覧いただけます。
                    </p>
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3 class="step-title">申請へ進む</h3>
                    <p class="step-description">
                        公式サイトへのリンクから、そのまま申請手続きへ進むことができます。
                        問い合わせ先情報も併せて掲載しています。
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- データの信頼性 -->
    <section class="about-section accent">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <div class="decorative-line" style="background: #1a1a1a;"></div>
            <h2 class="section-title">データの信頼性</h2>
            <p style="font-size: 1.15rem; margin-bottom: 40px; line-height: 1.8;">
                Google Sheetsとの完全連携により、管理者が随時情報を更新。
                古い情報や誤った情報に惑わされることなく、
                <strong>正確で最新の助成金情報</strong>をご提供します。
            </p>
            
            <div class="stats-grid" style="margin-top: 60px;">
                <div class="stat-item">
                    <div class="stat-number" style="color: #1a1a1a;">31</div>
                    <div class="stat-label">データ項目</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #1a1a1a;">100%</div>
                    <div class="stat-label">同期精度</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" style="color: #1a1a1a;">24/7</div>
                    <div class="stat-label">リアルタイム</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 対応情報 -->
    <section class="about-section light">
        <div class="decorative-line"></div>
        <h2 class="section-title">充実の検索項目</h2>
        <p class="section-subtitle">31項目の詳細データで、あなたのニーズに応えます</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📍</div>
                <h3 class="feature-title">地域情報</h3>
                <p class="feature-description">
                    都道府県・市町村単位での検索に対応。
                    地域制限の有無も明確に表示されます。
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3 class="feature-title">金額情報</h3>
                <p class="feature-description">
                    最大助成額、補助率など、金額に関する
                    詳細な情報を掲載しています。
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3 class="feature-title">期限管理</h3>
                <p class="feature-description">
                    申請期限、募集ステータスを
                    リアルタイムで更新・表示します。
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3 class="feature-title">申請情報</h3>
                <p class="feature-description">
                    必要書類、申請方法、難易度など、
                    申請に必要な情報を網羅しています。
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3 class="feature-title">実施機関</h3>
                <p class="feature-description">
                    国・都道府県・市町村など、
                    実施機関の情報も確認できます。
                </p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"></div>
                <h3 class="feature-title">対象情報</h3>
                <p class="feature-description">
                    対象者、対象事業、対象経費など、
                    応募条件を詳しく掲載しています。
                </p>
            </div>
        </div>
    </section>

    <!-- 最終CTA -->
    <section class="about-section dark" style="text-align: center; padding: 100px 20px;">
        <h2 class="section-title" style="margin-bottom: 24px;">今すぐ始める</h2>
        <p style="font-size: 1.15rem; margin-bottom: 48px; opacity: 0.85; max-width: 600px; margin-left: auto; margin-right: auto;">
            あなたのビジネスに最適な助成金を見つけましょう。<br>
            完全無料でご利用いただけます。
        </p>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo home_url('/'); ?>" class="cta-button">助成金を探す</a>
            <a href="<?php echo home_url('/contact/'); ?>" class="cta-button secondary">お問い合わせ</a>
        </div>
    </section>

</div>

<?php get_footer(); ?>
