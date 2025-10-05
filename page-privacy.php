<?php
/**
 * Template Name: Privacy Policy (プライバシーポリシー)
 * 
 * スタイリッシュな白黒ベース + イエローアクセントのプライバシーポリシーページ
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

get_header(); ?>

<style>
/* ========== Privacy/Terms Page Styles ========== */

.legal-page {
    background: #ffffff;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.8;
    color: #1a1a1a;
}

.legal-hero {
    padding: 80px 20px 60px;
    text-align: center;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: #ffffff;
}

.legal-hero h1 {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    margin-bottom: 16px;
    letter-spacing: -0.02em;
}

.legal-hero p {
    font-size: 1rem;
    opacity: 0.85;
}

.legal-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 80px 20px;
}

.legal-section {
    margin-bottom: 48px;
}

.legal-section h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 3px solid #FFD500;
}

.legal-section h3 {
    font-size: 1.3rem;
    font-weight: 600;
    margin: 32px 0 16px;
    color: #2d2d2d;
}

.legal-section p {
    margin-bottom: 16px;
    line-height: 1.9;
}

.legal-section ul,
.legal-section ol {
    margin: 16px 0 16px 24px;
    line-height: 1.9;
}

.legal-section li {
    margin-bottom: 8px;
}

.legal-note {
    background: #f8f8f8;
    border-left: 4px solid #FFD500;
    padding: 20px;
    margin: 24px 0;
    border-radius: 4px;
}

.legal-table {
    width: 100%;
    border-collapse: collapse;
    margin: 24px 0;
}

.legal-table th,
.legal-table td {
    padding: 12px 16px;
    text-align: left;
    border: 1px solid #e0e0e0;
}

.legal-table th {
    background: #1a1a1a;
    color: #ffffff;
    font-weight: 600;
}

.legal-table tr:nth-child(even) {
    background: #f8f8f8;
}

