<?php
/**
 * AI-Powered Grant Search Section - Complete Integration (Fixed Version)
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.1 - Button Click Fix
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// セッションID生成
$session_id = 'gi_session_' . wp_generate_uuid4();
$nonce = wp_create_nonce('gi_ai_search_nonce');
?>

<!-- AI Grant Search Section - Monochrome Professional Edition -->
<section id="ai-search-section" class="monochrome-ai-search" data-session-id="<?php echo esc_attr($session_id); ?>">
    <!-- 背景エフェクト（カテゴリーセクションと同じ） -->
    <div class="background-effects">
        <div class="grid-pattern"></div>
        <div class="gradient-overlay"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>

    <div class="section-container">
        
        <!-- Section Header（統計情報削除版） -->
        <div class="section-header" data-aos="fade-up">
            <div class="header-accent"></div>
            
            <h2 class="section-title">
                <span class="title-en">AI POWERED SEARCH</span>
                <span class="title-ja">補助金AI検索</span>
                <div class="yellow-marker"></div>
            </h2>
            
            <p class="section-description">
                最適な補助金を瞬時に発見
            </p>
        </div>

        <!-- Main Search Interface -->
        <div class="ai-search-interface">
            
            <!-- Search Bar -->
            <div class="ai-search-bar">
                <div class="search-input-wrapper">
                    <input 
                        type="text" 
                        id="ai-search-input" 
                        class="search-input"
                        placeholder="業種、地域、目的などを入力..."
                        autocomplete="off">
                    <div class="search-actions">
                        <button class="voice-btn" aria-label="音声入力">
                            音声
                        </button>
                        <button id="ai-search-btn" class="search-btn">
                            <span class="btn-text">検索</span>
                        </button>
                    </div>
                </div>
                <div class="search-suggestions" id="search-suggestions"></div>
            </div>

            <!-- Quick Filters -->
            <div class="quick-filters">
                <button class="filter-chip active" data-filter="all">すべて</button>
                <button class="filter-chip" data-filter="it">IT導入</button>
                <button class="filter-chip" data-filter="manufacturing">ものづくり</button>
                <button class="filter-chip" data-filter="startup">創業支援</button>
                <button class="filter-chip" data-filter="sustainability">持続化</button>
                <button class="filter-chip" data-filter="innovation">事業再構築</button>
                <button class="filter-chip" data-filter="employment">雇用関連</button>
            </div>

            <!-- AI Chat & Results -->
            <div class="ai-main-content">
                
                <!-- Left: AI Assistant -->
                <div class="ai-assistant-panel">
                    <div class="assistant-header">
                        <div class="assistant-avatar">
                            <div class="avatar-ring"></div>
                            <span class="avatar-icon">AI</span>
                        </div>
                        <div class="assistant-info">
                            <h3 class="assistant-name">補助金AIアシスタント</h3>
                            <span class="assistant-status">オンライン</span>
                        </div>
                        <button class="ai-history-btn" onclick="toggleChatHistory()" title="会話履歴">
                            履歴
                            <span class="history-count">0</span>
                        </button>
                    </div>
                    
                    <!-- AI会話履歴パネル -->
                    <div class="ai-history-panel" id="ai-history-panel" style="display:none;">
                        <div class="ai-history-header">
                            <h4>会話履歴</h4>
                            <button onclick="clearChatHistory()" class="ai-history-clear">クリア</button>
                        </div>
                        <div class="ai-history-list" id="ai-history-list">
                            <p class="ai-history-empty">履歴がありません</p>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <div class="message message-ai">
                            <div class="message-bubble">
                                どのような補助金をお探しですか？<br>
                                業種や目的をお聞かせください。
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-input-area">
                        <div class="typing-indicator" id="typing-indicator">
                            <span></span><span></span><span></span>
                        </div>
                        <textarea 
                            id="chat-input" 
                            class="chat-input"
                            placeholder="質問を入力してください"
                            rows="1"></textarea>
                        <button id="chat-send" class="chat-send-btn">送信</button>
                    </div>

                    <!-- Quick Questions -->
                    <div class="quick-questions" id="quick-questions">
                        <button class="quick-q" data-q="申請の流れを教えて">申請の流れ</button>
                        <button class="quick-q" data-q="必要書類は？">必要書類</button>
                        <button class="quick-q" data-q="締切はいつ？">締切確認</button>
                        <button class="quick-q" data-q="採択率は？">採択率</button>
                    </div>
                </div>

                <!-- Right: Search Results -->
                <div class="search-results-panel">
                    <div class="results-header">
                        <h3 class="results-title">
                            <span id="results-count">0</span>件の補助金
                        </h3>
                        <div class="view-controls">
                            <button class="view-btn active" data-view="grid">
                                グリッド
                            </button>
                            <button class="view-btn" data-view="list">
                                リスト
                            </button>
                        </div>
                    </div>
                    
                    <div class="results-container" id="results-container">
                        <!-- Initial Featured Grants -->
                        <div class="featured-grants">
                            <?php
                            // 注目の補助金を表示
                            $featured_grants = get_posts([
                                'post_type' => 'grant',
                                'posts_per_page' => 6,
                                'meta_key' => 'is_featured',
                                'meta_value' => '1',
                                'orderby' => 'date',
                                'order' => 'DESC'
                            ]);
                            
                            foreach ($featured_grants as $grant):
                                $amount = get_post_meta($grant->ID, 'max_amount', true);
                                $deadline = get_post_meta($grant->ID, 'deadline', true);
                                $organization = get_post_meta($grant->ID, 'organization', true);
                                $success_rate = get_post_meta($grant->ID, 'grant_success_rate', true);
                            ?>
                            <div class="grant-card" data-id="<?php echo $grant->ID; ?>">
                                <div class="card-badge">注目</div>
                                <h4 class="card-title"><?php echo esc_html($grant->post_title); ?></h4>
                                <div class="card-meta">
                                    <span class="meta-item">
                                        <span class="meta-label">最大</span>
                                        <span class="meta-value"><?php echo esc_html($amount ?: '未定'); ?></span>
                                    </span>
                                    <span class="meta-item">
                                        <span class="meta-label">締切</span>
                                        <span class="meta-value"><?php echo esc_html($deadline ?: '随時'); ?></span>
                                    </span>
                                </div>
                                <p class="card-org"><?php echo esc_html($organization); ?></p>
                                <?php if ($success_rate): ?>
                                <div class="card-rate">
                                    <div class="rate-bar">
                                        <div class="rate-fill" style="width: <?php echo $success_rate; ?>%"></div>
                                    </div>
                                    <span class="rate-text">採択率 <?php echo $success_rate; ?>%</span>
                                </div>
                                <?php endif; ?>
                                <div class="card-actions">
                                    <button class="ai-assist-btn" 
                                            data-grant-id="<?php echo $grant->ID; ?>" 
                                            data-post-id="<?php echo $grant->ID; ?>"
                                            data-grant-title="<?php echo esc_attr($grant->post_title); ?>">
                                        AI質問
                                    </button>
                                    <a href="<?php echo get_permalink($grant->ID); ?>" class="card-link">
                                        詳細を見る
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="results-loading" id="results-loading">
                        <div class="loading-spinner"></div>
                        <span>検索中...</span>
                    </div>
                </div>
            </div>


        </div>
    </div>
</section>

<style>
/* Monochrome AI Search Section Styles */
.monochrome-ai-search {
    position: relative;
    padding: 120px 0;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    font-family: 'Inter', 'Noto Sans JP', -apple-system, sans-serif;
    overflow: hidden;
}

/* 背景エフェクト（カテゴリーセクションと統一） */
.background-effects {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.grid-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        linear-gradient(rgba(0, 0, 0, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: grid-move 20s linear infinite;
}

@keyframes grid-move {
    0% { transform: translate(0, 0); }
    100% { transform: translate(50px, 50px); }
}

.gradient-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 50%, rgba(0, 0, 0, 0.02) 0%, transparent 70%);
}

.floating-shapes {
    position: absolute;
    inset: 0;
}

.shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.05;
}

.shape-1 {
    width: 400px;
    height: 400px;
    background: #000;
    top: -200px;
    left: -200px;
    animation: float-1 20s ease-in-out infinite;
}

.shape-2 {
    width: 300px;
    height: 300px;
    background: #333;
    bottom: -150px;
    right: -150px;
    animation: float-2 25s ease-in-out infinite;
}

.shape-3 {
    width: 250px;
    height: 250px;
    background: #666;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: float-3 30s ease-in-out infinite;
}

@keyframes float-1 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(100px, 50px) scale(1.1); }
}

@keyframes float-2 {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    50% { transform: translate(-50px, -100px) rotate(180deg); }
}

@keyframes float-3 {
    0%, 100% { transform: translate(-50%, -50%) scale(1); }
    50% { transform: translate(-50%, -50%) scale(1.2); }
}

.section-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
    position: relative;
    z-index: 1;
}

/* セクションヘッダー（統計情報削除版） */
.section-header {
    text-align: center;
    margin-bottom: 80px;
    position: relative;
}

.header-accent {
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, transparent, #000, transparent);
    margin: 0 auto 40px;
    animation: accent-pulse 3s ease-in-out infinite;
}

@keyframes accent-pulse {
    0%, 100% { opacity: 0.3; transform: scaleX(1); }
    50% { opacity: 1; transform: scaleX(1.5); }
}

.section-title {
    margin: 0 0 20px;
}

.title-en {
    display: block;
    font-size: clamp(32px, 5vw, 56px);
    font-weight: 900;
    letter-spacing: -0.02em;
    line-height: 1;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #000 0%, #333 50%, #000 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-size: 200% 200%;
    animation: gradient-shift 5s ease infinite;
}

@keyframes gradient-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.title-ja {
    display: block;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 0.15em;
    color: #666;
    margin-bottom: 20px;
}

.section-description {
    font-size: 18px;
    color: #888;
    letter-spacing: 0.05em;
    margin: 0 auto 40px;
    max-width: 600px;
    line-height: 1.6;
}

/* Yellow Markers */
.yellow-marker {
    width: 60px;
    height: 4px;
    background: #ffeb3b;
    margin: 10px auto 0;
    border-radius: 2px;
    position: relative;
    box-shadow: 0 2px 8px rgba(255, 235, 59, 0.3);
}

.yellow-marker::after {
    content: '';
    position: absolute;
    top: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 8px;
    background: #ffeb3b;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(255, 235, 59, 0.4);
}

