<?php
/**
 * Template Name: Terms of Service (利用規約)
 * 
 * スタイリッシュな白黒ベース + イエローアクセントの利用規約ページ
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

get_header(); ?>

<style>
/* Legal page styles are inherited from page-privacy.php */
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
}
</style>

<div class="legal-page">
    
    <!-- ヒーローセクション -->
    <section class="legal-hero">
        <h1>利用規約</h1>
        <p>サービスのご利用にあたって</p>
    </section>

    <!-- メインコンテンツ -->
    <div class="legal-container">
        
        <div class="legal-note">
            <strong>重要なお知らせ</strong><br>
            本利用規約（以下「本規約」）は、Grant Insight Perfect（以下「当サービス」）の利用条件を定めるものです。
            当サービスをご利用いただく前に、必ず本規約をお読みください。
            当サービスをご利用された場合、本規約に同意したものとみなします。
        </div>

        <div class="legal-section">
            <h2>第1条（適用）</h2>
            <ol>
                <li>本規約は、当サービスの利用に関する条件を、ユーザーと当サービス運営者との間で定めるものです。</li>
                <li>当サービスに関する個別規定、ガイドライン等（以下「個別規定等」）は、本規約の一部を構成します。</li>
                <li>本規約の内容と個別規定等の内容が異なる場合は、個別規定等が優先されます。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第2条（定義）</h2>
            <p>本規約において使用する用語の定義は、以下のとおりとします。</p>
            <ol>
                <li>「当サービス」とは、Grant Insight Perfect（助成金インサイト）が提供する助成金・補助金情報検索サービスを指します。</li>
                <li>「ユーザー」とは、当サービスを利用する全ての方を指します。</li>
                <li>「本コンテンツ」とは、当サービスが提供する助成金情報、記事、画像、動画等の情報を指します。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第3条（サービスの内容）</h2>
            <p>当サービスは、以下のサービスを提供します。</p>
            <ol>
                <li>全国の助成金・補助金情報の検索・閲覧機能</li>
                <li>地域、カテゴリ、金額等による詳細検索機能</li>
                <li>助成金情報の詳細表示</li>
                <li>その他、当サービスが提供する機能</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第4条（利用登録）</h2>
            <ol>
                <li>当サービスの基本機能は、登録なしで無料でご利用いただけます。</li>
                <li>将来的に会員機能を追加する場合は、別途利用登録に関する規定を設けます。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第5条（禁止事項）</h2>
            <p>ユーザーは、当サービスの利用にあたり、以下の行為を行ってはなりません。</p>
            <ol>
                <li>法令または公序良俗に違反する行為</li>
                <li>犯罪行為に関連する行為</li>
                <li>当サービスの運営を妨害する行為</li>
                <li>当サービスまたは第三者の知的財産権を侵害する行為</li>
                <li>当サービスまたは第三者の名誉・信用を毀損する行為</li>
                <li>当サービスのサーバーやネットワークに過度な負荷をかける行為</li>
                <li>不正アクセス、クラッキング等の行為</li>
                <li>当サービスの情報を営利目的で利用する行為</li>
                <li>虚偽の情報を登録・提供する行為</li>
                <li>自動化されたツール等を用いた大量アクセス</li>
                <li>その他、当サービスが不適切と判断する行為</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第6条（本コンテンツの利用）</h2>
            <ol>
                <li>本コンテンツの著作権は、当サービスまたは正当な権利者に帰属します。</li>
                <li>ユーザーは、本コンテンツを個人的な利用の範囲でのみ使用できます。</li>
                <li>ユーザーは、当サービスの事前の許可なく、本コンテンツを複製、転載、改変、配布することはできません。</li>
                <li>ユーザーは、本コンテンツを営利目的で利用することはできません。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第7条（情報の正確性）</h2>
            <ol>
                <li>当サービスは、掲載する助成金情報の正確性を保つよう努めますが、情報の完全性・正確性・有用性について保証するものではありません。</li>
                <li>助成金の申請にあたっては、必ず各実施機関の公式サイト等で最新情報をご確認ください。</li>
                <li>当サービスの情報に基づいて行われた行為について、当サービスは一切の責任を負いません。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第8条（サービスの変更・停止）</h2>
            <ol>
                <li>当サービスは、ユーザーへの事前通知なく、サービスの内容を変更・追加・削除することができます。</li>
                <li>当サービスは、以下の場合、ユーザーへの事前通知なく、サービスの全部または一部を停止することができます。
                    <ul>
                        <li>システムの保守・点検を行う場合</li>
                        <li>地震、火災等の不可抗力によりサービスの提供が困難な場合</li>
                        <li>その他、当サービスがサービスの停止が必要と判断した場合</li>
                    </ul>
                </li>
                <li>当サービスは、サービスの変更・停止により生じた損害について、一切の責任を負いません。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第9条（免責事項）</h2>
            <ol>
                <li>当サービスは、サービスに瑕疵がないこと、中断しないこと、エラーが生じないことを保証しません。</li>
                <li>当サービスは、ユーザーが当サービスを利用したことにより生じた損害について、一切の責任を負いません。</li>
                <li>当サービスは、ユーザー間または第三者との間で生じた紛争について、一切の責任を負いません。</li>
                <li>当サービスは、外部サイトへのリンクの内容について、一切の責任を負いません。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第10条（損害賠償）</h2>
            <p>
                ユーザーが本規約に違反して当サービスに損害を与えた場合、
                当サービスは当該ユーザーに対して損害賠償を請求できるものとします。
            </p>
        </div>

        <div class="legal-section">
            <h2>第11条（知的財産権）</h2>
            <ol>
                <li>当サービスおよび本コンテンツに関する知的財産権は、当サービスまたは正当な権利者に帰属します。</li>
                <li>ユーザーは、当サービスの事前の許可なく、知的財産権を侵害する行為を行ってはなりません。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第12条（個人情報の取り扱い）</h2>
            <p>
                当サービスは、<a href="<?php echo home_url('/privacy/'); ?>" style="color: #FFD500; text-decoration: underline;">プライバシーポリシー</a>に基づき、
                個人情報を適切に取り扱います。
            </p>
        </div>

        <div class="legal-section">
            <h2>第13条（利用規約の変更）</h2>
            <ol>
                <li>当サービスは、ユーザーへの事前通知なく、本規約を変更することができます。</li>
                <li>変更後の規約は、当サービスのWebサイトに掲載した時点から効力を生じます。</li>
                <li>変更後の規約に同意できない場合は、当サービスの利用を中止してください。</li>
                <li>変更後も当サービスを利用した場合、変更後の規約に同意したものとみなします。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第14条（準拠法・管轄裁判所）</h2>
            <ol>
                <li>本規約の解釈にあたっては、日本法を準拠法とします。</li>
                <li>当サービスに関する紛争については、当サービスの所在地を管轄する裁判所を専属的合意管轄裁判所とします。</li>
            </ol>
        </div>

        <div class="legal-section">
            <h2>第15条（お問い合わせ）</h2>
            <p>本規約に関するお問い合わせは、以下までご連絡ください。</p>
            <div class="legal-note">
                <strong>メールアドレス:</strong> info@joseikin-insight.com<br>
                <strong>お問い合わせフォーム:</strong> <a href="<?php echo home_url('/contact/'); ?>" style="color: #FFD500; text-decoration: underline;">こちら</a>
            </div>
        </div>

        <div class="last-updated">
            制定日: 2024年10月4日<br>
            最終改定日: 2024年10月4日
        </div>

    </div>

</div>

<?php get_footer(); ?>