.last-updated {
    text-align: right;
    color: #666;
    font-size: 0.9rem;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

@media (max-width: 768px) {
    .legal-container {
        padding: 60px 20px;
    }
    
    .legal-section h2 {
        font-size: 1.5rem;
    }
    
    .legal-table {
        font-size: 0.9rem;
    }
    
    .legal-table th,
    .legal-table td {
        padding: 8px 12px;
    }
}
</style>

<div class="legal-page">
    
    <!-- ヒーローセクション -->
    <section class="legal-hero">
        <h1>プライバシーポリシー</h1>
        <p>個人情報の取り扱いについて</p>
    </section>

    <!-- メインコンテンツ -->
    <div class="legal-container">
        
        <div class="legal-note">
            <strong>基本方針</strong><br>
            Grant Insight Perfect（以下「当サービス」）は、ユーザーの個人情報の保護を重要な責務と認識し、
            個人情報保護法および関連法令を遵守し、適切に取り扱います。
        </div>

        <div class="legal-section">
            <h2>1. 事業者情報</h2>
            <table class="legal-table">
                <tr>
                    <th style="width: 200px;">サービス名</th>
                    <td>Grant Insight Perfect（助成金インサイト）</td>
                </tr>
                <tr>
                    <th>運営者</th>
                    <td>［運営者名を記載］</td>
                </tr>
                <tr>
                    <th>所在地</th>
                    <td>［住所を記載］</td>
                </tr>
                <tr>
                    <th>メールアドレス</th>
                    <td>info@joseikin-insight.com</td>
                </tr>
            </table>
        </div>

        <div class="legal-section">
            <h2>2. 取得する個人情報</h2>
            <p>当サービスでは、以下の個人情報を取得することがあります。</p>
            
            <h3>2.1 お問い合わせ時に取得する情報</h3>
            <ul>
                <li>氏名</li>
                <li>メールアドレス</li>
                <li>会社名・団体名（任意）</li>
                <li>お問い合わせ内容</li>
            </ul>
            
            <h3>2.2 自動的に取得する情報</h3>
            <ul>
                <li>IPアドレス</li>
                <li>ブラウザの種類・バージョン</li>
                <li>閲覧ページ・日時</li>
                <li>リファラー情報</li>
                <li>デバイス情報</li>
            </ul>
        </div>

        <div class="legal-section">
            <h2>3. 個人情報の利用目的</h2>
            <p>取得した個人情報は、以下の目的で利用いたします。</p>
            <ol>
                <li>お問い合わせへの対応</li>
                <li>サービスの提供・運営</li>
                <li>サービスの改善・開発</li>
                <li>利用状況の分析</li>
                <li>重要なお知らせの通知</li>
                <li>利用規約違反への対応</li>
                <li>その他、上記利用目的に付随する目的</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>4. 個人情報の第三者提供</h2>
            <p>当サービスは、以下の場合を除き、ユーザーの同意なく第三者に個人情報を提供いたしません。</p>
            <ul>
                <li>法令に基づく場合</li>
                <li>人の生命、身体または財産の保護のために必要がある場合</li>
                <li>公衆衛生の向上または児童の健全な育成の推進のために特に必要がある場合</li>
                <li>国の機関もしくは地方公共団体またはその委託を受けた者が法令の定める事務を遂行することに対して協力する必要がある場合</li>
            </ul>
        </div>

        <div class="legal-section">
            <h2>5. 個人情報の管理</h2>
            <p>当サービスは、個人情報の正確性を保ち、これを安全に管理いたします。</p>
            <ul>
                <li>不正アクセス・紛失・破壊・改ざん・漏洩等の防止のため、適切なセキュリティ対策を実施</li>
                <li>個人情報の取り扱いに関する内部規定の整備</li>
                <li>従業員への教育・研修の実施</li>
                <li>定期的な監査・見直しの実施</li>
            </ul>
        </div>

        <div class="legal-section">
            <h2>6. Cookie（クッキー）の使用</h2>
            <p>当サービスでは、サービスの利便性向上のためCookieを使用しています。</p>
            
            <h3>6.1 Cookieとは</h3>
            <p>
                Cookieとは、Webサイトが利用者のコンピュータに一時的にデータを保存する仕組みです。
                ユーザーは、ブラウザの設定によりCookieの受け取りを拒否することができますが、
                一部機能が利用できなくなる場合があります。
            </p>
            
            <h3>6.2 使用目的</h3>
            <ul>
                <li>サービスの利用状況の把握</li>
                <li>ユーザー体験の向上</li>
                <li>広告配信の最適化</li>
            </ul>
        </div>

        <div class="legal-section">
            <h2>7. アクセス解析ツール</h2>
            <p>当サービスでは、Googleアナリティクス等のアクセス解析ツールを使用しています。</p>
            <p>
                これらのツールはCookieを使用して、個人を特定する情報を含まずにデータを収集します。
                収集されたデータは、各サービス提供者のプライバシーポリシーに基づいて管理されます。
            </p>
            <ul>
                <li><a href="https://policies.google.com/privacy" target="_blank" rel="noopener" style="color: #FFD500; text-decoration: underline;">Googleプライバシーポリシー</a></li>
            </ul>
        </div>

        <div class="legal-section">
            <h2>8. 個人情報の開示・訂正・削除</h2>
            <p>ユーザーは、自己の個人情報について、以下の請求を行うことができます。</p>
            <ul>
                <li>開示請求</li>
                <li>訂正・追加・削除請求</li>
                <li>利用停止・消去請求</li>
            </ul>
            <p>
                請求をされる場合は、お問い合わせフォームよりご連絡ください。
                本人確認の上、合理的な期間内に対応いたします。
            </p>
        </div>

        <div class="legal-section">
            <h2>9. お問い合わせ窓口</h2>
            <p>個人情報の取り扱いに関するお問い合わせは、以下までご連絡ください。</p>
            <div class="legal-note">
                <strong>メールアドレス:</strong> info@joseikin-insight.com<br>
                <strong>対応時間:</strong> 平日 10:00 - 18:00（土日祝日を除く）
            </div>
        </div>

        <div class="legal-section">
            <h2>10. プライバシーポリシーの変更</h2>
            <p>
                当サービスは、法令の変更や事業内容の変更等により、本プライバシーポリシーを変更することがあります。
                変更した場合は、本ページに掲載し、重要な変更の場合はWebサイト上で告知いたします。
            </p>
        </div>

        <div class="last-updated">
            制定日: 2024年10月4日<br>
            最終改定日: 2024年10月4日
        </div>

    </div>

</div>

<?php get_footer(); ?>
