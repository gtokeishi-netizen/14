<?php
/**
 * Template Name: Contact Page (お問い合わせ)
 * 
 * スタイリッシュな白黒ベース + イエローアクセントのお問い合わせページ
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

get_header(); ?>

<style>
/* ========== Contact Page Styles ========== */

.contact-page {
    background: #ffffff;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.8;
    color: #1a1a1a;
}

.contact-hero {
    padding: 80px 20px 60px;
    text-align: center;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: #ffffff;
}

.contact-hero h1 {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    margin-bottom: 16px;
    letter-spacing: -0.02em;
}

.contact-hero p {
    font-size: 1.1rem;
    opacity: 0.85;
    max-width: 600px;
    margin: 0 auto;
}

.contact-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 20px;
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 60px;
}

.contact-info {
    padding: 40px;
    background: #1a1a1a;
    color: #ffffff;
    border-radius: 16px;
    height: fit-content;
}

.contact-info h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 24px;
    color: #FFD500;
}

.info-item {
    margin-bottom: 32px;
}

.info-item h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-item p {
    opacity: 0.85;
    line-height: 1.7;
}

.contact-form-wrapper {
    background: #ffffff;
    padding: 40px;
    border-radius: 16px;
    border: 2px solid #f0f0f0;
}

.contact-form-wrapper h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 32px;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-group label .required {
    color: #FFD500;
    margin-left: 4px;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #FFD500;
    box-shadow: 0 0 0 3px rgba(255, 213, 0, 0.1);
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.submit-button {
    width: 100%;
    padding: 16px 32px;
    background: #FFD500;
    color: #1a1a1a;
    border: 3px solid #FFD500;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-button:hover {
    background: transparent;
    color: #FFD500;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255, 213, 0, 0.3);
}

.form-note {
    margin-top: 24px;
    padding: 16px;
    background: #f8f8f8;
    border-left: 4px solid #FFD500;
    border-radius: 4px;
    font-size: 0.9rem;
    line-height: 1.6;
}

/* レスポンシブ */
@media (max-width: 768px) {
    .contact-container {
        grid-template-columns: 1fr;
        gap: 40px;
        padding: 60px 20px;
    }
    
    .contact-info,
    .contact-form-wrapper {
        padding: 30px 20px;
    }
}

/* アイコン装飾 */
.icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #FFD500;
    border-radius: 50%;
    font-size: 0.9rem;
}
</style>

<div class="contact-page">
    
    <!-- ヒーローセクション -->
    <section class="contact-hero">
        <h1>お問い合わせ</h1>
        <p>ご質問・ご相談などございましたら、お気軽にお問い合わせください。<br>2営業日以内にご返信いたします。</p>
    </section>

    <!-- メインコンテンツ -->
    <div class="contact-container">
        
        <!-- 左側: お問い合わせ情報 -->
        <aside class="contact-info">
            <h2>📧 連絡先情報</h2>
            
            <div class="info-item">
                <h3><span class="icon-wrapper">📧</span> メールアドレス</h3>
                <p>info@joseikin-insight.com</p>
            </div>
            
            <div class="info-item">
                <h3><span class="icon-wrapper">⏰</span> 対応時間</h3>
                <p>平日 10:00 - 18:00<br>（土日祝日を除く）</p>
            </div>
            
            <div class="info-item">
                <h3><span class="icon-wrapper">💬</span> お問い合わせ内容</h3>
                <p>
                    ・サービスに関するご質問<br>
                    ・助成金情報の掲載依頼<br>
                    ・機能改善のご提案<br>
                    ・技術的なお問い合わせ<br>
                    ・その他ご相談
                </p>
            </div>
            
            <div class="info-item">
                <h3><span class="icon-wrapper">🔒</span> プライバシー</h3>
                <p>
                    お預かりした個人情報は、<br>
                    <a href="<?php echo home_url('/privacy/'); ?>" style="color: #FFD500; text-decoration: underline;">プライバシーポリシー</a>に基づき<br>
                    適切に管理いたします。
                </p>
            </div>
        </aside>

        <!-- 右側: お問い合わせフォーム -->
        <div class="contact-form-wrapper">
            <h2>お問い合わせフォーム</h2>
            
            <form id="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                <input type="hidden" name="action" value="submit_contact_form">
                <?php wp_nonce_field('contact_form_nonce', 'contact_nonce'); ?>
                
                <div class="form-group">
                    <label for="contact_name">
                        お名前 <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="contact_name" 
                           name="contact_name" 
                           class="form-control" 
                           required 
                           placeholder="山田太郎">
                </div>
                
                <div class="form-group">
                    <label for="contact_email">
                        メールアドレス <span class="required">*</span>
                    </label>
                    <input type="email" 
                           id="contact_email" 
                           name="contact_email" 
                           class="form-control" 
                           required 
                           placeholder="example@email.com">
                </div>
                
                <div class="form-group">
                    <label for="contact_company">
                        会社名・団体名
                    </label>
                    <input type="text" 
                           id="contact_company" 
                           name="contact_company" 
                           class="form-control" 
                           placeholder="株式会社〇〇">
                </div>
                
                <div class="form-group">
                    <label for="contact_subject">
                        お問い合わせ件名 <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="contact_subject" 
                           name="contact_subject" 
                           class="form-control" 
                           required 
                           placeholder="サービスについて">
                </div>
                
                <div class="form-group">
                    <label for="contact_message">
                        お問い合わせ内容 <span class="required">*</span>
                    </label>
                    <textarea id="contact_message" 
                              name="contact_message" 
                              class="form-control" 
                              required 
                              placeholder="お問い合わせ内容を詳しくご記入ください..."></textarea>
                </div>
                
                <button type="submit" class="submit-button">
                    送信する
                </button>
                
                <div class="form-note">
                    <strong>※ 送信前にご確認ください</strong><br>
                    ・入力内容に誤りがないかご確認ください<br>
                    ・メールアドレスが正しくないと返信できません<br>
                    ・通常2営業日以内にご返信いたします
                </div>
            </form>
        </div>
        
    </div>

</div>

<script>
// お問い合わせフォーム送信処理
document.getElementById('contact-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('.submit-button');
    const originalText = submitButton.textContent;
    submitButton.textContent = '送信中...';
    submitButton.disabled = true;
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('お問い合わせを受け付けました。\n2営業日以内にご返信いたします。');
            this.reset();
        } else {
            alert('送信に失敗しました。\n時間をおいて再度お試しください。');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('送信に失敗しました。\n時間をおいて再度お試しください。');
    })
    .finally(() => {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
});
</script>

<?php get_footer(); ?>
