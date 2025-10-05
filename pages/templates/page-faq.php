<?php
/**
 * Template Name: FAQ Page (よくある質問)
 * 
 * スタイリッシュな白黒ベース + イエローアクセントのFAQページ
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

get_header(); ?>

<style>
/* ========== FAQ Page Styles ========== */

/* ベース設定 */
.faq-page {
    background: #ffffff;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.8;
    color: #1a1a1a;
}

/* ヒーローセクション */
.faq-hero {
    padding: 120px 20px 80px;
    text-align: center;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: #ffffff;
}

.faq-hero-title {
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 900;
    margin-bottom: 24px;
    letter-spacing: -0.02em;
}

.faq-hero-subtitle {
    font-size: clamp(1.1rem, 2.5vw, 1.5rem);
    font-weight: 300;
    max-width: 700px;
    margin: 0 auto;
    opacity: 0.9;
    line-height: 1.6;
}

/* メインコンテンツ */
.faq-content {
    max-width: 900px;
    margin: 0 auto;
    padding: 80px 20px;
}

/* カテゴリーナビゲーション */
.faq-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 60px;
    justify-content: center;
    padding: 40px 20px 0;
}

.faq-category-btn {
    padding: 12px 28px;
    background: #f5f5f5;
    border: 2px solid #e0e0e0;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.faq-category-btn:hover,
.faq-category-btn.active {
    background: #FFD500;
    border-color: #FFD500;
    color: #1a1a1a;
    transform: translateY(-2px);
}

/* FAQセクション */
.faq-section {
    margin-bottom: 80px;
}

.faq-section-title {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 40px;
    padding-bottom: 16px;
    border-bottom: 4px solid #FFD500;
    color: #1a1a1a;
}

/* FAQ項目 */
.faq-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.faq-item {
    background: #ffffff;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.faq-item:hover {
    border-color: #FFD500;
    box-shadow: 0 4px 20px rgba(255, 213, 0, 0.15);
}

.faq-item.active {
    border-color: #FFD500;
}

/* FAQ質問部分 */
.faq-question {
    padding: 24px 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 20px;
    background: #ffffff;
    transition: background 0.3s ease;
}

.faq-question:hover {
    background: #f9f9f9;
}

.faq-item.active .faq-question {
    background: #fffdf5;
}

.faq-q-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: #FFD500;
    color: #1a1a1a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 900;
}

.faq-q-text {
    flex: 1;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.6;
}

.faq-toggle-icon {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #666;
    transition: transform 0.3s ease;
}

.faq-item.active .faq-toggle-icon {
    transform: rotate(180deg);
    color: #FFD500;
}

/* FAQ回答部分 */
.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.faq-item.active .faq-answer {
    max-height: 2000px;
}

.faq-answer-inner {
    padding: 0 28px 28px 88px;
    border-top: 1px solid #f0f0f0;
}

.faq-a-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #1a1a1a;
    color: #ffffff;
    border-radius: 50%;
    font-size: 1.1rem;
    font-weight: 900;
    margin-right: 12px;
    margin-bottom: 16px;
    margin-top: 20px;
}

.faq-a-text {
    font-size: 1.05rem;
    line-height: 1.9;
    color: #333;
}

.faq-a-text p {
    margin-bottom: 16px;
}

.faq-a-text ul,
.faq-a-text ol {
    margin: 20px 0;
    padding-left: 24px;
}

.faq-a-text li {
    margin-bottom: 12px;
    line-height: 1.8;
}

.faq-a-text strong {
    color: #1a1a1a;
    font-weight: 700;
}

.faq-a-text a {
    color: #1a1a1a;
    text-decoration: underline;
    font-weight: 600;
}

.faq-a-text a:hover {
    color: #FFD500;
}

/* お問い合わせCTAセクション */
.faq-cta {
    margin-top: 80px;
    padding: 60px 40px;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-radius: 20px;
    text-align: center;
    color: #ffffff;
}

.faq-cta-title {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 16px;
}

.faq-cta-text {
    font-size: 1.1rem;
    margin-bottom: 32px;
    opacity: 0.9;
}