/* モノクローム検索バー */
.ai-search-bar {
    position: relative;
    max-width: 800px;
    margin: 0 auto 48px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #fff;
    border: 2px solid #000;
    border-radius: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.search-input-wrapper::before {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 0;
    height: 2px;
    background: #000;
    transition: width 0.3s ease;
}

.search-input-wrapper:focus-within::before {
    width: 100%;
}

.search-input-wrapper:focus-within {
    transform: translateY(-2px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.search-input {
    flex: 1;
    padding: 20px 24px;
    background: none;
    border: none;
    font-size: 16px;
    font-weight: 500;
    outline: none;
    letter-spacing: 0.02em;
}

.search-input::placeholder {
    color: #999;
    font-weight: 400;
}

.search-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-right: 8px;
}

.voice-btn {
    width: 44px;
    height: 44px;
    border: none;
    background: transparent;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    position: relative;
}

.voice-btn::after {
    content: '';
    position: absolute;
    inset: 8px;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.voice-btn:hover {
    color: #000;
}

.voice-btn:hover::after {
    border-color: #000;
}

.search-btn {
    height: 48px;
    padding: 0 32px;
    background: #000;
    color: #fff;
    border: none;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.search-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.search-btn:hover::before {
    transform: translateX(100%);
}

.search-btn:hover {
    transform: scale(1.02);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.search-btn:active {
    transform: scale(0.98);
}

.btn-icon {
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
}

/* Search Suggestions */
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 8px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    display: none;
    z-index: 10;
}

.search-suggestions.active {
    display: block;
}

.suggestion-item {
    padding: 12px 20px;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
}

.suggestion-item:hover {
    background: #f8f8f8;
}

.suggestion-icon {
    width: 32px;
    height: 32px;
    background: #f0f0f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

/* モノクロームフィルター */
.quick-filters {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 60px;
}

.filter-chip {
    padding: 12px 24px;
    background: transparent;
    border: 2px solid #000;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.05em;
    color: #000;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.filter-chip::before {
    content: '';
    position: absolute;
    inset: 0;
    background: #000;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
    z-index: -1;
}

.filter-chip:hover::before {
    transform: scaleX(1);
}

.filter-chip:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.filter-chip.active {
    background: #000;
    color: #fff;
}

.filter-chip.active::before {
    transform: scaleX(1);
}

/* Main Content */
.ai-main-content {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 32px;
    margin-bottom: 48px;
}

/* AI Assistant Panel */
.ai-assistant-panel {
    background: #fafafa;
    border-radius: 20px;
    border: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    height: 600px;
}

.assistant-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.assistant-avatar {
    position: relative;
    width: 48px;
    height: 48px;
}

.avatar-ring {
    position: absolute;
    inset: 0;
    border: 2px solid #000;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.05); }
}

.avatar-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    color: #fff;
    border-radius: 50%;
    font-size: 14px;
    font-weight: 700;
}

.assistant-name {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
}

.assistant-status {
    font-size: 11px;
    color: #10b981;
}

/* AI History Button */
.ai-history-btn {
    margin-left: auto;
    background: #fff;
    border: 2px solid #000;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.875rem;
}

.ai-history-btn:hover {
    background: #000;
    color: #fff;
}

.history-count {
    background: #000;
    color: #fff;
    padding: 0.125rem 0.5rem;
    border-radius: 2px;
    font-size: 0.75rem;
    font-weight: 700;
}

.ai-history-btn:hover .history-count {
    background: #fff;
    color: #000;
}

/* AI History Panel */
.ai-history-panel {
    position: absolute;
    top: 100%;
    right: 0;
    width: 100%;
    max-height: 300px;
    background: #fff;
    border: 2px solid #000;
    border-radius: 0.75rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    overflow: hidden;
    animation: slideDown 0.3s ease;
    margin-top: 0.5rem;
}

@keyframes slideDown {
    from { 
        opacity: 0; 
        transform: translateY(-10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

.ai-history-header {
    padding: 1rem;
    border-bottom: 2px solid #000;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fafafa;
}

.ai-history-header h4 {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ai-history-clear {
    background: #fff;
    border: 2px solid #000;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.ai-history-clear:hover {
    background: #000;
    color: #fff;
}

.ai-history-list {
    padding: 1rem;
    max-height: 220px;
    overflow-y: auto;
}

.ai-history-empty {
    text-align: center;
    color: #999;
    font-size: 0.875rem;
    padding: 2rem 1rem;
    margin: 0;
}

.ai-history-item {
    padding: 0.75rem;
    border: 2px solid #e5e5e5;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.ai-history-item:hover {
    border-color: #000;
    background: #fafafa;
    transform: translateX(4px);
}

.ai-history-item:last-child {
    margin-bottom: 0;
}

.history-date {
    font-size: 0.625rem;
    color: #999;
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.history-question {
    font-size: 0.8125rem;
    color: #333;
    font-weight: 500;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Chat Messages */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.message {
    display: flex;
    gap: 12px;
    animation: messageIn 0.3s ease-out;
}

@keyframes messageIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-user {
    flex-direction: row-reverse;
}

.message-bubble {
    max-width: 100%;
    width: 100%;
    padding: 20px 24px;
    background: #fff;
    border-radius: 8px;
    font-size: 16px;
    line-height: 1.8;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border: 1px solid #e0e0e0;
    min-height: 80px;
}

.message-user .message-bubble {
    background: #000;
    color: #fff;
    border-color: #000;
}

/* Chat Input */
.chat-input-area {
    padding: 16px;
    border-top: 1px solid #e0e0e0;
    position: relative;
}

.typing-indicator {
    position: absolute;
    top: -24px;
    left: 20px;
    display: none;
    gap: 4px;
}

.typing-indicator.active {
    display: flex;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #999;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.chat-input {
    width: calc(100% - 80px);
    padding: 12px 16px;
    background: #fff;
    border: 2px solid #000;
    border-radius: 4px;
    font-size: 16px;
    resize: none;
    outline: none;
    transition: all 0.2s;
    min-height: 44px;
}

.chat-input:focus {
    border-color: #000;
    box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
}

.chat-send-btn {
    position: absolute;
    right: 16px;
    bottom: 16px;
    height: 44px;
    padding: 0 24px;
    background: #000;
    color: #fff;
    border: 2px solid #000;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
    font-size: 16px;
}

.chat-send-btn:hover {
    background: #fff;
    color: #000;
}

.chat-send-btn:active {
    transform: scale(0.98);
}

/* Quick Questions */
.quick-questions {
    padding: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.quick-q {
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    font-size: 11px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}

.quick-q:hover {
    background: #000;
    color: #fff;
    border-color: #000;
}

/* Search Results Panel */
.search-results-panel {
    background: #fafafa;
    border-radius: 20px;
    padding: 24px;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.results-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

#results-count {
    font-size: 24px;
    font-weight: 900;
    color: #000;
}

.view-controls {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: #fff;
    border-radius: 8px;
}

.view-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: none;
    color: #999;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.view-btn:hover {
    background: #f0f0f0;
}

.view-btn.active {
    background: #000;
    color: #fff;
}

.view-btn svg {
    fill: currentColor;
}

/* モノクロームグラントカード */
.featured-grants,
.results-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

.grant-card {
    position: relative;
    background: #fff;
    padding: 30px;
    border: 2px solid #000;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    overflow: hidden;
}

.grant-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: #000;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s ease;
}

.grant-card:hover::before {
    transform: scaleX(1);
}

.grant-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent 0%, rgba(0,0,0,0.02) 100%);
    opacity: 0;
    transition: opacity 0.3s;
}

.grant-card:hover::after {
    opacity: 1;
}

.grant-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.card-badge {
    position: absolute;
    top: 0;
    right: 0;
    padding: 8px 16px;
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.card-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px;
    line-height: 1.4;
}

.card-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 12px;
}

.meta-item {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 10px;
    color: #999;
    margin-bottom: 2px;
}

.meta-value {
    font-size: 14px;
    font-weight: 700;
    color: #000;
}

.card-org {
    font-size: 11px;
    color: #666;
    margin: 0 0 12px;
}

.card-rate {
    margin-bottom: 16px;
}

.rate-bar {
    height: 4px;
    background: #e0e0e0;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 4px;
}

.rate-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    transition: width 1s ease-out;
}

.rate-text {
    font-size: 10px;
    color: #666;
}

/* Enhanced Grant Card Actions - 🔧 修正箇所 */
.card-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 16px;
    pointer-events: auto !important;
    position: relative;
    z-index: 10;
}

.ai-assist-btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid #000;
    color: #000;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    pointer-events: auto !important;
    position: relative;
    z-index: 20;
}

.ai-assist-btn:hover {
    background: #000;
    color: #fff;
    transform: translateY(-1px);
}

.ai-assist-btn svg {
    fill: none;
    stroke: currentColor;
    stroke-width: 1.5;
}

.card-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #000;
    text-decoration: none;
    transition: all 0.2s;
    pointer-events: auto !important;
    position: relative;
    z-index: 20;
}

.card-link:hover {
    gap: 10px;
}

.card-link svg {
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
}

/* 🔧 修正: ボタンクリック問題の解決 */
.grant-card .ai-assist-btn,
.grant-card .card-link {
    pointer-events: auto !important;
    cursor: pointer !important;
    user-select: none;
}

/* ローディング中でもボタンは有効に */
.grant-card.loading .ai-assist-btn,
.grant-card.loading .card-link {
    pointer-events: auto !important;
    opacity: 1 !important;
}

/* Loading State */
.results-loading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px;
    color: #666;
    font-size: 14px;
}

.results-loading.active {
    display: flex;
}

.loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f0f0f0;
    border-top-color: #000;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 12px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Grant Assistant Modal */
.grant-assistant-modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.grant-assistant-modal.active {
    opacity: 1;
    visibility: visible;
}

.modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    position: relative;
    width: 90vw;
    max-width: 500px;
    max-height: 80vh;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.grant-assistant-modal.active .modal-content {
    transform: scale(1);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.assistant-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.assistant-details h3 {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.grant-title {
    font-size: 12px;
    color: #666;
    margin: 0;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: #f0f0f0;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #000;
    color: #fff;
}

.modal-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.assistant-chat {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    min-height: 200px;
    max-height: 300px;
}

.initial-message,
.assistant-message {
    margin-bottom: 16px;
}

.assistant-message.user {
    text-align: right;
}

.message-bubble {
    display: inline-block;
    max-width: 80%;
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 13px;
    line-height: 1.5;
    word-wrap: break-word;
}

.message-bubble:not(.user) {
    background: #f8f9fa;
    color: #333;
}

.message-bubble.user {
    background: #000;
    color: #fff;
}

/* Grant intro actions and message action links */
.grant-intro-actions,
.message-action-links {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.detail-link,
.message-detail-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #000;
    color: #fff;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    transition: all 0.3s ease;
    border: 2px solid #000;
}

.detail-link:hover,
.message-detail-link:hover {
    background: #fff;
    color: #000;
    transform: translateX(4px);
}

.detail-link i,
.message-detail-link i {
    font-size: 11px;
}

.typing-dots {
    display: flex;
    gap: 4px;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    background: #999;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typingBounce {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-8px);
    }
}

.suggestion-buttons {
    padding: 0 20px 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.suggestion-btn {
    padding: 8px 16px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    font-size: 12px;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.suggestion-btn.small {
    padding: 6px 12px;
    font-size: 11px;
}

.suggestion-btn:hover {
    background: #000;
    color: #fff;
    border-color: #000;
}

.chat-input-area {
    padding: 16px 20px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 12px;
    align-items: end;
}

.grant-chat-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    font-size: 13px;
    resize: none;
    outline: none;
    transition: border-color 0.2s;
    max-height: 100px;
}

.grant-chat-input:focus {
    border-color: #000;
}

.send-btn {
    width: 40px;
    height: 40px;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.send-btn svg {
    fill: none;
    stroke: currentColor;
}

/* Notification System */
.ai-notification {
    position: absolute;
    top: -40px;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 16px;
    background: #333;
    color: #fff;
    border-radius: 20px;
    font-size: 12px;
    opacity: 0;
    transition: all 0.3s;
    z-index: 100;
    white-space: nowrap;
}

.ai-notification.visible {
    opacity: 1;
    top: -50px;
}

.ai-notification.error {
    background: #dc3545;
}

.ai-notification.success {
    background: #28a745;
}

.ai-notification.info {
    background: #17a2b8;
}

/* Voice Recording Animation */
.voice-btn.recording {
    background: #dc3545;
    color: #fff;
    animation: recordPulse 1.5s infinite;
}

@keyframes recordPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Loading States */
.search-input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.search-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Enhanced Grant Cards */
.grant-card.loading {
    pointer-events: none;
    opacity: 0.5;
}

.grant-card .loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    display: none;
}

.grant-card.loading .loading-overlay {
    display: flex;
}

/* List View */
.results-list .grant-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
}

.results-list .card-title {
    flex: 1;
}

.results-list .card-meta {
    display: flex;
    gap: 20px;
}

/* No Results - Monochrome Stylish Design */
.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-results h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.no-results p {
    font-size: 14px;
    color: #999;
}

/* Smart No Results - White/Black Stylish */
.smart-no-results {
    max-width: 900px;
    margin: 0 auto;
    padding: 60px 30px;
}

.no-results-header {
    text-align: center;
    margin-bottom: 50px;
}

.no-results-header .icon-circle {
    width: 100px;
    height: 100px;
    margin: 0 auto 30px;
    background: #f5f5f5;
    border: 3px solid #000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.no-results-header .icon-circle::before {
    content: '';
    position: absolute;
    inset: -6px;
    border: 2px solid #e5e5e5;
    border-radius: 50%;
}

.no-results-header .icon-circle i {
    font-size: 40px;
    color: #000;
}

.no-results-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #000;
    margin-bottom: 12px;
    line-height: 1.4;
}

.no-results-header p {
    font-size: 16px;
    color: #666;
    font-weight: 500;
}

.suggestions-section {
    margin-bottom: 50px;
    padding: 30px;
    background: #fafafa;
    border: 2px solid #e5e5e5;
    border-radius: 16px;
}

.suggestions-section h4 {
    font-size: 18px;
    font-weight: 700;
    color: #000;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.suggestions-section h4 i {
    font-size: 20px;
    color: #000;
}

.suggestion-chips,
.category-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.suggestion-chip,
.category-chip {
    padding: 12px 20px;
    background: #fff;
    border: 2px solid #000;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    color: #000;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.suggestion-chip::before,
.category-chip::before {
    content: '';
    position: absolute;
    inset: 0;
    background: #000;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
    z-index: 0;
}

.suggestion-chip:hover::before,
.category-chip:hover::before {
    transform: scaleX(1);
}

.suggestion-chip:hover,
.category-chip:hover {
    color: #fff;
}

.suggestion-chip .chip-text,
.suggestion-chip .chip-icon,
.category-chip .chip-text,
.category-chip .chip-count {
    position: relative;
    z-index: 1;
}

.chip-count {
    padding: 2px 8px;
    background: #000;
    color: #fff;
    border-radius: 10px;
    font-size: 12px;
}

.suggestion-chip:hover .chip-count {
    background: #fff;
    color: #000;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.tip-card {
    padding: 20px;
    background: #fff;
    border: 2px solid #e5e5e5;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.tip-card:hover {
    border-color: #000;
    transform: translateY(-2px);
}

.tip-icon {
    width: 50px;
    height: 50px;
    background: #f5f5f5;
    border: 2px solid #000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    font-size: 24px;
}

.tip-content h5 {
    font-size: 16px;
    font-weight: 700;
    color: #000;
    margin-bottom: 8px;
}

.tip-content p {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 10px;
}

.tip-example {
    display: inline-block;
    padding: 4px 10px;
    background: #f5f5f5;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    font-size: 12px;
    color: #000;
    font-weight: 600;
}

.popular-grants {
    display: grid;
    gap: 16px;
}

.popular-grant-card {
    padding: 20px;
    background: #fff;
    border: 2px solid #e5e5e5;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: block;
}

.popular-grant-card:hover {
    border-color: #000;
    transform: translateX(4px);
}

.popular-grant-card h5 {
    font-size: 16px;
    font-weight: 700;
    color: #000;
    margin-bottom: 8px;
    line-height: 1.4;
}

.popular-grant-card p {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 10px;
}

.view-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #999;
    font-weight: 600;
}

/* Error Message */
.error-message {
    padding: 20px;
    background: #fee;
    color: #c33;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
    .ai-main-content {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .ai-assistant-panel {
        height: 450px;
    }
    
    .section-container {
        padding: 0 30px;
    }
}

@media (max-width: 640px) {
    .monochrome-ai-search {
        padding: 60px 0;
    }
    
    .section-header {
        margin-bottom: 40px;
    }
    
    .title-en {
        font-size: 24px;
    }
    
    .title-ja {
        font-size: 18px;
    }
    
    .quick-filters {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 30px;
    }
    
    .filter-chip {
        padding: 8px 12px;
        font-size: 11px;
        border-width: 1.5px;
    }
    
    .ai-main-content {
        gap: 20px;
    }
    
    .ai-assistant-panel {
        height: 350px;
        min-height: 350px;
    }
    
    .assistant-chat {
        min-height: 200px;
    }
    
    /* Smart No Results - Mobile */
    .smart-no-results {
        padding: 40px 20px;
    }
    
    .no-results-header .icon-circle {
        width: 80px;
        height: 80px;
    }
    
    .no-results-header .icon-circle i {
        font-size: 32px;
    }
    
    .no-results-header h3 {
        font-size: 20px;
    }
    
    .suggestions-section {
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .monochrome-ai-search {
        padding: 80px 0;
    }
    
    .section-header {
        margin-bottom: 50px;
    }
    
    .title-en {
        font-size: 28px;
    }
    
    .quick-filters {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 40px;
        padding: 0;
    }
    
    .filter-chip {
        padding: 10px 16px;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .featured-grants,
    .results-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .section-container {
        padding: 0 20px;
    }
    
    /* Search Bar - Mobile */
    .ai-search-bar {
        margin-bottom: 30px;
    }
    
    .search-input-wrapper {
        padding: 12px 16px;
    }
    
    .search-input {
        font-size: 14px;
    }
    
    .search-btn {
        padding: 10px 20px;
    }
    
    .btn-text {
        display: none;
    }
    
    .btn-icon {
        margin: 0;
    }

    /* Grant Assistant Modal - Mobile */
    .modal-content {
        width: 95vw;
        max-height: 90vh;
        border-radius: 16px;
    }

    .modal-header {
        padding: 16px;
    }

    .assistant-details h3 {
        font-size: 13px;
    }

    .grant-title {
        font-size: 11px;
        max-width: 200px;
    }

    .assistant-chat {
        padding: 16px;
        min-height: 150px;
        max-height: 250px;
    }

    .suggestion-buttons {
        padding: 0 16px 12px;
    }

    .suggestion-btn {
        font-size: 11px;
        padding: 6px 12px;
    }

    .chat-input-area {
        padding: 12px 16px;
    }

    .grant-chat-input {
        font-size: 12px;
        padding: 10px 14px;
    }

    .send-btn {
        width: 36px;
        height: 36px;
    }

    /* Card Actions - Mobile */
    .card-actions {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
    }

    .ai-assist-btn {
        justify-content: center;
        padding: 10px 16px;
    }

    .card-link {
        text-align: center;
        justify-content: center;
    }
}

/* Ultra-small mobile phones (375px and below) */
@media (max-width: 375px) {
    .monochrome-ai-search {
        padding: 40px 0;
    }
    
    .section-header {
        margin-bottom: 30px;
    }
    
    .title-en {
        font-size: 20px;
    }
    
    .title-ja {
        font-size: 16px;
    }
    
    .section-description {
        font-size: 13px;
    }
    
    .quick-filters {
        gap: 6px;
        margin-bottom: 20px;
    }
    
    .filter-chip {
        padding: 6px 10px;
        font-size: 10px;
    }
    
    .search-input-wrapper {
        padding: 10px 12px;
    }
    
    .search-input {
        font-size: 13px;
    }
    
    .search-btn {
        padding: 8px 16px;
    }
    
    .ai-main-content {
        gap: 15px;
    }
    
    .ai-assistant-panel {
        height: 300px;
        min-height: 300px;
    }
    
    .section-container {
        padding: 0 16px;
    }
    
    /* Smart No Results - Ultra Small */
    .smart-no-results {
        padding: 30px 16px;
    }
    
    .no-results-header .icon-circle {
        width: 70px;
        height: 70px;
    }
    
    .no-results-header .icon-circle i {
        font-size: 28px;
    }
    
    .no-results-header h3 {
        font-size: 18px;
    }
    
    .no-results-header p {
        font-size: 14px;
    }
    
    .suggestions-section {
        padding: 16px;
    }
    
    .suggestions-section h4 {
        font-size: 16px;
    }
    
    .suggestion-chip,
    .category-chip {
        padding: 10px 16px;
        font-size: 13px;
    }
}
</style>

<script>
(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        API_URL: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
        NONCE: '<?php echo esc_js($nonce); ?>',
        SESSION_ID: '<?php echo esc_js($session_id); ?>',
        TYPING_DELAY: 30,
        DEBOUNCE_DELAY: 300,
    };
    
    // Debug: AJAXのURLとnonceを確認
    console.log('AJAX Configuration:', {
        url: CONFIG.API_URL,
        nonce: CONFIG.NONCE,
        session: CONFIG.SESSION_ID
    });

    // AI Search Controller
    class AISearchController {
        constructor() {
            this.state = {
                isSearching: false,
                isTyping: false,
                currentFilter: 'all',
                currentView: 'grid',
                results: [],
                chatHistory: [],
            };
            
            this.elements = {};
            this.init();
        }

        init() {
            this.cacheElements();
            this.bindEvents();
            this.initAnimations();
            this.testConnection();
            this.debugButtonStates(); // 🆕 デバッグ関数を追加
        }

        // 🆕 ボタンの状態をデバッグする関数
        debugButtonStates() {
            console.log('=== Button Debug Information ===');
            
            // AI相談ボタンの確認
            const aiButtons = document.querySelectorAll('.ai-assist-btn');
            console.log(`AI Assistant buttons found: ${aiButtons.length}`);
            aiButtons.forEach((btn, index) => {
                console.log(`AI Button ${index}:`, {
                    grantId: btn.dataset.grantId,
                    postId: btn.dataset.postId, 
                    grantTitle: btn.dataset.grantTitle,
                    clickable: window.getComputedStyle(btn).pointerEvents !== 'none'
                });
            });
            
            // 詳細リンクの確認
            const detailLinks = document.querySelectorAll('.card-link');
            console.log(`Detail links found: ${detailLinks.length}`);
            detailLinks.forEach((link, index) => {
                console.log(`Detail Link ${index}:`, {
                    href: link.href,
                    clickable: window.getComputedStyle(link).pointerEvents !== 'none'
                });
            });
        }

        // AJAXテスト接続
        async testConnection() {
            try {
                const formData = new FormData();
                formData.append('action', 'gi_test_connection');
                
                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                console.log('Test connection result:', data);
            } catch (error) {
                console.error('Test connection failed:', error);
            }
        }

        cacheElements() {
            this.elements = {
                searchInput: document.getElementById('ai-search-input'),
                searchBtn: document.getElementById('ai-search-btn'),
                suggestions: document.getElementById('search-suggestions'),
                filterChips: document.querySelectorAll('.filter-chip'),
                chatMessages: document.getElementById('chat-messages'),
                chatInput: document.getElementById('chat-input'),
                chatSend: document.getElementById('chat-send'),
                typingIndicator: document.getElementById('typing-indicator'),
                resultsContainer: document.getElementById('results-container'),
                resultsLoading: document.getElementById('results-loading'),
                resultsCount: document.getElementById('results-count'),
                viewBtns: document.querySelectorAll('.view-btn'),
                quickQuestions: document.querySelectorAll('.quick-q'),
                voiceBtn: document.querySelector('.voice-btn'),
            };
        }

        bindEvents() {
            // Search events
            this.elements.searchInput?.addEventListener('input', this.debounce(this.handleSearchInput.bind(this), CONFIG.DEBOUNCE_DELAY));
            this.elements.searchInput?.addEventListener('focus', this.showSuggestions.bind(this));
            this.elements.searchBtn?.addEventListener('click', this.performSearch.bind(this));
            
            // Enter key for search
            this.elements.searchInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch();
                }
            });

            // Filter chips
            this.elements.filterChips.forEach(chip => {
                chip.addEventListener('click', this.handleFilterClick.bind(this));
            });

            // Chat events
            this.elements.chatInput?.addEventListener('input', this.autoResizeTextarea.bind(this));
            this.elements.chatSend?.addEventListener('click', this.sendChatMessage.bind(this));
            
            // Enter key for chat (Shift+Enter for new line)
            this.elements.chatInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendChatMessage();
                }
            });

            // Quick questions
            this.elements.quickQuestions.forEach(btn => {
                btn.addEventListener('click', this.handleQuickQuestion.bind(this));
            });

            // View controls
            this.elements.viewBtns.forEach(btn => {
                btn.addEventListener('click', this.handleViewChange.bind(this));
            });

            // Voice input
            this.elements.voiceBtn?.addEventListener('click', this.startVoiceInput.bind(this));

            // Click outside to close suggestions
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.ai-search-bar')) {
                    this.hideSuggestions();
                }
            });
        }

        // Search Methods
        async handleSearchInput(e) {
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.hideSuggestions();
                return;
            }

            // Get suggestions from server
            const suggestions = await this.fetchSuggestions(query);
            this.displaySuggestions(suggestions);
        }

        async fetchSuggestions(query) {
            try {
                const formData = new FormData();
                formData.append('action', 'gi_search_suggestions');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('query', query);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                if (data.success && data.data && data.data.suggestions && Array.isArray(data.data.suggestions)) {
                    // Validate each suggestion
                    return data.data.suggestions.filter(s => s && typeof s.text === 'string' && s.text.trim() !== '');
                }
            } catch (error) {
                console.error('Suggestions error:', error);
            }

            // Fallback suggestions - ensure all properties are defined
            const fallbackSuggestions = [
                { icon: '🏭', text: 'ものづくり補助金', type: 'grant' },
                { icon: '', text: 'IT導入補助金', type: 'grant' },
                { icon: '🏪', text: '小規模事業者持続化補助金', type: 'grant' },
                { icon: '🔄', text: '事業再構築補助金', type: 'grant' }
            ];

            return fallbackSuggestions.filter(s => s.text && s.text.toLowerCase().includes(query.toLowerCase()));
        }

        displaySuggestions(suggestions) {
            const container = this.elements.suggestions;
            if (!container) return;

            if (!suggestions || suggestions.length === 0) {
                this.hideSuggestions();
                return;
            }

            container.innerHTML = suggestions.map(s => {
                // Handle undefined values
                const text = s?.text || '';
                const icon = s?.icon || '🔍';
                
                if (!text) return ''; // Skip empty suggestions
                
                return `
                    <div class="suggestion-item" data-text="${text}">
                        <span class="suggestion-icon">${icon}</span>
                        <span>${text}</span>
                    </div>
                `;
            }).filter(html => html !== '').join('');

            container.classList.add('active');

            // Bind click events
            container.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.elements.searchInput.value = item.dataset.text;
                    this.hideSuggestions();
                    this.performSearch();
                });
            });
        }

        showSuggestions() {
            if (this.elements.searchInput.value.length >= 2) {
                this.elements.suggestions?.classList.add('active');
            }
        }

        hideSuggestions() {
            this.elements.suggestions?.classList.remove('active');
        }

        async performSearch() {
            const query = this.elements.searchInput.value.trim();
            if (!query || this.state.isSearching) return;

            this.state.isSearching = true;
            this.showLoading();

            try {
                const formData = new FormData();
                formData.append('action', 'gi_ai_search');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('query', query);
                formData.append('filter', this.state.currentFilter);
                formData.append('session_id', CONFIG.SESSION_ID);

                // Debug: リクエストの詳細を表示
                console.log('Sending search request:', {
                    url: CONFIG.API_URL,
                    action: 'gi_ai_search',
                    nonce: CONFIG.NONCE,
                    query: query,
                    filter: this.state.currentFilter
                });

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const text = await response.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    this.showError('サーバーからの応答が不正です: ' + text.substring(0, 100));
                    return;
                }

                if (data.success) {
                    this.displayResults(data.data.grants);
                    this.updateResultsCount(data.data.count);
                    
                    // Add AI response to chat
                    if (data.data.ai_response) {
                        this.addChatMessage(data.data.ai_response, 'ai');
                    }
                } else {
                    const errorMsg = data.data?.message || data.data || '検索エラーが発生しました';
                    console.error('Search failed:', errorMsg);
                    this.showError(errorMsg);
                }
            } catch (error) {
                console.error('Search error:', error);
                this.showError('通信エラーが発生しました: ' + error.message);
            } finally {
                this.state.isSearching = false;
                this.hideLoading();
            }
        }

        displayResults(grants) {
            const container = this.elements.resultsContainer;
            if (!container || !grants) return;

            if (grants.length === 0) {
                this.showSmartNoResultsSuggestions(this.state.currentQuery);
                return;
            }

            container.innerHTML = grants.map(grant => this.createGrantCard(grant)).join('');
            this.animateCards();
            this.bindGrantCardEvents();
        }

        createGrantCard(grant) {
            return `
                <div class="grant-card" data-id="${grant.id}" style="animation-delay: ${Math.random() * 0.2}s">
                    ${grant.featured ? '<div class="card-badge">注目</div>' : ''}
                    <h4 class="card-title">${grant.title}</h4>
                    <div class="card-meta">
                        <span class="meta-item">
                            <span class="meta-label">最大</span>
                            <span class="meta-value">${grant.amount || '未定'}</span>
                        </span>
                        <span class="meta-item">
                            <span class="meta-label">締切</span>
                            <span class="meta-value">${grant.deadline || '随時'}</span>
                        </span>
                    </div>
                    <p class="card-org">${grant.organization || ''}</p>
                    ${grant.success_rate ? `
                        <div class="card-rate">
                            <div class="rate-bar">
                                <div class="rate-fill" style="width: ${grant.success_rate}%"></div>
                            </div>
                            <span class="rate-text">採択率 ${grant.success_rate}%</span>
                        </div>
                    ` : ''}
                    
                    <!-- Enhanced AI Assistant Integration -->
                    <div class="card-actions">
                        <button class="ai-assist-btn" 
                                data-grant-id="${grant.id}" 
                                data-post-id="${grant.id}"
                                data-grant-title="${grant.title}">
                            AI質問
                        </button>
                        <a href="${grant.permalink}" class="card-link">
                            詳細を見る
                        </a>
                    </div>
                </div>
            `;
        }

        updateResultsCount(count) {
            if (this.elements.resultsCount) {
                this.animateNumber(this.elements.resultsCount, count);
            }
        }

        // Filter Methods
        handleFilterClick(e) {
            const filter = e.currentTarget.dataset.filter;
            
            // Update active state
            this.elements.filterChips.forEach(chip => {
                chip.classList.toggle('active', chip.dataset.filter === filter);
            });

            this.state.currentFilter = filter;
            
            // Perform search with new filter
            if (this.elements.searchInput.value) {
                this.performSearch();
            }
        }

        // Chat Methods
        async sendChatMessage() {
            const message = this.elements.chatInput.value.trim();
            if (!message || this.state.isTyping) return;

            // Clear input
            this.elements.chatInput.value = '';
            this.autoResizeTextarea();

            // Add user message
            this.addChatMessage(message, 'user');

            // Show typing indicator
            this.showTyping();

            try {
                const formData = new FormData();
                formData.append('action', 'gi_ai_chat');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('message', message);
                formData.append('session_id', CONFIG.SESSION_ID);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const text = await response.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    this.addChatMessage('サーバーエラー: 不正な応答形式です。', 'ai');
                    return;
                }

                if (data.success) {
                    // Type AI response
                    this.typeMessage(data.data.response);
                    
                    // 💾 Save chat history (提案4)
                    if (typeof window.saveChatHistory === 'function') {
                        window.saveChatHistory(message, data.data.response);
                    }
                    
                    // Update search results if needed
                    if (data.data.related_grants) {
                        this.displayResults(data.data.related_grants);
                    }
                } else {
                    const errorMsg = data.data?.message || data.data || '申し訳ございません。エラーが発生しました。';
                    console.error('Chat failed:', errorMsg);
                    this.addChatMessage(errorMsg, 'ai');
                }
            } catch (error) {
                console.error('Chat error:', error);
                this.addChatMessage('通信エラーが発生しました: ' + error.message, 'ai');
            } finally {
                this.hideTyping();
            }
        }

        addChatMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${type}`;
            messageDiv.innerHTML = `<div class="message-bubble">${text}</div>`;
            
            this.elements.chatMessages.appendChild(messageDiv);
            this.scrollChatToBottom();
        }

        typeMessage(text) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message message-ai';
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            messageDiv.appendChild(bubble);
            
            this.elements.chatMessages.appendChild(messageDiv);
            
            let index = 0;
            const typeChar = () => {
                if (index < text.length) {
                    bubble.textContent += text[index];
                    index++;
                    this.scrollChatToBottom();
                    setTimeout(typeChar, CONFIG.TYPING_DELAY);
                }
            };
            
            typeChar();
        }

        handleQuickQuestion(e) {
            const question = e.currentTarget.dataset.q;
            this.elements.chatInput.value = question;
            this.autoResizeTextarea();
            this.sendChatMessage();
        }

        autoResizeTextarea() {
            const textarea = this.elements.chatInput;
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        scrollChatToBottom() {
            this.elements.chatMessages.scrollTop = this.elements.chatMessages.scrollHeight;
        }

        showTyping() {
            this.state.isTyping = true;
            this.elements.typingIndicator?.classList.add('active');
        }

        hideTyping() {
            this.state.isTyping = false;
            this.elements.typingIndicator?.classList.remove('active');
        }

        // View Methods
        handleViewChange(e) {
            const view = e.currentTarget.dataset.view;
            
            this.elements.viewBtns.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.view === view);
            });

            this.state.currentView = view;
            
            // Update results display
            const container = this.elements.resultsContainer;
            if (container) {
                container.className = view === 'list' ? 'results-list' : 'featured-grants';
            }
        }

        // Voice Input
        startVoiceInput() {
            // Check for speech recognition support
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                this.showNotification('音声入力はこのブラウザではサポートされていません', 'error');
                return;
            }

            const recognition = new SpeechRecognition();
            recognition.lang = 'ja-JP';
            recognition.interimResults = true;
            recognition.maxAlternatives = 1;
            recognition.continuous = false;

            // Visual feedback
            this.elements.voiceBtn?.classList.add('recording');
            this.showNotification('音声入力中...話してください', 'info');

            recognition.onstart = () => {
                console.log('Voice recognition started');
            };

            recognition.onresult = async (event) => {
                const transcript = Array.from(event.results)
                    .map(result => result[0])
                    .map(result => result.transcript)
                    .join('');
                
                this.elements.searchInput.value = transcript;
                
                // If final result, perform search
                if (event.results[event.results.length - 1].isFinal) {
                    this.hideNotification();
                    this.performSearch();
                    
                    // Save voice input history
                    if (transcript) {
                        this.saveVoiceHistory(transcript, event.results[0][0].confidence);
                    }
                }
            };

            recognition.onerror = (event) => {
                console.error('Voice recognition error:', event.error);
                let errorMessage = '音声認識エラーが発生しました';
                
                switch(event.error) {
                    case 'no-speech':
                        errorMessage = '音声が検出されませんでした';
                        break;
                    case 'audio-capture':
                        errorMessage = 'マイクが使用できません';
                        break;
                    case 'not-allowed':
                        errorMessage = 'マイクのアクセスが拒否されました';
                        break;
                }
                
                this.showNotification(errorMessage, 'error');
            };

            recognition.onend = () => {
                this.elements.voiceBtn?.classList.remove('recording');
                this.hideNotification();
            };

            recognition.start();
        }

        // Save voice input history
        async saveVoiceHistory(text, confidence) {
            try {
                const formData = new FormData();
                formData.append('action', 'gi_voice_history');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('session_id', CONFIG.SESSION_ID);
                formData.append('text', text);
                formData.append('confidence', confidence);

                await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            } catch (error) {
                console.error('Voice history save error:', error);
            }
        }

        // Notification system
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `ai-notification ${type}`;
            notification.textContent = message;
            
            const container = document.querySelector('.ai-search-bar');
            container?.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('visible');
            }, 10);
        }

        hideNotification() {
            const notification = document.querySelector('.ai-notification');
            if (notification) {
                notification.classList.remove('visible');
                setTimeout(() => notification.remove(), 300);
            }
        }

        // Loading States
        showLoading() {
            this.elements.resultsLoading?.classList.add('active');
            this.elements.resultsContainer?.classList.add('loading');
        }

        hideLoading() {
            this.elements.resultsLoading?.classList.remove('active');
            this.elements.resultsContainer?.classList.remove('loading');
        }

        showError(message) {
            const container = this.elements.resultsContainer;
            if (container) {
                container.innerHTML = `<div class="error-message">${message}</div>`;
            }
        }

        // Smart No Results Suggestions
        async showSmartNoResultsSuggestions(query) {
            const container = this.elements.resultsContainer;
            if (!container) return;

            // Show loading state
            container.innerHTML = '<div class="no-results-loading"> より良い結果を探しています...</div>';

            try {
                const formData = new FormData();
                formData.append('action', 'gi_no_results_suggestions');
                formData.append('query', query);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    this.renderNoResultsSuggestions(query, data.data);
                } else {
                    // Fallback to basic no results message
                    container.innerHTML = this.getBasicNoResults(query);
                }
            } catch (error) {
                console.error('Suggestions error:', error);
                container.innerHTML = this.getBasicNoResults(query);
            }
        }

        renderNoResultsSuggestions(query, suggestions) {
            const container = this.elements.resultsContainer;
            
            let html = `
                <div class="smart-no-results">
                    <div class="no-results-header">
                        <div class="icon-circle">
                            <span style="font-size: 1.5rem; font-weight: 600;">×</span>
                        </div>
                        <h3>「${this.escapeHtml(query)}」の検索結果が見つかりませんでした</h3>
                        <p>以下の方法をお試しください</p>
                    </div>
            `;

            // Alternative queries
            if (suggestions.alternative_queries && suggestions.alternative_queries.length > 0) {
                html += `
                    <div class="suggestions-section">
                        <h4>こちらのキーワードで検索してみませんか？</h4>
                        <div class="suggestion-chips">
                `;
                
                suggestions.alternative_queries.forEach(alt => {
                    html += `
                        <button class="suggestion-chip" data-query="${this.escapeHtml(alt.query)}" title="${this.escapeHtml(alt.reason)}">
                            <span class="chip-text">${this.escapeHtml(alt.query)}</span>
                            <span class="chip-icon">→</span>
                        </button>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }

            // Search tips
            if (suggestions.search_tips && suggestions.search_tips.length > 0) {
                html += `
                    <div class="suggestions-section">
                        <h4>検索のヒント</h4>
                        <div class="tips-grid">
                `;
                
                suggestions.search_tips.forEach(tip => {
                    html += `
                        <div class="tip-card">
                            <div class="tip-icon">${tip.icon}</div>
                            <div class="tip-content">
                                <h5>${this.escapeHtml(tip.title)}</h5>
                                <p>${this.escapeHtml(tip.description)}</p>
                                ${tip.example ? `<span class="tip-example">${this.escapeHtml(tip.example)}</span>` : ''}
                            </div>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }

            // Related categories
            if (suggestions.related_categories && suggestions.related_categories.length > 0) {
                html += `
                    <div class="suggestions-section">
                        <h4>関連するカテゴリから探す</h4>
                        <div class="category-chips">
                `;
                
                suggestions.related_categories.forEach(cat => {
                    html += `
                        <a href="${cat.link}" class="category-chip">
                            <span class="chip-text">${this.escapeHtml(cat.category)}</span>
                            <span class="chip-count">${cat.count}件</span>
                        </a>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }

            // Popular grants
            if (suggestions.popular_grants && suggestions.popular_grants.length > 0) {
                html += `
                    <div class="suggestions-section">
                        <h4>人気の助成金</h4>
                        <div class="popular-grants">
                `;
                
                suggestions.popular_grants.forEach(grant => {
                    html += `
                        <a href="${grant.url}" class="popular-grant-card">
                            <h5>${this.escapeHtml(grant.title)}</h5>
                            <p>${this.escapeHtml(grant.excerpt)}</p>
                            ${grant.view_count ? `<span class="view-count">${grant.view_count}回閲覧</span>` : ''}
                        </a>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }

            // Example queries
            if (suggestions.example_queries && suggestions.example_queries.length > 0) {
                html += `
                    <div class="suggestions-section">
                        <h4><span class="icon"></span> 検索例</h4>
                        <div class="example-queries">
                `;
                
                suggestions.example_queries.forEach(example => {
                    html += `
                        <button class="example-query" data-query="${this.escapeHtml(example.query)}">
                            <span class="example-text">${this.escapeHtml(example.query)}</span>
                            <span class="example-desc">${this.escapeHtml(example.description)}</span>
                        </button>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }

            // AI Chat suggestion
            html += `
                <div class="suggestions-section ai-chat-cta">
                    <div class="cta-content">
                        <div class="cta-icon">🤖</div>
                        <div class="cta-text">
                            <h4>AIアシスタントに相談する</h4>
                            <p>具体的な状況を教えていただければ、最適な助成金をご提案します</p>
                        </div>
                        <button class="cta-button" onclick="document.querySelector('.ai-chat-toggle')?.click()">
                            チャットを開く
                        </button>
                    </div>
                </div>
            `;

            html += '</div>';
            container.innerHTML = html;

            // Bind click events
            this.bindSuggestionEvents();
        }

        bindSuggestionEvents() {
            // Suggestion chips
            const chips = document.querySelectorAll('.suggestion-chip, .example-query');
            chips.forEach(chip => {
                chip.addEventListener('click', (e) => {
                    const query = e.currentTarget.dataset.query;
                    if (query && this.elements.searchInput) {
                        this.elements.searchInput.value = query;
                        this.performSearch();
                    }
                });
            });
        }

        getBasicNoResults(query) {
            return `
                <div class="basic-no-results">
                    <div class="icon">🔍</div>
                    <h3>該当する補助金が見つかりませんでした</h3>
                    <p>「${this.escapeHtml(query)}」の検索結果が見つかりませんでした</p>
                    <div class="basic-tips">
                        <p>• キーワードを変更してみてください</p>
                        <p>• 業種や地域を追加してみてください</p>
                        <p>• カテゴリから探してみてください</p>
                    </div>
                    <button class="retry-button" onclick="document.querySelector('.ai-search-input')?.focus()">
                        再検索する
                    </button>
                </div>
            `;
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Animation Methods
        initAnimations() {
            // Intersection Observer for scroll animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.grant-card').forEach(card => {
                observer.observe(card);
            });
        }

        animateCards() {
            const cards = document.querySelectorAll('.grant-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }

        animateNumber(element, target) {
            const duration = 1500;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }

        // 🔧 修正: イベントバインディングの改善
        bindGrantCardEvents() {
            // 重複削除を正しく処理
            document.querySelectorAll('.ai-assist-btn').forEach(btn => {
                // 既存のイベントリスナーを削除
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                // 新しいイベントリスナーを追加
                newBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const grantId = newBtn.dataset.postId || newBtn.dataset.grantId;
                    const grantTitle = newBtn.dataset.grantTitle;
                    
                    console.log('AI Assistant clicked:', { grantId, grantTitle }); // デバッグ用
                    
                    if (grantId && grantTitle) {
                        this.showGrantAssistant(grantId, grantTitle);
                    } else {
                        console.error('Missing grant data:', { grantId, grantTitle });
                    }
                });
            });
            
            // 詳細リンクの確認
            document.querySelectorAll('.card-link').forEach(link => {
                // 明示的にポインターイベントを有効化
                link.style.pointerEvents = 'auto';
                link.style.cursor = 'pointer';
                
                // デバッグ用ログ
                console.log('Detail link found:', link.href);
            });
        }

        // Grant-specific AI Assistant Interface
        async showGrantAssistant(grantId, grantTitle) {
            // Create AI Assistant modal
            const modal = this.createAssistantModal(grantId, grantTitle);
            document.body.appendChild(modal);
            
            // Show initial suggestions
            this.showInitialGrantSuggestions(grantId);
            
            // Animate in
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        createAssistantModal(grantId, grantTitle) {
            const modal = document.createElement('div');
            modal.className = 'grant-assistant-modal';
            modal.innerHTML = `
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="assistant-info">
                            <div class="assistant-avatar">
                                <span class="avatar-icon">AI</span>
                            </div>
                            <div class="assistant-details">
                                <h3>補助金AIアシスタント</h3>
                                <p class="grant-title">${grantTitle}</p>
                            </div>
                        </div>
                        <button class="modal-close">×</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="assistant-chat" id="assistant-chat-${grantId}">
                            <div class="initial-message">
                                <div class="message-bubble">
                                    <p>こんにちは！「<strong>${grantTitle}</strong>」について、どのようなことをお聞きしたいですか？</p>
                                    <div class="grant-intro-actions">
                                        <a href="<?php echo home_url('/grant/'); ?>${grantId}/" class="detail-link" target="_blank">
                                            詳細ページはこちら
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="suggestion-buttons" id="suggestions-${grantId}">
                            <!-- Dynamic suggestions will be loaded here -->
                        </div>
                        
                        <div class="chat-input-area">
                            <textarea 
                                id="grant-chat-input-${grantId}" 
                                class="grant-chat-input"
                                placeholder="質問を入力してください..."
                                rows="2"></textarea>
                            <button class="send-btn" data-grant-id="${grantId}">
                                送信
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Bind modal events
            this.bindModalEvents(modal, grantId);
            
            return modal;
        }

        bindModalEvents(modal, grantId) {
            const closeBtn = modal.querySelector('.modal-close');
            const overlay = modal.querySelector('.modal-overlay');
            const sendBtn = modal.querySelector('.send-btn');
            const chatInput = modal.querySelector(`#grant-chat-input-${grantId}`);
            
            // Close modal
            [closeBtn, overlay].forEach(el => {
                el.addEventListener('click', () => {
                    modal.classList.remove('active');
                    setTimeout(() => {
                        modal.remove();
                    }, 300);
                });
            });
            
            // Send message
            sendBtn.addEventListener('click', () => {
                this.sendGrantQuestion(grantId, chatInput.value.trim(), 'custom');
                chatInput.value = '';
            });
            
            // Enter key to send
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendGrantQuestion(grantId, chatInput.value.trim(), 'custom');
                    chatInput.value = '';
                }
            });
        }

        async showInitialGrantSuggestions(grantId) {
            const suggestionsContainer = document.getElementById(`suggestions-${grantId}`);
            if (!suggestionsContainer) return;
            
            // Initial suggestion buttons
            const initialSuggestions = [
                { text: 'この補助金の概要を教えて', type: 'overview' },
                { text: '申請要件について', type: 'requirements' },
                { text: '申請手順を知りたい', type: 'process' },
                { text: '採択のコツは？', type: 'tips' }
            ];
            
            suggestionsContainer.innerHTML = initialSuggestions.map(suggestion => `
                <button class="suggestion-btn" data-grant-id="${grantId}" data-type="${suggestion.type}">
                    ${suggestion.text}
                </button>
            `).join('');
            
            // Bind suggestion button events
            suggestionsContainer.querySelectorAll('.suggestion-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const questionType = btn.dataset.type;
                    const questionText = btn.textContent;
                    this.sendGrantQuestion(grantId, questionText, questionType);
                });
            });
        }

        // 🔧 修正: 正しいアクション名に変更
        async sendGrantQuestion(grantId, question, questionType) {
            if (!question.trim()) return;
            
            const chatContainer = document.getElementById(`assistant-chat-${grantId}`);
            const suggestionsContainer = document.getElementById(`suggestions-${grantId}`);
            
            // Add user message
            this.addAssistantMessage(chatContainer, question, 'user');
            
            // Show typing indicator
            const typingIndicator = this.addTypingIndicator(chatContainer);
            
            try {
                const formData = new FormData();
                formData.append('action', 'handle_grant_ai_question'); // 🔧 修正: 正しいアクション名
                formData.append('nonce', CONFIG.NONCE);
                formData.append('post_id', grantId); // 🔧 修正: post_idパラメータ名に統一
                formData.append('question', question);
                formData.append('session_id', CONFIG.SESSION_ID);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                // Remove typing indicator
                if (typingIndicator) {
                    typingIndicator.remove();
                }

                if (data.success) {
                    // Add AI response with detail link
                    this.addAssistantMessage(chatContainer, data.data.response, 'ai', grantId);
                    
                    // Update suggestions
                    if (data.data.suggestions && suggestionsContainer) {
                        suggestionsContainer.innerHTML = data.data.suggestions.map(suggestion => `
                            <button class="suggestion-btn small" data-grant-id="${grantId}" data-type="custom">
                                ${suggestion}
                            </button>
                        `).join('');
                        
                        // Re-bind suggestion events
                        suggestionsContainer.querySelectorAll('.suggestion-btn').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const questionText = btn.textContent;
                                this.sendGrantQuestion(grantId, questionText, 'custom');
                            });
                        });
                    }
                } else {
                    this.addAssistantMessage(chatContainer, '申し訳ございません。エラーが発生しました。', 'ai');
                }
                
            } catch (error) {
                console.error('Grant assistant error:', error);
                if (typingIndicator) {
                    typingIndicator.remove();
                }
                this.addAssistantMessage(chatContainer, '通信エラーが発生しました。', 'ai');
            }
        }

        addAssistantMessage(container, text, type, grantId = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `assistant-message ${type}`;
            
            // AIメッセージの場合は詳細ページリンクを追加
            let messageContent = text.replace(/\n/g, '<br>');
            if (type === 'ai' && grantId) {
                messageContent += `
                    <div class="message-action-links">
                        <a href="<?php echo home_url('/grant/'); ?>${grantId}/" class="message-detail-link" target="_blank">
                            詳細ページで確認する
                        </a>
                    </div>
                `;
            }
            
            messageDiv.innerHTML = `
                <div class="message-bubble ${type}">
                    ${messageContent}
                </div>
            `;
            
            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
            
            return messageDiv;
        }

        addTypingIndicator(container) {
            const indicator = document.createElement('div');
            indicator.className = 'assistant-message ai typing';
            indicator.innerHTML = `
                <div class="message-bubble ai">
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            `;
            
            container.appendChild(indicator);
            container.scrollTop = container.scrollHeight;
            
            return indicator;
        }

        // Utility Methods
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    }

    // Ensure global AI chat function is available
    if (typeof window.openGrantAIChat === 'undefined') {
        window.openGrantAIChat = function(button) {
            const postId = button.getAttribute('data-post-id') || button.dataset.postId;
            const grantTitle = button.getAttribute('data-grant-title') || button.dataset.grantTitle;
            
            if (!postId) {
                console.error('Post ID not found');
                return;
            }
            
            // Find the controller instance and use its method
            const searchSection = document.getElementById('ai-search-section');
            if (searchSection && searchSection._aiController) {
                searchSection._aiController.showGrantAssistant(postId, grantTitle);
            } else {
                console.error('AI Controller not found');
            }
        };
    }

    // 🚨 緊急修正: 最低限の動作保証
    document.addEventListener('DOMContentLoaded', function() {
        // メインコントローラーの初期化
        const controller = new AISearchController();
        
        // Store controller reference for global access
        const searchSection = document.getElementById('ai-search-section');
        if (searchSection) {
            searchSection._aiController = controller;
        }
        
        // 初期カードのイベントバインド
        controller.bindGrantCardEvents();
        
        // 500ms後に強制的にイベントを再バインド（緊急修正）
        setTimeout(() => {
            // AI相談ボタン
            document.querySelectorAll('.ai-assist-btn').forEach(btn => {
                btn.style.pointerEvents = 'auto';
                btn.style.cursor = 'pointer';
                
                // 既存のイベントを削除して再設定
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const grantId = this.dataset.postId || this.dataset.grantId;
                    const grantTitle = this.dataset.grantTitle;
                    
                    console.log('Emergency AI click handler:', { grantId, grantTitle });
                    
                    if (grantId && grantTitle) {
                        if (controller) {
                            controller.showGrantAssistant(grantId, grantTitle);
                        } else {
                            alert(`AI相談機能：${grantTitle} (ID: ${grantId})`);
                        }
                    } else {
                        alert('AI相談機能 - データが不正です');
                    }
                };
            });
            
            // 詳細リンク
            document.querySelectorAll('.card-link').forEach(link => {
                link.style.pointerEvents = 'auto';
                link.style.cursor = 'pointer';
                
                console.log('Detail link enabled:', link.href);
            });
            
            console.log('Emergency fix applied - buttons should work now');
        }, 500);
    });

    // ============================================
    // AI Chat History Management (提案4)
    // ============================================
    
    /**
     * チャット履歴をトグル表示
     */
    window.toggleChatHistory = function() {
        const panel = document.getElementById('ai-history-panel');
        if (!panel) return;
        
        if (panel.style.display === 'none' || !panel.style.display) {
            loadChatHistory();
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    };
    
    /**
     * チャット履歴を保存
     */
    window.saveChatHistory = function(question, answer) {
        try {
            let history = JSON.parse(localStorage.getItem('gi_chat_history') || '[]');
            
            // 新しい会話を先頭に追加
            history.unshift({
                id: Date.now(),
                question: question,
                answer: answer,
                timestamp: new Date().toISOString(),
                date: new Date().toLocaleDateString('ja-JP', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            });
            
            // 最新20件のみ保持
            history = history.slice(0, 20);
            localStorage.setItem('gi_chat_history', JSON.stringify(history));
            
            // バッジの数を更新
            updateHistoryCount();
            
            console.log('Chat history saved:', history.length);
        } catch (error) {
            console.error('Error saving chat history:', error);
        }
    };
    
    /**
     * チャット履歴を読み込んで表示
     */
    window.loadChatHistory = function() {
        try {
            const history = JSON.parse(localStorage.getItem('gi_chat_history') || '[]');
            const listContainer = document.getElementById('ai-history-list');
            
            if (!listContainer) return;
            
            if (history.length === 0) {
                listContainer.innerHTML = '<p class="ai-history-empty">履歴がありません</p>';
                return;
            }
            
            // 履歴アイテムを生成
            listContainer.innerHTML = history.map((item, index) => `
                <div class="ai-history-item" onclick="restoreConversation(${item.id})" data-index="${index}">
                    <div class="history-date">${item.date}</div>
                    <div class="history-question">${escapeHtml(item.question.substring(0, 80))}${item.question.length > 80 ? '...' : ''}</div>
                </div>
            `).join('');
            
            console.log('Chat history loaded:', history.length);
        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    };
    
    /**
     * チャット履歴をクリア
     */
    window.clearChatHistory = function() {
        if (confirm('会話履歴を削除しますか？この操作は取り消せません。')) {
            try {
                localStorage.removeItem('gi_chat_history');
                updateHistoryCount();
                
                const listContainer = document.getElementById('ai-history-list');
                if (listContainer) {
                    listContainer.innerHTML = '<p class="ai-history-empty">履歴がありません</p>';
                }
                
                console.log('Chat history cleared');
            } catch (error) {
                console.error('Error clearing chat history:', error);
            }
        }
    };
    
    /**
     * 過去の会話を復元
     */
    window.restoreConversation = function(id) {
        try {
            const history = JSON.parse(localStorage.getItem('gi_chat_history') || '[]');
            const conversation = history.find(item => item.id == id);
            
            if (!conversation) {
                console.error('Conversation not found:', id);
                return;
            }
            
            const chatMessages = document.getElementById('chat-messages');
            if (!chatMessages) return;
            
            // チャットメッセージをクリアして復元
            chatMessages.innerHTML = `
                <div class="message message-user" style="animation: messageIn 0.3s ease-out;">
                    <div class="message-bubble">${escapeHtml(conversation.question)}</div>
                </div>
                <div class="message message-ai" style="animation: messageIn 0.3s ease-out;">
                    <div class="message-bubble">${escapeHtml(conversation.answer)}</div>
                </div>
            `;
            
            // 最新のメッセージにスクロール
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // モーダルを開く（存在する場合）
            const modal = document.getElementById('grant-assistant-modal');
            if (modal) {
                modal.classList.add('active');
            }
            
            // 履歴パネルを閉じる
            const panel = document.getElementById('ai-history-panel');
            if (panel) {
                panel.style.display = 'none';
            }
            
            console.log('Conversation restored:', id);
        } catch (error) {
            console.error('Error restoring conversation:', error);
        }
    };
    
    /**
     * 履歴カウントバッジを更新
     */
    function updateHistoryCount() {
        try {
            const history = JSON.parse(localStorage.getItem('gi_chat_history') || '[]');
            const countBadge = document.querySelector('.history-count');
            
            if (countBadge) {
                countBadge.textContent = history.length;
                countBadge.style.display = history.length > 0 ? 'flex' : 'none';
            }
        } catch (error) {
            console.error('Error updating history count:', error);
        }
    }
    
    /**
     * HTML特殊文字をエスケープ
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ページ読み込み時に履歴カウントを初期化
    document.addEventListener('DOMContentLoaded', function() {
        updateHistoryCount();
        console.log('Chat history initialized');
    });
    
    // チャットメッセージ送信後に履歴を保存（既存のsendChatMessage関数と連携）
    // Note: AISearchController.sendChatMessage内でsaveChatHistory()を呼び出す必要があります

    // ============================================
    // 提案8: AI質問サジェスト機能強化
    // ============================================
    
    /**
     * AI動的質問サジェスト生成
     */
    function generateDynamicQuestions() {
        const container = document.getElementById('quick-questions');
        const statusElement = document.getElementById('ai-suggestion-status');
        
        if (!container) return;
        
        // ユーザーの行動履歴を分析
        const chatHistory = JSON.parse(localStorage.getItem('gi_chat_history') || '[]');
        const viewHistory = JSON.parse(localStorage.getItem('gi_view_history') || '[]');
        const searchHistory = JSON.parse(localStorage.getItem('gi_search_history') || '[]');
        
        // コンテキストに基づいて質問を生成
        const questions = analyzeAndGenerateQuestions(chatHistory, viewHistory, searchHistory);
        
        if (questions.length > 0) {
            // AIが生成した質問で置き換え
            container.innerHTML = questions.map(q => `
                <button class="quick-q ai-generated" data-q="${q.question}" title="${q.reason}">
                    ${q.label}
                </button>
            `).join('');
            
            // イベントリスナーを再設定
            document.querySelectorAll('.quick-q').forEach(btn => {
                btn.addEventListener('click', function() {
                    const question = this.dataset.q;
                    const chatInput = document.getElementById('chat-input');
                    if (chatInput) {
                        chatInput.value = question;
                        const event = new Event('input', { bubbles: true });
                        chatInput.dispatchEvent(event);
                    }
                });
            });
            
            if (statusElement) {
                statusElement.innerHTML = 'AIが最適な質問を生成しました';
            }
        } else {
            if (statusElement) {
                statusElement.textContent = 'AI学習中...';
            }
        }
    }
    
    /**
     * コンテキストに基づいて質問を分析・生成
     */
    function analyzeAndGenerateQuestions(chatHistory, viewHistory, searchHistory) {
        const questions = [];
        
        // デフォルトの基本質問
        const defaultQuestions = [
            { question: '申請の流れを教えて', label: '申請の流れ', icon: 'fa-route', reason: '基本的な申請プロセス' },
            { question: '必要書類は？', label: '必要書類', icon: 'fa-file-alt', reason: '必要な提出書類' },
            { question: '締切はいつ？', label: '締切確認', icon: 'fa-clock', reason: '申請期限の確認' },
            { question: '採択率は？', label: '採択率', icon: 'fa-chart-line', reason: '採択される確率' }
        ];
        
        // 履歴がある場合、コンテキストベースの質問を生成
        if (chatHistory.length > 0 || viewHistory.length > 0 || searchHistory.length > 0) {
            // 最近の会話から関連質問を生成
            const recentChat = chatHistory.slice(0, 3);
            
            // パターン1: 特定カテゴリーに興味がある
            const categories = extractCategories(viewHistory);
            if (categories.length > 0) {
                const topCategory = categories[0];
                questions.push({
                    question: `${topCategory}の補助金で人気なのは？`,
                    label: `${topCategory}の人気`,
                    icon: 'fa-fire',
                    reason: `${topCategory}への関心が高い`
                });
            }
            
            // パターン2: 金額に関心がある
            if (searchHistory.some(s => s.includes('金額') || s.includes('万円') || s.includes('億円'))) {
                questions.push({
                    question: '最大助成額が大きい補助金を教えて',
                    label: '高額助成金',
                    icon: 'fa-coins',
                    reason: '高額助成金への関心'
                });
            }
            
            // パターン3: 締切を気にしている
            if (recentChat.some(c => c.question.includes('締切') || c.question.includes('期限'))) {
                questions.push({
                    question: '今すぐ申請できる補助金は？',
                    label: '今すぐ申請可能',
                    icon: 'fa-bolt',
                    reason: '緊急性が高い'
                });
            }
            
            // パターン4: 地域に興味がある
            const prefectures = extractPrefectures(viewHistory);
            if (prefectures.length > 0) {
                const topPref = prefectures[0];
                questions.push({
                    question: `${topPref}の補助金で申請しやすいのは？`,
                    label: `${topPref}で申請しやすい`,
                    icon: 'fa-map-marker-alt',
                    reason: `${topPref}への地域的関心`
                });
            }
            
            // パターン5: 難易度を気にしている
            if (recentChat.some(c => c.question.includes('難易度') || c.question.includes('簡単'))) {
                questions.push({
                    question: '初心者でも申請しやすい補助金は？',
                    label: '初心者向け',
                    icon: 'fa-graduation-cap',
                    reason: '申請難易度への関心'
                });
            }
            
            // パターン6: 成功率を気にしている
            if (recentChat.some(c => c.question.includes('採択') || c.question.includes('成功'))) {
                questions.push({
                    question: '採択率が高い補助金を教えて',
                    label: '高採択率',
                    icon: 'fa-trophy',
                    reason: '採択率への関心'
                });
            }
            
            // パターン7: 複数回質問している
            if (chatHistory.length >= 5) {
                questions.push({
                    question: '私に最適な補助金をAIで提案して',
                    label: 'AIで最適化',
                    icon: 'fa-brain',
                    reason: 'パーソナライズされた提案'
                });
            }
        }
        
        // 質問が4件未満の場合、デフォルトで補完
        while (questions.length < 4) {
            const remaining = defaultQuestions.filter(dq => 
                !questions.some(q => q.question === dq.question)
            );
            if (remaining.length > 0) {
                questions.push(remaining[0]);
            } else {
                break;
            }
        }
        
        // 最大4件に制限
        return questions.slice(0, 4);
    }
    
    /**
     * 履歴からカテゴリーを抽出
     */
    function extractCategories(history) {
        const freq = {};
        history.forEach(item => {
            if (item.category) {
                freq[item.category] = (freq[item.category] || 0) + 1;
            }
        });
        return Object.keys(freq).sort((a, b) => freq[b] - freq[a]);
    }
    
    /**
     * 履歴から都道府県を抽出
     */
    function extractPrefectures(history) {
        const freq = {};
        history.forEach(item => {
            if (item.prefecture) {
                freq[item.prefecture] = (freq[item.prefecture] || 0) + 1;
            }
        });
        return Object.keys(freq).sort((a, b) => freq[b] - freq[a]);
    }
    
    // ページ読み込み時とユーザー操作後に質問を更新
    let questionUpdateTimeout;
    function scheduleQuestionUpdate() {
        clearTimeout(questionUpdateTimeout);
        questionUpdateTimeout = setTimeout(() => {
            generateDynamicQuestions();
        }, 2000); // 2秒後に更新
    }
    
    // 初回読み込み時
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(generateDynamicQuestions, 1500);
    });
    
    // チャット送信後に質問を更新
    const originalSendChat = window.AISearchController?.prototype?.sendChatMessage;
    if (originalSendChat) {
        // Hook into send chat to update suggestions
        document.addEventListener('chatMessageSent', function() {
            scheduleQuestionUpdate();
        });
    }

    // ============================================
    // 提案10: AI音声入力・読み上げ
    // ============================================
    
    // Web Speech API support check
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const SpeechSynthesis = window.speechSynthesis;
    
    let recognition = null;
    let isRecording = false;
    
    /**
     * 音声入力のトグル
     */
    window.toggleVoiceInput = function() {
        if (!SpeechRecognition) {
            showToast('お使いのブラウザは音声入力に対応していません。Chrome、Edge、Safariをお試しください。', 'error');
            return;
        }
        
        const btn = document.getElementById('voice-input-btn');
        const input = document.getElementById('chat-input');
        
        if (isRecording) {
            // Stop recording
            stopVoiceRecognition();
        } else {
            // Start recording
            startVoiceRecognition(btn, input);
        }
    };
    
    /**
     * 音声認識を開始
     */
    function startVoiceRecognition(btn, input) {
        if (!recognition) {
            recognition = new SpeechRecognition();
            recognition.lang = 'ja-JP';
            recognition.continuous = false;
            recognition.interimResults = true;
            
            recognition.onstart = function() {
                isRecording = true;
                btn.classList.add('recording');
                btn.innerHTML = '停止';
                btn.style.fontSize = '14px';
                btn.style.fontWeight = '600';
                input.placeholder = '音声を認識中...';
                console.log('Voice recognition started');
            };
            
            recognition.onresult = function(event) {
                let transcript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        transcript += event.results[i][0].transcript;
                    } else {
                        // Show interim results
                        input.value = event.results[i][0].transcript;
                    }
                }
                
                if (transcript) {
                    input.value = transcript;
                    console.log('Recognized:', transcript);
                }
            };
            
            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                stopVoiceRecognition();
                
                let errorMsg = '音声認識エラーが発生しました。';
                if (event.error === 'no-speech') {
                    errorMsg = '音声が検出されませんでした。もう一度お試しください。';
                } else if (event.error === 'not-allowed') {
                    errorMsg = 'マイクへのアクセスが拒否されました。ブラウザの設定を確認してください。';
                }
                showToast(errorMsg, 'error');
            };
            
            recognition.onend = function() {
                stopVoiceRecognition();
            };
        }
        
        try {
            recognition.start();
        } catch (error) {
            console.error('Failed to start recognition:', error);
            showToast('音声認識の開始に失敗しました。', 'error');
        }
    }
    
    /**
     * 音声認識を停止
     */
    function stopVoiceRecognition() {
        if (recognition && isRecording) {
            recognition.stop();
        }
        
        isRecording = false;
        const btn = document.getElementById('voice-input-btn');
        const input = document.getElementById('chat-input');
        
        if (btn) {
            btn.classList.remove('recording');
            btn.innerHTML = '音声';
        }
        if (input) {
            input.placeholder = '質問を入力してください';
        }
    }
    
    /**
     * テキスト読み上げ（AI応答用）
     */
    window.speakText = function(text, messageElement) {
        if (!SpeechSynthesis) {
            console.warn('Text-to-speech not supported');
            return;
        }
        
        // Cancel any ongoing speech
        SpeechSynthesis.cancel();
        
        // Create speaker button if not exists
        let speakerBtn = messageElement.querySelector('.tts-speaker-btn');
        if (!speakerBtn) {
            speakerBtn = document.createElement('button');
            speakerBtn.className = 'tts-speaker-btn';
            speakerBtn.innerHTML = '再生';
            speakerBtn.style.cssText = `
                background: #fff;
                border: 2px solid #000;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                margin-top: 0.5rem;
                transition: all 0.3s;
                font-size: 0.75rem;
            `;
            speakerBtn.onclick = function(e) {
                e.stopPropagation();
                if (SpeechSynthesis.speaking) {
                    SpeechSynthesis.cancel();
                    speakerBtn.innerHTML = '再生';
                    speakerBtn.style.background = '#fff';
                } else {
                    speakText(text, messageElement);
                }
            };
            messageElement.querySelector('.message-bubble').appendChild(speakerBtn);
        }
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'ja-JP';
        utterance.rate = 1.0;
        utterance.pitch = 1.0;
        utterance.volume = 1.0;
        
        // Visual feedback
        speakerBtn.innerHTML = '停止';
        speakerBtn.style.background = '#fbbf24';
        speakerBtn.style.borderColor = '#fbbf24';
        
        utterance.onend = function() {
            speakerBtn.innerHTML = '再生';
            speakerBtn.style.background = '#fff';
            speakerBtn.style.borderColor = '#000';
        };
        
        utterance.onerror = function(event) {
            console.error('Speech synthesis error:', event);
            speakerBtn.innerHTML = '再生';
            speakerBtn.style.background = '#fff';
        };
        
        SpeechSynthesis.speak(utterance);
    };
    
    /**
     * AI応答に自動でスピーカーボタンを追加
     */
    function addSpeakerButtonToAIMessages() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('message-ai')) {
                        const bubble = node.querySelector('.message-bubble');
                        if (bubble && !bubble.querySelector('.tts-speaker-btn')) {
                            const text = bubble.textContent;
                            
                            // Add speaker button
                            const speakerBtn = document.createElement('button');
                            speakerBtn.className = 'tts-speaker-btn';
                            speakerBtn.innerHTML = '再生';
                            speakerBtn.title = '読み上げる';
                            speakerBtn.style.cssText = `
                                background: #fff;
                                border: 2px solid #000;
                                border-radius: 50%;
                                width: 28px;
                                height: 28px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                cursor: pointer;
                                margin-left: 0.5rem;
                                transition: all 0.3s;
                                font-size: 0.875rem;
                                vertical-align: middle;
                            `;
                            speakerBtn.onclick = function(e) {
                                e.stopPropagation();
                                speakText(text, node);
                            };
                            
                            bubble.appendChild(speakerBtn);
                        }
                    }
                });
            });
        });
        
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            observer.observe(chatMessages, {
                childList: true,
                subtree: true
            });
        }
    }
    
    /**
     * トースト通知
     */
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'voice-toast';
        
        const bgColors = {
            'info': '#2563eb',
            'success': '#10b981',
            'error': '#dc2626',
            'warning': '#f59e0b'
        };
        
        const icons = {
            'info': 'fa-info-circle',
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle'
        };
        
        toast.innerHTML = `
            <i class="fas ${icons[type]}"></i>
            <span>${message}</span>
        `;
        
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: ${bgColors[type]};
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            z-index: 10001;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
            max-width: 90%;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
    
    // Initialize speaker buttons for AI messages
    document.addEventListener('DOMContentLoaded', function() {
        addSpeakerButtonToAIMessages();
        
        // Check browser support
        const chatInput = document.getElementById('chat-input');
        if (chatInput && !SpeechRecognition) {
            const voiceBtn = document.getElementById('voice-input-btn');
            if (voiceBtn) {
                voiceBtn.style.opacity = '0.5';
                voiceBtn.style.cursor = 'not-allowed';
                voiceBtn.title = 'お使いのブラウザは音声入力に対応していません';
            }
        }
    });

})();
</script>