.faq-cta-button {
    display: inline-block;
    padding: 18px 48px;
    background: #FFD500;
    color: #1a1a1a;
    font-size: 1.1rem;
    font-weight: 700;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.faq-cta-button:hover {
    background: #ffffff;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 213, 0, 0.3);
}

/* レスポンシブ */
@media (max-width: 768px) {
    .faq-hero {
        padding: 80px 20px 60px;
    }

    .faq-content {
        padding: 60px 20px;
    }

    .faq-categories {
        padding: 20px 20px 0;
    }

    .faq-category-btn {
        padding: 10px 20px;
        font-size: 0.95rem;
    }

    .faq-section {
        margin-bottom: 60px;
    }

    .faq-section-title {
        font-size: 1.6rem;
        margin-bottom: 30px;
    }

    .faq-question {
        padding: 20px;
        gap: 12px;
    }

    .faq-q-icon {
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
    }

    .faq-q-text {
        font-size: 1.05rem;
    }

    .faq-toggle-icon {
        width: 28px;
        height: 28px;
        font-size: 1.3rem;
    }

    .faq-answer-inner {
        padding: 0 20px 20px 20px;
    }

    .faq-a-icon {
        display: block;
        margin-bottom: 12px;
    }

    .faq-a-text {
        font-size: 1rem;
    }

    .faq-cta {
        padding: 40px 24px;
    }

    .faq-cta-title {
        font-size: 1.6rem;
    }

    .faq-cta-button {
        padding: 16px 40px;
        font-size: 1rem;
    }
}
</style>

<div class="faq-page">
    <!-- ヒーローセクション -->
    <section class="faq-hero">
        <h1 class="faq-hero-title">よくある質問</h1>
        <p class="faq-hero-subtitle">
            Grant Insight Perfectに関してよくいただく質問をまとめました。<br>
            お探しの情報が見つからない場合は、お気軽にお問い合わせください。
        </p>
    </section>

    <!-- カテゴリーナビゲーション -->
    <div class="faq-categories">
        <a href="#basic" class="faq-category-btn active">基本情報</a>
        <a href="#features" class="faq-category-btn">機能について</a>
        <a href="#usage" class="faq-category-btn">使い方</a>
        <a href="#billing" class="faq-category-btn">料金・プラン</a>
        <a href="#technical" class="faq-category-btn">技術的な質問</a>
        <a href="#support" class="faq-category-btn">サポート</a>
    </div>

    <!-- メインコンテンツ -->
    <div class="faq-content">
        <!-- 基本情報 -->
        <section class="faq-section" id="basic">
            <h2 class="faq-section-title">基本情報</h2>
            <div class="faq-items">
                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">Grant Insight Perfectとは何ですか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>Grant Insight Perfectは、全国の助成金・補助金情報を一元管理し、企業や個人事業主の方が最適な支援制度を簡単に見つけられるサービスです。</p>
                                <p>主な特徴：</p>
                                <ul>
                                    <li><strong>全国対応</strong> - 47都道府県すべての助成金情報を網羅</li>
                                    <li><strong>リアルタイム更新</strong> - 最新の募集情報を毎日更新</li>
                                    <li><strong>高度な検索機能</strong> - 業種・地域・金額など多角的に検索可能</li>
                                    <li><strong>申請サポート</strong> - 申請書類作成のサポート機能も完備</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">無料で使えますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>はい、基本的な検索機能は<strong>完全無料</strong>でご利用いただけます。</p>
                                <p>無料プランで利用できる機能：</p>
                                <ul>
                                    <li>助成金・補助金の検索</li>
                                    <li>詳細情報の閲覧</li>
                                    <li>お気に入り登録（最大10件）</li>
                                    <li>メールでの最新情報配信（月1回）</li>
                                </ul>
                                <p>より高度な機能やサポートが必要な場合は、有料プランもご用意しております。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">どのような方が利用していますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>幅広い業種・規模の事業者様にご利用いただいています：</p>
                                <ul>
                                    <li><strong>中小企業・スタートアップ</strong> - 設備投資や新規事業の資金調達に</li>
                                    <li><strong>個人事業主・フリーランス</strong> - 開業資金や事業拡大の支援に</li>
                                    <li><strong>製造業・小売業</strong> - DX推進や省エネ設備導入に</li>
                                    <li><strong>士業・コンサルタント</strong> - クライアント支援のための情報収集に</li>
                                    <li><strong>自治体・商工会議所</strong> - 地域企業への情報提供に</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 機能について -->
        <section class="faq-section" id="features">
            <h2 class="faq-section-title">機能について</h2>
            <div class="faq-items">
                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">どのような助成金情報が掲載されていますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>国・都道府県・市区町村が実施する各種助成金・補助金情報を網羅しています：</p>
                                <ul>
                                    <li><strong>事業再構築補助金</strong> - 新分野展開・業態転換の支援</li>
                                    <li><strong>ものづくり補助金</strong> - 設備投資・試作開発の支援</li>
                                    <li><strong>IT導入補助金</strong> - システム導入・デジタル化の支援</li>
                                    <li><strong>小規模事業者持続化補助金</strong> - 販路開拓・生産性向上の支援</li>
                                    <li><strong>雇用関連助成金</strong> - 採用・人材育成の支援</li>
                                    <li><strong>地域独自の補助金</strong> - 各自治体が実施する支援制度</li>
                                </ul>
                                <p>現在、<strong>1,500件以上</strong>の助成金情報を掲載中です。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">情報の更新頻度はどのくらいですか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p><strong>毎日更新</strong>しています。新しい助成金の公募開始や募集要項の変更があれば、できる限り早く反映するよう努めています。</p>
                                <p>更新内容：</p>
                                <ul>
                                    <li>新規助成金の追加（公募開始当日～3営業日以内）</li>
                                    <li>募集要項の変更（変更発表後24時間以内）</li>
                                    <li>申請期限の更新（リアルタイム）</li>
                                    <li>採択結果の反映（発表後1週間以内）</li>
                                </ul>
                                <p>また、有料プランにご登録いただくと、新着情報を<strong>即座にメール通知</strong>でお届けします。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">自分に合った助成金を見つける方法は?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>複数の方法で最適な助成金を見つけることができます：</p>
                                <ol>
                                    <li><strong>条件検索機能</strong>
                                        <ul>
                                            <li>地域（都道府県・市区町村）</li>
                                            <li>業種・事業内容</li>
                                            <li>補助金額の範囲</li>
                                            <li>申請期限</li>
                                            <li>対象者（個人事業主・法人など）</li>
                                        </ul>
                                    </li>
                                    <li><strong>キーワード検索</strong><br>
                                        「IT導入」「設備投資」「人材育成」などのキーワードで検索
                                    </li>
                                    <li><strong>AIレコメンド機能</strong>（プレミアムプラン）<br>
                                        事業内容を登録すると、AIが最適な助成金を自動提案
                                    </li>
                                    <li><strong>相談サポート</strong>（プレミアムプラン）<br>
                                        専門スタッフが電話・メールで個別にアドバイス
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 使い方 -->
        <section class="faq-section" id="usage">
            <h2 class="faq-section-title">使い方</h2>
            <div class="faq-items">
                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">会員登録は必要ですか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>基本的な検索機能の利用には<strong>会員登録は不要</strong>です。</p>
                                <p>ただし、以下の機能をご利用いただく場合は会員登録（無料）が必要です：</p>
                                <ul>
                                    <li>お気に入り登録</li>
                                    <li>申請状況の管理</li>
                                    <li>メール通知の受信</li>
                                    <li>検索条件の保存</li>
                                    <li>申請サポート機能の利用</li>
                                </ul>
                                <p>会員登録は<strong>メールアドレスのみ</strong>で簡単に行えます。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">検索結果が多すぎて絞り込めません</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>以下の方法で効率的に絞り込むことができます：</p>
                                <ol>
                                    <li><strong>複数条件を組み合わせる</strong>
                                        <ul>
                                            <li>地域 + 業種 + 補助金額</li>
                                            <li>キーワード + 申請期限</li>
                                        </ul>
                                    </li>
                                    <li><strong>並び替え機能を活用</strong>
                                        <ul>
                                            <li>申請期限が近い順</li>
                                            <li>補助金額が多い順</li>
                                            <li>採択率が高い順</li>
                                            <li>新着順</li>
                                        </ul>
                                    </li>
                                    <li><strong>詳細フィルター</strong>
                                        <ul>
                                            <li>補助率（1/2、2/3など）</li>
                                            <li>申請難易度</li>
                                            <li>必要書類の数</li>
                                        </ul>
                                    </li>
                                    <li><strong>除外キーワード設定</strong><br>
                                        不要な情報を除外して表示
                                    </li>
                                </ol>
                                <p>それでも絞り込みが難しい場合は、<a href="/contact/">お問い合わせ</a>ください。専門スタッフがサポートいたします。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">申請書類の作成サポートはありますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>はい、複数のサポート機能をご用意しています：</p>
                                <ul>
                                    <li><strong>申請書類テンプレート</strong>（無料プラン）<br>
                                        各助成金の申請に必要な書類の雛形をダウンロード可能
                                    </li>
                                    <li><strong>記入例・作成ガイド</strong>（無料プラン）<br>
                                        実際の採択事例をもとにした記入のポイントを解説
                                    </li>
                                    <li><strong>書類作成アシスタント</strong>（スタンダードプラン以上）<br>
                                        質問に答えていくだけで申請書類の下書きが完成
                                    </li>
                                    <li><strong>専門家による添削サービス</strong>（プレミアムプラン）<br>
                                        申請書類を専門家が添削し、採択率向上をサポート
                                    </li>
                                    <li><strong>申請代行サービス</strong>（別途見積もり）<br>
                                        申請手続きをすべて代行（提携士業による）
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 料金・プラン -->
        <section class="faq-section" id="billing">
            <h2 class="faq-section-title">料金・プラン</h2>
            <div class="faq-items">
                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">有料プランの料金体系を教えてください</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>3つの有料プランをご用意しています：</p>
                                <ul>
                                    <li><strong>スタンダードプラン</strong> - 月額3,980円（税込）
                                        <ul>
                                            <li>お気に入り無制限</li>
                                            <li>メール通知（週1回）</li>
                                            <li>申請状況管理</li>
                                            <li>書類作成アシスタント</li>
                                        </ul>
                                    </li>
                                    <li><strong>プレミアムプラン</strong> - 月額9,800円（税込）
                                        <ul>
                                            <li>スタンダードの全機能</li>
                                            <li>AIレコメンド機能</li>
                                            <li>メール通知（リアルタイム）</li>
                                            <li>専門家による添削サービス（月1回）</li>
                                            <li>電話・メールサポート</li>
                                        </ul>
                                    </li>
                                    <li><strong>ビジネスプラン</strong> - 月額29,800円（税込）
                                        <ul>
                                            <li>プレミアムの全機能</li>
                                            <li>複数ユーザー対応（最大5名）</li>
                                            <li>専用ダッシュボード</li>
                                            <li>優先サポート</li>
                                            <li>カスタマイズ対応</li>
                                        </ul>
                                    </li>
                                </ul>
                                <p>年間契約で<strong>2ヶ月分お得</strong>になります。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">無料トライアルはありますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>はい、<strong>14日間の無料トライアル</strong>をご用意しています。</p>
                                <p>トライアル期間中は、すべてのプレミアム機能を<strong>無料でお試し</strong>いただけます：</p>
                                <ul>
                                    <li>AIレコメンド機能</li>
                                    <li>書類作成アシスタント</li>
                                    <li>リアルタイム通知</li>
                                    <li>専門家サポート</li>
                                </ul>
                                <p>トライアル期間中の解約も可能で、<strong>自動課金はされません</strong>のでご安心ください。</p>
                                <p>※クレジットカードの登録が必要です（トライアル期間中は請求されません）</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">プランの変更・解約はできますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>はい、いつでも<strong>プラン変更・解約が可能</strong>です。</p>
                                <p><strong>プラン変更について：</strong></p>
                                <ul>
                                    <li>アップグレード：即座に反映、差額は日割り計算</li>
                                    <li>ダウングレード：次回更新日から適用</li>
                                </ul>
                                <p><strong>解約について：</strong></p>
                                <ul>
                                    <li>解約手数料：なし</li>
                                    <li>最低利用期間：なし</li>
                                    <li>解約手続き：マイページから簡単に手続き可能</li>
                                    <li>データ保持：解約後30日間はデータを保持</li>
                                </ul>
                                <p>年間契約の場合は、未使用期間分の払い戻しはございませんので、ご了承ください。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 技術的な質問 -->
        <section class="faq-section" id="technical">
            <h2 class="faq-section-title">技術的な質問</h2>
            <div class="faq-items">
                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">スマートフォンでも利用できますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>はい、<strong>スマートフォン・タブレットに完全対応</strong>しています。</p>
                                <p>対応環境：</p>
                                <ul>
                                    <li><strong>iOS</strong> - Safari、Chrome（iOS 14以上推奨）</li>
                                    <li><strong>Android</strong> - Chrome、Firefox（Android 9以上推奨）</li>
                                    <li><strong>PC</strong> - Chrome、Firefox、Safari、Edge（最新版推奨）</li>
                                </ul>
                                <p>レスポンシブデザインにより、どのデバイスでも快適にご利用いただけます。</p>
                                <p>また、<strong>専用アプリ</strong>（iOS・Android）も近日リリース予定です。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">データのセキュリティは大丈夫ですか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>お客様の情報は厳重に管理しています：</p>
                                <ul>
                                    <li><strong>SSL/TLS暗号化</strong> - 通信は256ビットSSLで暗号化</li>
                                    <li><strong>データベース暗号化</strong> - 保存データも暗号化して管理</li>
                                    <li><strong>定期的なセキュリティ監査</strong> - 第三者機関による監査を実施</li>
                                    <li><strong>アクセス制限</strong> - 権限管理により不正アクセスを防止</li>
                                    <li><strong>バックアップ体制</strong> - 毎日自動バックアップを実施</li>
                                </ul>
                                <p>また、<strong>プライバシーマーク</strong>の取得を準備中です。</p>
                                <p>詳細は<a href="/privacy/">プライバシーポリシー</a>をご確認ください。</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">APIは提供していますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>はい、<strong>ビジネスプラン以上</strong>のお客様に<strong>REST API</strong>を提供しています。</p>
                                <p>APIで利用可能な機能：</p>
                                <ul>
                                    <li>助成金情報の取得</li>
                                    <li>検索機能</li>
                                    <li>カテゴリ・タグ情報の取得</li>
                                    <li>申請期限の取得</li>
                                </ul>
                                <p>API仕様：</p>
                                <ul>
                                    <li>認証方式：APIキー認証</li>
                                    <li>データ形式：JSON</li>
                                    <li>レート制限：1,000リクエスト/日（ビジネスプラン）</li>
                                </ul>
                                <p>APIドキュメントは契約後にご提供いたします。</p>
                                <p>より高いレート制限が必要な場合は、<a href="/contact/">個別にご相談</a>ください。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- サポート -->
        <section class="faq-section" id="support">
            <h2 class="faq-section-title">サポート</h2>
            <div class="faq-items">
                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">サポートの対応時間を教えてください</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>プランによってサポート対応時間が異なります：</p>
                                <ul>
                                    <li><strong>無料プラン</strong><br>
                                        メールサポートのみ<br>
                                        対応時間：平日10:00～18:00<br>
                                        返信目安：3営業日以内
                                    </li>
                                    <li><strong>スタンダードプラン</strong><br>
                                        メールサポート<br>
                                        対応時間：平日9:00～19:00<br>
                                        返信目安：1営業日以内
                                    </li>
                                    <li><strong>プレミアムプラン</strong><br>
                                        電話・メールサポート<br>
                                        対応時間：平日9:00～20:00<br>
                                        返信目安：4時間以内（営業時間内）
                                    </li>
                                    <li><strong>ビジネスプラン</strong><br>
                                        優先サポート（電話・メール・チャット）<br>
                                        対応時間：平日9:00～21:00<br>
                                        返信目安：1時間以内（営業時間内）
                                    </li>
                                </ul>
                                <p>※土日祝日・年末年始（12/29～1/3）は休業日です</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">操作方法がわからない場合はどうすればいいですか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>複数のサポート方法をご用意しています：</p>
                                <ol>
                                    <li><strong>ヘルプセンター</strong><br>
                                        よくある質問や操作ガイドを掲載しています<br>
                                        URL: /help/
                                    </li>
                                    <li><strong>チュートリアル動画</strong><br>
                                        基本的な使い方を動画で解説<br>
                                        各機能画面に「？」アイコンから確認可能
                                    </li>
                                    <li><strong>チャットサポート</strong>（有料プラン）<br>
                                        画面右下のチャットアイコンから質問可能
                                    </li>
                                    <li><strong>メール・電話サポート</strong><br>
                                        専門スタッフが個別にサポート<br>
                                        <a href="/contact/">お問い合わせフォーム</a>からご連絡ください
                                    </li>
                                    <li><strong>オンライン個別相談</strong>（プレミアムプラン以上）<br>
                                        Zoom等での画面共有による個別レクチャー（要予約）
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-q-icon">Q</div>
                        <div class="faq-q-text">助成金申請の相談もできますか?</div>
                        <div class="faq-toggle-icon">▼</div>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            <div class="faq-a-icon">A</div>
                            <div class="faq-a-text">
                                <p>はい、助成金申請に関する相談も承っています：</p>
                                <ul>
                                    <li><strong>無料相談</strong>（すべてのプラン）<br>
                                        一般的な申請の流れや必要書類についてのご質問
                                    </li>
                                    <li><strong>個別相談</strong>（プレミアムプラン以上）<br>
                                        事業内容に合わせた助成金の選び方や申請戦略のアドバイス<br>
                                        月1回30分まで無料（追加相談は有料）
                                    </li>
                                    <li><strong>申請代行サービス</strong>（別途見積もり）<br>
                                        提携する中小企業診断士・行政書士による申請代行<br>
                                        成功報酬型のプランもご用意
                                    </li>
                                </ul>
                                <p>まずは<a href="/contact/">お問い合わせフォーム</a>から、お気軽にご相談ください。</p>
                                <p>※税務・会計に関する相談は対応できません。税理士等の専門家をご紹介することは可能です。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- お問い合わせCTA -->
        <section class="faq-cta">
            <h2 class="faq-cta-title">お探しの情報は見つかりましたか？</h2>
            <p class="faq-cta-text">
                ご不明な点がございましたら、お気軽にお問い合わせください。<br>
                専門スタッフが丁寧にサポートいたします。
            </p>
            <a href="/contact/" class="faq-cta-button">お問い合わせはこちら</a>
        </section>
    </div>
</div>

<script>
// FAQアコーディオン機能
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // 他の開いているアイテムを閉じる（オプション）
            // faqItems.forEach(otherItem => {
            //     if (otherItem !== item) {
            //         otherItem.classList.remove('active');
            //     }
            // });
            
            // クリックされたアイテムをトグル
            item.classList.toggle('active');
        });
    });
    
    // カテゴリーボタンのスムーススクロール
    const categoryButtons = document.querySelectorAll('.faq-category-btn');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            // アクティブ状態を更新
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // スムーススクロール
            const targetId = button.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offset = 80; // ヘッダーの高さ分のオフセット
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ページ読み込み時にURLハッシュに基づいてスクロール
    if (window.location.hash) {
        const targetSection = document.querySelector(window.location.hash);
        if (targetSection) {
            setTimeout(() => {
                const offset = 80;
                const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }, 100);
        }
    }
});
</script>

<?php get_footer(); ?>
