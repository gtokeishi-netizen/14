<?php
/**
 * Enhanced AI Content Generator
 * Advanced AI generation with context awareness and SEO optimization
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GI_Enhanced_AI_Generator {
    
    private $api_key;
    private $model = 'gpt-3.5-turbo';
    
    public function __construct() {
        // Get API key from options or constants
        $this->api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : get_option('gi_openai_api_key', '');
        
        add_action('wp_ajax_gi_smart_generate', array($this, 'handle_smart_generation'));
        add_action('wp_ajax_gi_regenerate_content', array($this, 'handle_regeneration'));
        add_action('wp_ajax_gi_contextual_fill', array($this, 'handle_contextual_fill'));
    }
    
    /**
     * Smart content generation based on existing fields
     */
    public function handle_smart_generation() {
        check_ajax_referer('gi_ai_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }
        
        $existing_data = $this->sanitize_input($_POST['existing_data'] ?? []);
        $target_field = sanitize_text_field($_POST['target_field'] ?? '');
        $generation_mode = sanitize_text_field($_POST['mode'] ?? 'smart_fill');
        
        try {
            $generated_content = $this->generate_contextual_content($existing_data, $target_field, $generation_mode);
            
            wp_send_json_success([
                'content' => $generated_content,
                'field' => $target_field,
                'mode' => $generation_mode,
                'context_used' => !empty($existing_data)
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'fallback' => $this->get_fallback_content($target_field, $existing_data)
            ]);
        }
    }
    
    /**
     * Handle content regeneration
     */
    public function handle_regeneration() {
        check_ajax_referer('gi_ai_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }
        
        $existing_data = $this->sanitize_input($_POST['existing_data'] ?? []);
        $target_field = sanitize_text_field($_POST['target_field'] ?? '');
        $current_content = sanitize_textarea_field($_POST['current_content'] ?? '');
        $regeneration_type = sanitize_text_field($_POST['type'] ?? 'improve');
        
        try {
            $regenerated_content = $this->regenerate_content($existing_data, $target_field, $current_content, $regeneration_type);
            
            wp_send_json_success([
                'content' => $regenerated_content,
                'original' => $current_content,
                'type' => $regeneration_type
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'fallback' => $this->improve_content_simple($current_content, $target_field)
            ]);
        }
    }
    
    /**
     * Handle contextual filling of multiple fields
     */
    public function handle_contextual_fill() {
        check_ajax_referer('gi_ai_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Permission denied');
        }
        
        $existing_data = $this->sanitize_input($_POST['existing_data'] ?? []);
        $empty_fields = $_POST['empty_fields'] ?? [];
        
        try {
            $filled_content = $this->fill_empty_fields($existing_data, $empty_fields);
            
            wp_send_json_success([
                'filled_fields' => $filled_content,
                'context_data' => $existing_data
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'partial_fill' => $this->get_fallback_fills($empty_fields)
            ]);
        }
    }
    
    /**
     * Generate contextual content based on existing data
     */
    private function generate_contextual_content($existing_data, $target_field, $mode) {
        // Build context from existing data
        $context = $this->build_context_prompt($existing_data);
        
        // Field-specific generation prompts
        $field_prompts = $this->get_field_specific_prompts();
        $field_prompt = $field_prompts[$target_field] ?? $field_prompts['default'];
        
        // SEO optimization instructions
        $seo_instructions = $this->get_seo_instructions($target_field);
        
        // Build the complete prompt
        $prompt = $this->build_generation_prompt($context, $field_prompt, $seo_instructions, $mode);
        
        // Call AI API
        return $this->call_openai_api($prompt);
    }
    
    /**
     * Build comprehensive context prompt from all available data
     */
    private function build_context_prompt($data) {
        $context_parts = [];
        
        // 基本情報
        if (!empty($data['title'])) {
            $context_parts[] = "助成金名: {$data['title']}";
        }
        
        if (!empty($data['organization'])) {
            $context_parts[] = "実施機関: {$data['organization']}";
        }
        
        if (!empty($data['organization_type'])) {
            $context_parts[] = "組織タイプ: {$data['organization_type']}";
        }
        
        // 金額情報
        if (!empty($data['max_amount'])) {
            $context_parts[] = "最大金額: {$data['max_amount']}万円";
        }
        
        if (!empty($data['min_amount'])) {
            $context_parts[] = "最小金額: {$data['min_amount']}万円";
        }
        
        if (!empty($data['max_amount_yen'])) {
            $context_parts[] = "最大助成額: " . number_format($data['max_amount_yen']) . "円";
        }
        
        if (!empty($data['subsidy_rate'])) {
            $context_parts[] = "補助率: {$data['subsidy_rate']}%";
        }
        
        if (!empty($data['amount_note'])) {
            $context_parts[] = "金額備考: {$data['amount_note']}";
        }
        
        // 期間情報
        if (!empty($data['application_deadline'])) {
            $context_parts[] = "申請期限: {$data['application_deadline']}";
        }
        
        if (!empty($data['recruitment_start'])) {
            $context_parts[] = "募集開始日: {$data['recruitment_start']}";
        }
        
        if (!empty($data['deadline'])) {
            $context_parts[] = "締切日: {$data['deadline']}";
        }
        
        if (!empty($data['deadline_note'])) {
            $context_parts[] = "締切備考: {$data['deadline_note']}";
        }
        
        if (!empty($data['application_status'])) {
            $context_parts[] = "申請ステータス: {$data['application_status']}";
        }
        
        // 対象・カテゴリー情報
        if (!empty($data['prefectures'])) {
            $prefectures = is_array($data['prefectures']) ? implode('、', $data['prefectures']) : $data['prefectures'];
            $context_parts[] = "対象都道府県: {$prefectures}";
        }
        
        if (!empty($data['categories'])) {
            $categories = is_array($data['categories']) ? implode('、', $data['categories']) : $data['categories'];
            $context_parts[] = "カテゴリー: {$categories}";
        }
        
        if (!empty($data['tags'])) {
            $tags = is_array($data['tags']) ? implode('、', $data['tags']) : $data['tags'];
            $context_parts[] = "タグ: {$tags}";
        }
        
        if (!empty($data['grant_target'])) {
            $context_parts[] = "助成金対象: {$data['grant_target']}";
        }
        
        if (!empty($data['target_expenses'])) {
            $context_parts[] = "対象経費: {$data['target_expenses']}";
        }
        
        // 難易度・成功率
        if (!empty($data['difficulty'])) {
            $context_parts[] = "難易度: {$data['difficulty']}";
        }
        
        if (!empty($data['success_rate'])) {
            $context_parts[] = "成功率: {$data['success_rate']}%";
        }
        
        // 詳細情報
        if (!empty($data['eligibility_criteria'])) {
            $criteria_excerpt = mb_substr(strip_tags($data['eligibility_criteria']), 0, 150);
            $context_parts[] = "対象者・応募要件: {$criteria_excerpt}...";
        }
        
        if (!empty($data['application_process'])) {
            $process_excerpt = mb_substr(strip_tags($data['application_process']), 0, 150);
            $context_parts[] = "申請手順: {$process_excerpt}...";
        }
        
        if (!empty($data['application_method'])) {
            $context_parts[] = "申請方法: {$data['application_method']}";
        }
        
        if (!empty($data['required_documents'])) {
            $documents_excerpt = mb_substr(strip_tags($data['required_documents']), 0, 100);
            $context_parts[] = "必要書類: {$documents_excerpt}...";
        }
        
        if (!empty($data['contact_info'])) {
            $context_parts[] = "連絡先: {$data['contact_info']}";
        }
        
        if (!empty($data['official_url'])) {
            $context_parts[] = "公式URL: {$data['official_url']}";
        }
        
        if (!empty($data['summary'])) {
            $summary_excerpt = mb_substr(strip_tags($data['summary']), 0, 200);
            $context_parts[] = "概要: {$summary_excerpt}...";
        }
        
        if (!empty($data['content'])) {
            $content_excerpt = mb_substr(strip_tags($data['content']), 0, 200);
            $context_parts[] = "既存本文: {$content_excerpt}...";
        }
        
        return implode("\n", $context_parts);
    }
    
    /**
     * Get field-specific generation prompts with enhanced HTML/CSS support
     */
    private function get_field_specific_prompts() {
        return [
            'post_title' => [
                'instruction' => '魅力的で検索されやすい助成金タイトルを生成してください',
                'requirements' => '30-60文字、キーワードを含む、具体的で分かりやすい、緊急性や魅力を表現',
                'examples' => '「【令和6年度】IT導入支援事業補助金（最大1000万円）」「中小企業デジタル化促進助成金【申請期限間近】」'
            ],
            'post_content' => [
                'instruction' => 'HTMLとCSSを使用したスタイリッシュで詳細な助成金本文を生成してください',
                'requirements' => '1000-2500文字、HTML構造化、CSS付き、白黒ベースのスタイリッシュなデザイン、黄色蛍光ペン効果使用',
                'structure' => '概要（アイコン付き）→金額詳細（表組み）→対象者（箇条書き）→申請手順（ステップ表示）→必要書類（チェックリスト）→注意事項（警告ボックス）→連絡先（ボックス表示）',
                'html_requirements' => 'div, h2, h3, table, ul, ol, span, strong要素を使用。CSS classを含める。',
                'css_style' => 'モノクロ（#000, #333, #666, #ccc, #f9f9f9）+ 黄色ハイライト（#ffeb3b, #fff59d）を使用',
                'design_theme' => '白黒ベースのスタイリッシュなビジネス文書風、重要部分に黄色蛍光ペン効果'
            ],
            'post_excerpt' => [
                'instruction' => '簡潔で魅力的な助成金概要を生成してください',
                'requirements' => '120-180文字、要点を簡潔に、検索結果で目立つ内容、金額と対象を明確に',
                'focus' => '対象者、最大金額、申請期限、メリットを明確に',
                'tone' => '専門的だが親しみやすく、行動を促す表現'
            ],
            'eligibility_criteria' => [
                'instruction' => '具体的で分かりやすい対象者・応募要件をHTML形式で生成してください',
                'requirements' => 'HTML箇条書き形式、具体的な条件、除外条件も含む、視覚的に分かりやすい',
                'html_format' => '<ul>タグと<li>タグを使用、重要な条件は<strong>で強調',
                'style' => '明確で読みやすい構造、条件の階層化'
            ],
            'application_process' => [
                'instruction' => 'ステップバイステップの申請手順をHTML形式で生成してください',
                'requirements' => 'HTML番号付きリスト、各ステップの詳細、期間、注意点を含む',
                'html_format' => '<ol>と<li>を使用、各ステップに説明とポイントを追加',
                'visual_elements' => 'ステップ番号を視覚的に強調、重要な期限や注意点をハイライト'
            ],
            'required_documents' => [
                'instruction' => '必要書類一覧をHTML形式で生成してください',
                'requirements' => '具体的な書類名、取得方法、注意点をチェックリスト形式で',
                'html_format' => '<ul>でチェックリスト風、書類カテゴリーごとに整理',
                'practical_info' => '取得先や準備時間の目安も含める'
            ],
            'summary' => [
                'instruction' => '助成金の魅力的な概要をHTML形式で生成してください',
                'requirements' => '200-300文字、HTML構造化、重要ポイントを強調',
                'html_format' => '<p>と<span>を使用、キーワードを<strong>で強調',
                'content_focus' => '金額、対象者、メリット、緊急性を含める'
            ],
            'amount_details' => [
                'instruction' => '助成金額の詳細情報をHTML表形式で生成してください',
                'requirements' => 'HTML table形式、明確で理解しやすい金額体系',
                'html_format' => '<table>タグで構造化、ヘッダーと明確な項目分け',
                'content_items' => '最大金額、最小金額、補助率、対象経費を整理'
            ],
            'contact_info' => [
                'instruction' => '連絡先情報を分かりやすいHTML形式で生成してください',
                'requirements' => 'HTML構造化、電話番号、メール、住所を見やすく配置',
                'html_format' => '<div>でボックス化、各連絡手段を明確に分離',
                'practical_focus' => '営業時間や対応可能な問い合わせ内容も含める'
            ],
            'default' => [
                'instruction' => 'この助成金に関する有用な情報をHTML形式で生成してください',
                'requirements' => '正確で実用的、SEO対策済み、HTML構造化',
                'tone' => '専門的だが分かりやすい',
                'html_format' => '適切なHTML要素を使用して構造化'
            ]
        ];
    }
    
    /**
     * Get SEO instructions for specific fields
     */
    private function get_seo_instructions($field) {
        $seo_keywords = ['助成金', '補助金', '支援', '申請', '中小企業', 'スタートアップ'];
        
        switch ($field) {
            case 'post_title':
                return "SEO要件: 主要キーワードを自然に含める。検索意図に合致。32文字以内推奨。";
            case 'post_content':
                return "SEO要件: 関連キーワードを適度に配置。見出し(H2,H3)を使用。内部リンク機会を作る。ユーザーの検索意図に応える。";
            case 'post_excerpt':
                return "SEO要件: メタディスクリプションとしても機能。クリック誘導する内容。主要キーワード含む。";
            default:
                return "SEO要件: 関連キーワードを自然に含める。ユーザーに価値ある情報を提供。";
        }
    }
    
    /**
     * Build complete generation prompt with enhanced HTML/CSS support
     */
    private function build_generation_prompt($context, $field_config, $seo_instructions, $mode) {
        $prompt = "あなたは助成金・補助金の専門家兼Webデザイナーです。以下の情報を参考に、高品質で視覚的に魅力的な内容を生成してください。\n\n";
        
        if (!empty($context)) {
            $prompt .= "【参考データ】\n{$context}\n\n";
        }
        
        $prompt .= "【生成要件】\n";
        $prompt .= "目的: {$field_config['instruction']}\n";
        $prompt .= "要件: {$field_config['requirements']}\n";
        
        // HTML/CSS要件の追加
        if (isset($field_config['html_requirements'])) {
            $prompt .= "HTML要件: {$field_config['html_requirements']}\n";
        }
        
        if (isset($field_config['css_style'])) {
            $prompt .= "CSS基準: {$field_config['css_style']}\n";
        }
        
        if (isset($field_config['design_theme'])) {
            $prompt .= "デザインテーマ: {$field_config['design_theme']}\n";
        }
        
        if (isset($field_config['html_format'])) {
            $prompt .= "HTML形式: {$field_config['html_format']}\n";
        }
        
        $prompt .= "{$seo_instructions}\n\n";
        
        if (isset($field_config['structure'])) {
            $prompt .= "【コンテンツ構成】\n{$field_config['structure']}\n\n";
        }
        
        // 本文生成の場合の特別なCSS・HTMLテンプレート指示
        if (strpos($field_config['instruction'], 'HTMLとCSS') !== false) {
            $prompt .= $this->get_html_css_template_instructions();
        }
        
        $prompt .= "\n【生成モード】\n";
        switch ($mode) {
            case 'creative':
                $prompt .= "クリエイティブで魅力的な表現を重視してください。視覚的インパクトも考慮。";
                break;
            case 'professional':
                $prompt .= "専門的で正確な表現を重視してください。ビジネス文書として完成度高く。";
                break;
            case 'seo_focused':
                $prompt .= "SEO効果を最大化する内容を重視してください。検索エンジンに評価される構造で。";
                break;
            default:
                $prompt .= "バランス良く実用的な内容を生成してください。読みやすさと情報の正確性を両立。";
        }
        
        $prompt .= "\n\n【出力形式】\n";
        $prompt .= "生成内容のみを出力してください（説明文や前置きは不要）。\n";
        $prompt .= "HTMLタグを使用する場合は、正しく閉じタグまで含めて出力してください。";
        
        return $prompt;
    }
    
    /**
     * Get HTML/CSS template instructions for content generation
     */
    private function get_html_css_template_instructions() {
        return "
【HTML/CSSテンプレート指示】
1. CSSスタイル定義:
   - 基本色: #000000(黒), #333333(濃いグレー), #666666(グレー), #cccccc(薄いグレー), #f9f9f9(背景)
   - ハイライト色: #ffeb3b(黄色), #fff59d(薄い黄色) - 重要部分用蛍光ペン効果
   - フォント: sans-serif系、読みやすさ重視
   
2. 必須HTML構造:
   <div class=\"grant-content\">
     <h2 class=\"grant-section\">セクションタイトル</h2>
     <div class=\"grant-highlight\">重要情報ボックス</div>
     <table class=\"grant-table\">詳細表</table>
     <ul class=\"grant-list\">リスト項目</ul>
   </div>

3. CSS クラス定義を含めること:
   <style>
   .grant-content { /* メインコンテナ */ }
   .grant-section { /* セクション見出し */ }
   .grant-highlight { /* 重要情報ハイライト */ }
   .grant-table { /* 表組み */ }
   .grant-list { /* リスト */ }
   .highlight-yellow { /* 黄色蛍光ペン効果 */ }
   </style>

4. デザイン要素:
   - アイコンは使用せず、テキストのみで表現
   - 表組みでの情報整理
   - 重要部分への黄色ハイライト
   - 白黒ベースのスタイリッシュなレイアウト

";
    }
    
    /**
     * Regenerate existing content with improvements
     */
    private function regenerate_content($existing_data, $field, $current_content, $type) {
        $context = $this->build_context_prompt($existing_data);
        
        $prompt = "以下の内容を{$type}してください。\n\n";
        $prompt .= "【現在の内容】\n{$current_content}\n\n";
        
        if (!empty($context)) {
            $prompt .= "【参考情報】\n{$context}\n\n";
        }
        
        switch ($type) {
            case 'improve':
                $prompt .= "【改善要件】\n- より分かりやすく\n- SEO効果を向上\n- 専門性を高める\n- 文章の流れを改善";
                break;
            case 'shorten':
                $prompt .= "【短縮要件】\n- 要点を保持\n- 50%程度に短縮\n- 重要情報は残す";
                break;
            case 'expand':
                $prompt .= "【拡張要件】\n- より詳細に\n- 具体例を追加\n- 関連情報を補完";
                break;
            case 'seo_optimize':
                $prompt .= "【SEO最適化要件】\n- キーワード密度を適正化\n- 見出し構造を改善\n- 検索意図に最適化";
                break;
        }
        
        $prompt .= "\n\n改善された内容のみを出力してください:";
        
        return $this->call_openai_api($prompt);
    }
    
    /**
     * Fill multiple empty fields based on context
     */
    private function fill_empty_fields($existing_data, $empty_fields) {
        $context = $this->build_context_prompt($existing_data);
        $filled_content = [];
        
        foreach ($empty_fields as $field) {
            try {
                $field_prompts = $this->get_field_specific_prompts();
                $field_config = $field_prompts[$field] ?? $field_prompts['default'];
                
                $prompt = "以下の情報を参考に、{$field}の内容を生成してください。\n\n";
                $prompt .= "【参考情報】\n{$context}\n\n";
                $prompt .= "【要件】\n{$field_config['instruction']}\n{$field_config['requirements']}\n\n";
                $prompt .= "生成内容のみを出力してください:";
                
                $filled_content[$field] = $this->call_openai_api($prompt);
                
                // Rate limiting
                sleep(1);
                
            } catch (Exception $e) {
                $filled_content[$field] = $this->get_fallback_content($field, $existing_data);
            }
        }
        
        return $filled_content;
    }
    
    /**
     * Call OpenAI API
     */
    private function call_openai_api($prompt) {
        if (empty($this->api_key)) {
            throw new Exception('OpenAI API key not configured');
        }
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'あなたは助成金・補助金の専門家です。正確で実用的な情報を提供し、SEOも考慮した高品質な日本語コンテンツを生成してください。'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7
        ];
        
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
            'timeout' => 30
        ]);
        
        if (is_wp_error($response)) {
            throw new Exception('API request failed: ' . $response->get_error_message());
        }
        
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($response_body['error'])) {
            throw new Exception('OpenAI API error: ' . $response_body['error']['message']);
        }
        
        if (!isset($response_body['choices'][0]['message']['content'])) {
            throw new Exception('Invalid API response format');
        }
        
        return trim($response_body['choices'][0]['message']['content']);
    }
    
    /**
     * Get fallback content when AI fails
     */
    private function get_fallback_content($field, $existing_data = []) {
        $fallbacks = [
            'post_title' => $this->generate_title_fallback($existing_data),
            'post_content' => $this->generate_content_fallback($existing_data),
            'post_excerpt' => $this->generate_excerpt_fallback($existing_data),
            'eligibility_criteria' => "・中小企業、個人事業主が対象\n・法人設立から3年以内\n・従業員数50名以下\n・過去に同様の助成金を受給していないこと",
            'application_process' => "1. 申請書類の準備\n2. オンライン申請システムでの登録\n3. 必要書類のアップロード\n4. 審査結果の通知待ち\n5. 採択後の手続き",
            'required_documents' => "・申請書（指定様式）\n・会社概要書\n・事業計画書\n・見積書\n・直近の決算書\n・履歴事項全部証明書"
        ];
        
        return $fallbacks[$field] ?? "こちらの項目について詳細な情報をご確認ください。";
    }
    
    /**
     * Generate fallback fills for multiple fields
     */
    private function get_fallback_fills($fields) {
        $fills = [];
        foreach ($fields as $field) {
            $fills[$field] = $this->get_fallback_content($field);
        }
        return $fills;
    }
    
    /**
     * Generate title fallback
     */
    private function generate_title_fallback($data) {
        $org = !empty($data['organization']) ? $data['organization'] : '各自治体';
        $category = !empty($data['categories'][0]) ? $data['categories'][0] : 'ビジネス支援';
        return "{$org} {$category}助成金・補助金制度";
    }
    
    /**
     * Generate enhanced HTML content fallback with CSS styling
     */
    private function generate_content_fallback($data) {
        $title = !empty($data['title']) ? $data['title'] : '助成金制度';
        $org = !empty($data['organization']) ? $data['organization'] : '実施機関';
        $max_amount = !empty($data['max_amount']) ? $data['max_amount'] . '万円' : '規定額';
        $deadline = !empty($data['deadline']) ? $data['deadline'] : '随時受付';
        $categories = !empty($data['categories']) ? (is_array($data['categories']) ? implode('、', $data['categories']) : $data['categories']) : '事業支援';
        
        return '<style>
.grant-content { font-family: "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; }
.grant-section { color: #000; border-bottom: 2px solid #000; padding-bottom: 8px; margin: 24px 0 16px 0; font-weight: bold; }
.grant-highlight { background: #f9f9f9; border-left: 4px solid #000; padding: 16px; margin: 16px 0; }
.grant-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
.grant-table th, .grant-table td { border: 1px solid #ccc; padding: 12px; text-align: left; }
.grant-table th { background: #000; color: white; font-weight: bold; }
.grant-list { margin: 16px 0; padding-left: 24px; }
.grant-list li { margin: 8px 0; }
.highlight-yellow { background: #ffeb3b; padding: 2px 4px; font-weight: bold; }
.contact-box { background: #f9f9f9; border: 1px solid #ccc; padding: 16px; margin: 16px 0; }
.step-number { background: #000; color: white; border-radius: 50%; padding: 4px 8px; margin-right: 8px; font-weight: bold; }
</style>

<div class="grant-content">
    <div class="grant-highlight">
        <h2>■ ' . esc_html($title) . '</h2>
        <p><strong>実施機関:</strong> ' . esc_html($org) . '</p>
        <p><span class="highlight-yellow">最大助成額: ' . esc_html($max_amount) . '</span></p>
    </div>

    <h2 class="grant-section">助成金概要</h2>
    <p>' . esc_html($title) . 'は、' . esc_html($org) . 'が実施する<span class="highlight-yellow">' . esc_html($categories) . '</span>を対象とした事業者支援制度です。事業の発展と成長を支援し、競争力強化を図ることを目的としています。</p>

    <h2 class="grant-section">助成金詳細</h2>
    <table class="grant-table">
        <tr>
            <th>項目</th>
            <th>内容</th>
        </tr>
        <tr>
            <td>最大助成額</td>
            <td><span class="highlight-yellow">' . esc_html($max_amount) . '</span></td>
        </tr>
        <tr>
            <td>申請期限</td>
            <td>' . esc_html($deadline) . '</td>
        </tr>
        <tr>
            <td>対象分野</td>
            <td>' . esc_html($categories) . '</td>
        </tr>
        <tr>
            <td>実施機関</td>
            <td>' . esc_html($org) . '</td>
        </tr>
    </table>

    <h2 class="grant-section">対象者・応募要件</h2>
    <ul class="grant-list">
        <li>中小企業基本法に定める中小企業・小規模事業者</li>
        <li>個人事業主（開業届を提出している方）</li>
        <li>法人設立または開業から1年以上経過している事業者</li>
        <li>過去に同様の助成金を受給していない事業者</li>
        <li><span class="highlight-yellow">事業計画書の提出が可能な事業者</span></li>
    </ul>

    <h2 class="grant-section">申請手順</h2>
    <ol class="grant-list">
        <li><span class="step-number">1</span>申請要件の確認と事前準備</li>
        <li><span class="step-number">2</span>必要書類の準備・収集</li>
        <li><span class="step-number">3</span>事業計画書の作成</li>
        <li><span class="step-number">4</span>申請書類の提出</li>
        <li><span class="step-number">5</span>審査結果の通知待ち</li>
        <li><span class="step-number">6</span>採択後の手続き・事業実施</li>
    </ol>

    <h2 class="grant-section">お問い合わせ</h2>
    <div class="contact-box">
        <p><strong>実施機関:</strong> ' . esc_html($org) . '</p>
        <p><strong>受付時間:</strong> 平日 9:00～17:00（土日祝日を除く）</p>
        <p>詳細な申請方法や最新情報については、実施機関の公式サイトをご確認いただくか、直接お問い合わせください。</p>
    </div>

    <div class="grant-highlight">
        <p><strong> 重要:</strong> 申請期限や条件は変更される場合があります。必ず最新の公式情報をご確認の上、お申し込みください。</p>
    </div>
</div>';
    }
    
    /**
     * Generate excerpt fallback
     */
    private function generate_excerpt_fallback($data) {
        $org = !empty($data['organization']) ? $data['organization'] : '実施機関';
        $amount = !empty($data['max_amount']) ? $data['max_amount'] : '規定の金額';
        
        return "{$org}による事業者向け助成金制度。最大{$amount}の支援を受けることができます。申請条件や手続き方法について詳しくご紹介します。";
    }
    
    /**
     * Simple content improvement (non-AI)
     */
    private function improve_content_simple($content, $field) {
        // Simple text improvements without AI
        $content = trim($content);
        
        // Add structure if missing
        if ($field === 'post_content' && strpos($content, '##') === false) {
            return "## 概要\n{$content}\n\n## 詳細情報\n申請や条件について、詳細は実施機関にお問い合わせください。";
        }
        
        return $content;
    }
    
    /**
     * Sanitize input data
     */
    private function sanitize_input($data) {
        if (!is_array($data)) {
            return [];
        }
        
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized[$key] = sanitize_textarea_field($value);
            }
        }
        
        return $sanitized;
    }
}

/**
 * =============================================================================
 * SEARCH & HISTORY MANAGEMENT - Consolidated from search-functions.php
 * =============================================================================
 */

/**
 * 検索履歴の保存（統合版）
 */
function gi_save_search_history($query, $filters = [], $results_count = 0, $session_id = null) {
    if ($session_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'gi_search_history';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") == $table) {
            $wpdb->insert(
                $table,
                [
                    'session_id' => $session_id,
                    'user_id' => get_current_user_id() ?: null,
                    'search_query' => $query,
                    'search_filter' => is_array($filters) ? json_encode($filters) : $filters,
                    'results_count' => $results_count,
                    'search_time' => current_time('mysql')
                ],
                ['%s', '%d', '%s', '%s', '%d', '%s']
            );
        }
    }
    
    $user_id = get_current_user_id();
    if ($user_id) {
        $history = get_user_meta($user_id, 'gi_search_history', true) ?: [];
        
        array_unshift($history, [
            'query' => sanitize_text_field($query),
            'filters' => $filters,
            'results_count' => intval($results_count),
            'timestamp' => current_time('timestamp')
        ]);
        
        $history = array_slice($history, 0, 20);
        update_user_meta($user_id, 'gi_search_history', $history);
    }
    
    return true;
}

/**
 * 検索履歴の取得
 */
/**
 * OpenAI統合クラス
 */
class GI_OpenAI_Integration {
    private static $instance = null;
    private $api_key;
    private $api_endpoint = 'https://api.openai.com/v1/';
    
    private function __construct() {
        $this->api_key = get_option('gi_openai_api_key', '');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function is_configured() {
        return !empty($this->api_key);
    }
    
    public function generate_response($prompt, $context = []) {
        if (!$this->is_configured()) {
            return $this->generate_fallback_response($prompt, $context);
        }
        
        try {
            return $this->call_gpt_api($prompt, $context);
        } catch (Exception $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            return $this->generate_fallback_response($prompt, $context);
        }
    }
    
    private function call_gpt_api($prompt, $context = []) {
        $system_prompt = "あなたは助成金・補助金の専門アドバイザーです。";
        
        if (!empty($context['grants'])) {
            $system_prompt .= "\n\n関連する助成金情報:\n";
            foreach (array_slice($context['grants'], 0, 3) as $grant) {
                $system_prompt .= "- {$grant['title']}: {$grant['excerpt']}\n";
            }
        }
        
        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $prompt]
        ];
        
        $response = $this->make_openai_request('chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        
        throw new Exception('Invalid API response');
    }
    
    public function test_connection() {
        if (!$this->is_configured()) {
            return ['success' => false, 'message' => 'APIキーが設定されていません'];
        }
        
        try {
            $response = $this->make_openai_request('models');
            if ($response && isset($response['data'])) {
                return ['success' => true, 'message' => 'API接続成功'];
            }
            return ['success' => false, 'message' => 'API応答が無効です'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'API接続エラー: ' . $e->getMessage()];
        }
    }
    
    private function make_openai_request($endpoint, $data = null) {
        $url = $this->api_endpoint . $endpoint;
        
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ];
        
        if ($data) {
            $args['body'] = json_encode($data);
            $args['method'] = 'POST';
            $response = wp_remote_post($url, $args);
        } else {
            $response = wp_remote_get($url, $args);
        }
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $http_code = wp_remote_retrieve_response_code($response);
        
        if ($http_code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) 
                ? $error_data['error']['message'] 
                : 'HTTP Error: ' . $http_code;
            throw new Exception($error_message);
        }
        
        return json_decode($body, true);
    }
    
    private function generate_fallback_response($prompt, $context = []) {
        if (mb_stripos($prompt, '検索') !== false || mb_stripos($prompt, '補助金') !== false) {
            return 'ご質問ありがとうございます。補助金に関する詳細情報をお調べしております。具体的な業種や目的をお聞かせいただけると、より適切な情報をご提供できます。';
        }
        
        if (mb_stripos($prompt, '申請') !== false) {
            return '申請に関するご質問ですね。補助金の申請には通常、事業計画書、必要書類の準備、申請書の提出が必要です。具体的にどの補助金についてお知りになりたいですか？';
        }
        
        return 'ご質問ありがとうございます。より具体的な情報をお聞かせいただけると、詳しい回答をお提供できます。';
    }
}

/**
 * AI設定管理関数
 */
function gi_set_openai_api_key($api_key) {
    return update_option('gi_openai_api_key', sanitize_text_field($api_key));
}

function gi_get_openai_api_key() {
    return get_option('gi_openai_api_key', '');
}

// Initialize the enhanced AI generator
new GI_Enhanced_AI_Generator();

/**
 * =====================================================
 * ENHANCED AI FEATURES (v2.0)
 * =====================================================
 * 
 * New capabilities:
 * 1. Semantic Search with Vector Embeddings
 * 2. Context Memory & Personalization
 * 3. Smart Recommendations
 * 4. Advanced Caching
 * 5. Multi-turn Conversation
 */

/**
 * GI_Semantic_Search: Advanced semantic search using OpenAI Embeddings
 */
class GI_Semantic_Search {
    private static $instance = null;
    private $openai;
    private $embedding_model = 'text-embedding-3-small';
    private $cache_duration = DAY_IN_SECONDS;
    
    private function __construct() {
        $this->openai = GI_OpenAI_Integration::getInstance();
        $this->create_tables();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create embedding cache tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gi_embeddings_cache (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            content_hash varchar(64) NOT NULL,
            embedding_vector longtext NOT NULL,
            model_version varchar(50) NOT NULL DEFAULT 'text-embedding-3-small',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY post_content_hash (post_id, content_hash),
            KEY expires_at (expires_at),
            KEY post_id (post_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get or generate embedding for a post
     */
    public function get_post_embedding($post_id) {
        global $wpdb;
        
        $post = get_post($post_id);
        if (!$post) return false;
        
        // Generate content for embedding
        $content = $this->prepare_content_for_embedding($post);
        $content_hash = md5($content);
        
        // Check cache
        $table = $wpdb->prefix . 'gi_embeddings_cache';
        $cached = $wpdb->get_row($wpdb->prepare(
            "SELECT embedding_vector FROM $table 
            WHERE post_id = %d AND content_hash = %s AND expires_at > NOW()",
            $post_id, $content_hash
        ));
        
        if ($cached) {
            return json_decode($cached->embedding_vector, true);
        }
        
        // Generate new embedding
        if (!$this->openai->is_configured()) {
            return false;
        }
        
        try {
            $embedding = $this->generate_embedding($content);
            if ($embedding) {
                // Cache the embedding
                $wpdb->replace($table, [
                    'post_id' => $post_id,
                    'content_hash' => $content_hash,
                    'embedding_vector' => json_encode($embedding),
                    'model_version' => $this->embedding_model,
                    'expires_at' => date('Y-m-d H:i:s', time() + $this->cache_duration)
                ]);
                return $embedding;
            }
        } catch (Exception $e) {
            error_log('Embedding generation failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Prepare post content for embedding
     */
    private function prepare_content_for_embedding($post) {
        $parts = [];
        
        // Title (重要度高)
        $parts[] = $post->post_title . '. ' . $post->post_title;
        
        // ACF fields
        $acf_fields = ['organization', 'grant_target', 'deadline', 'max_amount'];
        foreach ($acf_fields as $field) {
            $value = get_field($field, $post->ID);
            if ($value) {
                $parts[] = $value;
            }
        }
        
        // Categories and tags
        $categories = wp_get_post_terms($post->ID, 'grant_category', ['fields' => 'names']);
        if (!empty($categories) && !is_wp_error($categories)) {
            $parts[] = implode(' ', $categories);
        }
        
        // Content (first 500 chars)
        $parts[] = wp_trim_words($post->post_content, 100, '');
        
        return implode(' ', $parts);
    }
    
    /**
     * Generate embedding using OpenAI API
     */
    private function generate_embedding($text) {
        $response = gi_make_embedding_request($text, $this->embedding_model);
        if ($response && isset($response['data'][0]['embedding'])) {
            return $response['data'][0]['embedding'];
        }
        return false;
    }
    
    /**
     * Semantic search for grants
     */
    public function semantic_search($query, $limit = 10) {
        if (!$this->openai->is_configured()) {
            return [];
        }
        
        // Get query embedding
        $query_embedding = $this->generate_embedding($query);
        if (!$query_embedding) {
            return [];
        }
        
        // Get all grant posts with embeddings
        $posts = get_posts([
            'post_type' => 'grant',
            'post_status' => 'publish',
            'numberposts' => -1
        ]);
        
        $results = [];
        foreach ($posts as $post) {
            $post_embedding = $this->get_post_embedding($post->ID);
            if ($post_embedding) {
                $similarity = $this->cosine_similarity($query_embedding, $post_embedding);
                $results[] = [
                    'post_id' => $post->ID,
                    'similarity' => $similarity,
                    'post' => $post
                ];
            }
        }
        
        // Sort by similarity
        usort($results, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($results, 0, $limit);
    }
    
    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosine_similarity($vec1, $vec2) {
        if (count($vec1) !== count($vec2)) {
            return 0;
        }
        
        $dot_product = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        for ($i = 0; $i < count($vec1); $i++) {
            $dot_product += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] * $vec1[$i];
            $magnitude2 += $vec2[$i] * $vec2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        
        return $dot_product / ($magnitude1 * $magnitude2);
    }
    
    /**
     * Cleanup expired cache entries
     */
    public function cleanup_expired_cache() {
        global $wpdb;
        $table = $wpdb->prefix . 'gi_embeddings_cache';
        $wpdb->query("DELETE FROM $table WHERE expires_at < NOW()");
    }
}

/**
 * GI_Context_Manager: User context and conversation memory
 */
class GI_Context_Manager {
    private static $instance = null;
    private $max_history = 10;
    
    private function __construct() {
        $this->create_tables();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create context tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gi_user_context (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NULL,
            session_id varchar(64) NOT NULL,
            interaction_type varchar(20) NOT NULL,
            query text NOT NULL,
            response longtext NULL,
            metadata longtext NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_session (user_id, session_id),
            KEY session_id (session_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Save interaction to context
     */
    public function save_interaction($type, $query, $response = '', $metadata = []) {
        global $wpdb;
        
        $user_id = get_current_user_id() ?: null;
        $session_id = $this->get_session_id();
        
        $wpdb->insert(
            $wpdb->prefix . 'gi_user_context',
            [
                'user_id' => $user_id,
                'session_id' => $session_id,
                'interaction_type' => $type,
                'query' => $query,
                'response' => $response,
                'metadata' => json_encode($metadata),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        // Also save to user meta for logged-in users
        if ($user_id) {
            $history = get_user_meta($user_id, 'gi_interaction_history', true) ?: [];
            array_unshift($history, [
                'type' => $type,
                'query' => $query,
                'response' => substr($response, 0, 200),
                'timestamp' => time()
            ]);
            $history = array_slice($history, 0, $this->max_history);
            update_user_meta($user_id, 'gi_interaction_history', $history);
        }
    }
    
    /**
     * Get user context history
     */
    public function get_context_history($limit = 5) {
        global $wpdb;
        
        $session_id = $this->get_session_id();
        $user_id = get_current_user_id();
        
        $where = $user_id 
            ? $wpdb->prepare("user_id = %d", $user_id)
            : $wpdb->prepare("session_id = %s", $session_id);
        
        $results = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}gi_user_context 
            WHERE $where 
            ORDER BY created_at DESC 
            LIMIT %d
        ", $limit);
        
        return $results ?: [];
    }
    
    /**
     * Build context for AI prompt
     */
    public function build_context_prompt($current_query) {
        $history = $this->get_context_history(3);
        
        if (empty($history)) {
            return $current_query;
        }
        
        $context = "Previous conversation:\n";
        foreach (array_reverse($history) as $item) {
            $context .= "User: {$item->query}\n";
            if ($item->response) {
                $context .= "Assistant: " . wp_trim_words($item->response, 30) . "\n";
            }
        }
        $context .= "\nCurrent question: {$current_query}";
        
        return $context;
    }
    
    /**
     * Get or create session ID
     */
    private function get_session_id() {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['gi_session_id'])) {
            $_SESSION['gi_session_id'] = wp_generate_password(32, false);
        }
        
        return $_SESSION['gi_session_id'];
    }
    
    /**
     * Cleanup old context data (older than 30 days)
     */
    public function cleanup_old_context() {
        global $wpdb;
        $wpdb->query("
            DELETE FROM {$wpdb->prefix}gi_user_context 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }
}

/**
 * Enhanced OpenAI Integration with new methods
 */
// Add new methods to existing class
if (class_exists('GI_OpenAI_Integration')) {
    // Extend the class with new embedding method
    add_filter('gi_openai_make_request', function($response, $endpoint, $data) {
        if ($endpoint === 'embeddings') {
            $openai = GI_OpenAI_Integration::getInstance();
            return $openai->make_embedding_request($data['input'], $data['model']);
        }
        return $response;
    }, 10, 3);
}

/**
 * Enhanced AJAX handlers
 */

// Enhanced semantic search handler
add_action('wp_ajax_gi_semantic_search', 'gi_handle_semantic_search');
add_action('wp_ajax_nopriv_gi_semantic_search', 'gi_handle_semantic_search');

function gi_handle_semantic_search() {
    $query = sanitize_text_field($_POST['query'] ?? '');
    
    if (empty($query)) {
        wp_send_json_error('検索クエリが空です');
    }
    
    $semantic_search = GI_Semantic_Search::getInstance();
    $context_manager = GI_Context_Manager::getInstance();
    
    // Save search query
    $context_manager->save_interaction('search', $query);
    
    // Try semantic search first
    $results = $semantic_search->semantic_search($query, 10);
    
    // Fallback to regular search if needed
    if (empty($results)) {
        $results = gi_fallback_search($query);
    }
    
    // Prepare response
    $formatted_results = [];
    foreach ($results as $result) {
        $post = isset($result['post']) ? $result['post'] : get_post($result['post_id']);
        $formatted_results[] = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'excerpt' => wp_trim_words($post->post_content, 30),
            'url' => get_permalink($post->ID),
            'similarity' => isset($result['similarity']) ? round($result['similarity'], 3) : null
        ];
    }
    
    wp_send_json_success([
        'results' => $formatted_results,
        'count' => count($formatted_results),
        'method' => empty($results) ? 'keyword' : 'semantic'
    ]);
}

// Enhanced chat with context
add_action('wp_ajax_gi_contextual_chat', 'gi_handle_contextual_chat');
add_action('wp_ajax_nopriv_gi_contextual_chat', 'gi_handle_contextual_chat');

function gi_handle_contextual_chat() {
    $query = sanitize_text_field($_POST['message'] ?? '');
    
    if (empty($query)) {
        wp_send_json_error('メッセージが空です');
    }
    
    $openai = GI_OpenAI_Integration::getInstance();
    $context_manager = GI_Context_Manager::getInstance();
    
    // Build context-aware prompt
    $contextual_prompt = $context_manager->build_context_prompt($query);
    
    // Get related grants for context
    $semantic_search = GI_Semantic_Search::getInstance();
    $related_grants = $semantic_search->semantic_search($query, 3);
    
    $context = [
        'grants' => array_map(function($item) {
            $post = $item['post'];
            return [
                'title' => $post->post_title,
                'excerpt' => wp_trim_words($post->post_content, 50)
            ];
        }, $related_grants)
    ];
    
    // Generate response
    $response = $openai->generate_response($contextual_prompt, $context);
    
    // Save interaction
    $context_manager->save_interaction('chat', $query, $response);
    
    wp_send_json_success([
        'response' => $response,
        'related_grants' => array_slice($related_grants, 0, 3),
        'has_context' => !empty($context['grants'])
    ]);
}

/**
 * Fallback search function
 */
function gi_fallback_search($query) {
    $args = [
        'post_type' => 'grant',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        's' => $query
    ];
    
    $search_query = new WP_Query($args);
    $results = [];
    
    if ($search_query->have_posts()) {
        while ($search_query->have_posts()) {
            $search_query->the_post();
            $results[] = [
                'post_id' => get_the_ID(),
                'post' => get_post(get_the_ID())
            ];
        }
        wp_reset_postdata();
    }
    
    return $results;
}

/**
 * Scheduled cleanup tasks
 */
add_action('gi_daily_cleanup', function() {
    $semantic_search = GI_Semantic_Search::getInstance();
    $semantic_search->cleanup_expired_cache();
    
    $context_manager = GI_Context_Manager::getInstance();
    $context_manager->cleanup_old_context();
});

if (!wp_next_scheduled('gi_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'gi_daily_cleanup');
}

/**
 * Add embedding generation method to OpenAI class
 */
add_filter('gi_openai_custom_method', function($result, $method, $args) {
    if ($method === 'make_embedding_request') {
        $openai = GI_OpenAI_Integration::getInstance();
        if (!$openai->is_configured()) {
            return false;
        }
        
        list($text, $model) = $args;
        
        try {
            $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
                'headers' => [
                    'Authorization' => 'Bearer ' . get_option('gi_openai_api_key', ''),
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'input' => $text,
                    'model' => $model
                ]),
                'timeout' => 30
            ]);
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return $body;
            
        } catch (Exception $e) {
            error_log('Embedding API error: ' . $e->getMessage());
            return false;
        }
    }
    
    return $result;
}, 10, 3);

/**
 * Helper function to call embedding API
 */
function gi_make_embedding_request($text, $model = 'text-embedding-3-small') {
    return apply_filters('gi_openai_custom_method', false, 'make_embedding_request', [$text, $model]);
}

/**
 * =====================================================
 * SMART QUERY SUGGESTIONS & ALTERNATIVE SEARCH
 * =====================================================
 */

/**
 * GI_Smart_Query_Assistant: Intelligent query suggestions and alternatives
 */
class GI_Smart_Query_Assistant {
    private static $instance = null;
    private $openai;
    
    private function __construct() {
        $this->openai = GI_OpenAI_Integration::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate smart suggestions when no results found
     */
    public function generate_no_results_suggestions($query, $filters = []) {
        $suggestions = [
            'alternative_queries' => $this->generate_alternative_queries($query),
            'related_categories' => $this->suggest_related_categories($query),
            'search_tips' => $this->get_search_tips($query),
            'popular_grants' => $this->get_popular_grants(),
            'example_queries' => $this->get_example_queries($query)
        ];
        
        return $suggestions;
    }
    
    /**
     * Generate alternative search queries
     */
    private function generate_alternative_queries($query) {
        $alternatives = [];
        
        // パターンベースの提案
        $patterns = [
            'DX' => ['デジタル化', 'IT導入', 'システム化', 'デジタルトランスフォーメーション'],
            'スタートアップ' => ['創業', '起業', 'ベンチャー', '新規事業'],
            '製造業' => ['ものづくり', '工場', '生産', '加工'],
            '中小企業' => ['小規模事業者', 'SME', '中堅企業'],
            '補助金' => ['助成金', '支援金', '給付金', '奨励金'],
            '東京' => ['首都圏', '関東', '都内'],
            '研究開発' => ['R&D', '技術開発', 'イノベーション', '新技術']
        ];
        
        foreach ($patterns as $keyword => $synonyms) {
            if (mb_stripos($query, $keyword) !== false) {
                foreach ($synonyms as $synonym) {
                    $alt_query = str_replace($keyword, $synonym, $query);
                    if ($alt_query !== $query) {
                        $alternatives[] = [
                            'query' => $alt_query,
                            'reason' => "「{$keyword}」を「{$synonym}」に言い換えました"
                        ];
                    }
                }
            }
        }
        
        // AI生成の提案（OpenAI利用可能時）
        if ($this->openai->is_configured() && count($alternatives) < 3) {
            $ai_suggestions = $this->generate_ai_alternative_queries($query);
            $alternatives = array_merge($alternatives, $ai_suggestions);
        }
        
        return array_slice($alternatives, 0, 5);
    }
    
    /**
     * AI-powered alternative query generation
     */
    private function generate_ai_alternative_queries($query) {
        if (!$this->openai->is_configured()) {
            return [];
        }
        
        try {
            $prompt = "以下の助成金検索クエリで結果が見つかりませんでした。より良い検索結果が得られる可能性のある、別の言い回しや関連キーワードを3つ提案してください。

元のクエリ: {$query}

各提案は以下の形式で:
1. [代替クエリ]
理由: [なぜこの提案が有効か]

JSON形式で回答してください:
{\"suggestions\": [{\"query\": \"...\", \"reason\": \"...\"}]}";

            $response = $this->openai->generate_response($prompt, []);
            
            // JSONパース
            if (preg_match('/\{.*\}/s', $response, $matches)) {
                $data = json_decode($matches[0], true);
                if (isset($data['suggestions']) && is_array($data['suggestions'])) {
                    return $data['suggestions'];
                }
            }
        } catch (Exception $e) {
            error_log('AI alternative query generation failed: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Suggest related categories
     */
    private function suggest_related_categories($query) {
        $category_mapping = [
            'IT' => ['grant_category' => ['IT関連', 'デジタル化', 'システム開発']],
            'DX' => ['grant_category' => ['IT関連', 'デジタル化', 'イノベーション']],
            '製造' => ['grant_category' => ['ものづくり', '製造業', '技術開発']],
            'スタートアップ' => ['grant_category' => ['創業支援', 'ベンチャー', '起業']],
            '環境' => ['grant_category' => ['環境・エネルギー', 'サステナビリティ', 'SDGs']],
            '農業' => ['grant_category' => ['農林水産', '6次産業化']],
            '観光' => ['grant_category' => ['観光', '地域活性化']],
            '研究' => ['grant_category' => ['研究開発', 'R&D', 'イノベーション']]
        ];
        
        $suggestions = [];
        
        foreach ($category_mapping as $keyword => $cats) {
            if (mb_stripos($query, $keyword) !== false) {
                foreach ($cats['grant_category'] as $cat) {
                    $term = get_term_by('name', $cat, 'grant_category');
                    if ($term) {
                        $suggestions[] = [
                            'category' => $cat,
                            'term_id' => $term->term_id,
                            'count' => $term->count,
                            'link' => get_term_link($term)
                        ];
                    }
                }
            }
        }
        
        // カテゴリが見つからない場合は人気カテゴリを提案
        if (empty($suggestions)) {
            $popular_cats = get_terms([
                'taxonomy' => 'grant_category',
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 5,
                'hide_empty' => true
            ]);
            
            foreach ($popular_cats as $term) {
                $suggestions[] = [
                    'category' => $term->name,
                    'term_id' => $term->term_id,
                    'count' => $term->count,
                    'link' => get_term_link($term)
                ];
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Get search tips based on query
     */
    private function get_search_tips($query) {
        $tips = [];
        
        // クエリ分析
        $is_too_short = mb_strlen($query) < 3;
        $is_too_long = mb_strlen($query) > 50;
        $has_specific_location = preg_match('/(東京|大阪|愛知|福岡|北海道|神奈川|埼玉|千葉)/u', $query);
        $has_industry = preg_match('/(製造|IT|農業|観光|飲食|建設|医療|介護)/u', $query);
        $has_purpose = preg_match('/(創業|設備|開発|雇用|販路|輸出)/u', $query);
        
        if ($is_too_short) {
            $tips[] = [
                'type' => 'length',
                'icon' => '',
                'title' => 'より詳しいキーワードを追加してみましょう',
                'description' => '「業種」「目的」「地域」を組み合わせると、より的確な結果が見つかります',
                'example' => '例: 「IT 東京 スタートアップ」'
            ];
        }
        
        if (!$has_industry) {
            $tips[] = [
                'type' => 'industry',
                'icon' => '🏭',
                'title' => '業種を追加してみましょう',
                'description' => '対象業種を指定すると、より適切な助成金が見つかります',
                'example' => '例: 「製造業」「IT業」「飲食業」など'
            ];
        }
        
        if (!$has_specific_location) {
            $tips[] = [
                'type' => 'location',
                'icon' => '📍',
                'title' => '地域を指定してみましょう',
                'description' => '都道府県や市区町村を指定すると、地域限定の助成金も見つかります',
                'example' => '例: 「東京都」「大阪市」など'
            ];
        }
        
        if (!$has_purpose) {
            $tips[] = [
                'type' => 'purpose',
                'icon' => '',
                'title' => '目的を明確にしてみましょう',
                'description' => '何に使いたいかを指定すると、マッチする助成金が見つかりやすくなります',
                'example' => '例: 「設備投資」「人材採用」「販路拡大」など'
            ];
        }
        
        // 一般的なヒント
        $tips[] = [
            'type' => 'general',
            'icon' => '',
            'title' => 'カテゴリから探す',
            'description' => 'カテゴリ一覧から興味のある分野を選んでみましょう',
            'action' => 'show_categories'
        ];
        
        return array_slice($tips, 0, 3);
    }
    
    /**
     * Get popular grants as fallback
     */
    private function get_popular_grants($limit = 5) {
        // 閲覧数が多い助成金を取得
        $args = [
            'post_type' => 'grant',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_key' => 'view_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ];
        
        $query = new WP_Query($args);
        $grants = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $grants[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => wp_trim_words(get_the_excerpt(), 30),
                    'url' => get_permalink(),
                    'view_count' => get_post_meta(get_the_ID(), 'view_count', true) ?: 0
                ];
            }
            wp_reset_postdata();
        }
        
        // 閲覧数がない場合は最新の助成金
        if (empty($grants)) {
            $args = [
                'post_type' => 'grant',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'orderby' => 'date',
                'order' => 'DESC'
            ];
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $grants[] = [
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'excerpt' => wp_trim_words(get_the_excerpt(), 30),
                        'url' => get_permalink()
                    ];
                }
                wp_reset_postdata();
            }
        }
        
        return $grants;
    }
    
    /**
     * Get example queries
     */
    private function get_example_queries($original_query) {
        $examples = [
            [
                'query' => '東京都 IT スタートアップ 創業',
                'description' => '地域・業種・目的を組み合わせた検索'
            ],
            [
                'query' => '製造業 設備投資 補助金',
                'description' => '業種と目的で絞り込んだ検索'
            ],
            [
                'query' => '中小企業 DX デジタル化支援',
                'description' => '対象者とキーワードを明確にした検索'
            ],
            [
                'query' => '研究開発 R&D イノベーション',
                'description' => '関連キーワードを複数使用した検索'
            ],
            [
                'query' => '飲食業 販路拡大 コロナ対策',
                'description' => '時事的なキーワードを含めた検索'
            ]
        ];
        
        // ランダムに3つ選択
        shuffle($examples);
        return array_slice($examples, 0, 3);
    }
    
    /**
     * Generate context-aware suggestions
     */
    public function generate_contextual_suggestions($user_id = null) {
        $context_manager = GI_Context_Manager::getInstance();
        $history = $context_manager->get_context_history(5);
        
        $suggestions = [];
        
        // 履歴に基づいた提案
        if (!empty($history)) {
            $recent_queries = array_map(function($item) {
                return $item->query;
            }, $history);
            
            $suggestions['based_on_history'] = [
                'title' => '最近の検索に基づく提案',
                'queries' => $this->generate_follow_up_queries($recent_queries)
            ];
        }
        
        // 時期に基づいた提案
        $seasonal_suggestions = $this->get_seasonal_suggestions();
        if (!empty($seasonal_suggestions)) {
            $suggestions['seasonal'] = $seasonal_suggestions;
        }
        
        return $suggestions;
    }
    
    /**
     * Generate follow-up queries
     */
    private function generate_follow_up_queries($recent_queries) {
        $follow_ups = [];
        
        foreach ($recent_queries as $query) {
            // より詳細な検索を提案
            if (mb_strlen($query) < 20) {
                $follow_ups[] = [
                    'query' => $query . ' 詳細',
                    'type' => 'detail',
                    'label' => '詳しく検索'
                ];
                
                $follow_ups[] = [
                    'query' => $query . ' 申請方法',
                    'type' => 'how_to',
                    'label' => '申請方法を調べる'
                ];
            }
            
            // 類似検索を提案
            $follow_ups[] = [
                'query' => $query . ' 類似',
                'type' => 'similar',
                'label' => '類似の助成金を探す'
            ];
        }
        
        return array_slice($follow_ups, 0, 5);
    }
    
    /**
     * Get seasonal suggestions
     */
    private function get_seasonal_suggestions() {
        $month = date('n');
        $suggestions = [];
        
        $seasonal_keywords = [
            1 => ['新年', '創業', '起業', '新規事業'],
            2 => ['確定申告', '決算', '補助金申請'],
            3 => ['新年度', '採用', '教育訓練'],
            4 => ['新入社員', '人材育成', '研修'],
            5 => ['中間決算', '設備投資'],
            6 => ['省エネ', '環境対策', 'SDGs'],
            7 => ['夏季休暇', 'インターン', '採用'],
            8 => ['事業計画', '下半期', '戦略'],
            9 => ['決算準備', '税制', '補助金'],
            10 => ['年末調整', '資金調達'],
            11 => ['年末決算', '来期計画'],
            12 => ['年末商戦', '確定申告準備']
        ];
        
        if (isset($seasonal_keywords[$month])) {
            $suggestions = [
                'title' => '今月のおすすめキーワード',
                'keywords' => $seasonal_keywords[$month],
                'month' => $month
            ];
        }
        
        return $suggestions;
    }
}

/**
 * AJAX Handler: Smart suggestions when no results
 */
add_action('wp_ajax_gi_no_results_suggestions', 'gi_handle_no_results_suggestions');
add_action('wp_ajax_nopriv_gi_no_results_suggestions', 'gi_handle_no_results_suggestions');

function gi_handle_no_results_suggestions() {
    $query = sanitize_text_field($_POST['query'] ?? '');
    $filters = $_POST['filters'] ?? [];
    
    if (empty($query)) {
        wp_send_json_error('検索クエリが必要です');
    }
    
    $assistant = GI_Smart_Query_Assistant::getInstance();
    $suggestions = $assistant->generate_no_results_suggestions($query, $filters);
    
    wp_send_json_success($suggestions);
}

/**
 * AJAX Handler: Contextual suggestions
 */
add_action('wp_ajax_gi_contextual_suggestions', 'gi_handle_contextual_suggestions');
add_action('wp_ajax_nopriv_gi_contextual_suggestions', 'gi_handle_contextual_suggestions');

function gi_handle_contextual_suggestions() {
    $user_id = get_current_user_id() ?: null;
    
    $assistant = GI_Smart_Query_Assistant::getInstance();
    $suggestions = $assistant->generate_contextual_suggestions($user_id);
    
    wp_send_json_success($suggestions);
}

// ============================================================================
// 新AI機能群（モノクロームデザイン対応）
// ============================================================================

/**
 * 提案1: AI適合度スコア計算
 * ユーザーコンテキストと助成金情報から適合度を算出（0-100%）
 */
function gi_calculate_match_score($post_id, $user_context = null) {
    if (!$user_context) {
        $user_context = gi_get_user_context();
    }
    
    // ユーザーコンテキストがなくても、基本情報から適合度を計算
    $score = 70; // ベーススコアを表示閾値以上に
    
    // 業種マッチング
    $grant_categories = wp_get_post_terms($post_id, 'grant_category', ['fields' => 'names']);
    if (!empty($grant_categories) && !empty($user_context['industry'])) {
        foreach ($grant_categories as $cat) {
            if (stripos($cat, $user_context['industry']) !== false) {
                $score += 20;
                break;
            }
        }
    }
    
    // 地域マッチング
    $grant_prefecture = wp_get_post_terms($post_id, 'grant_prefecture', ['fields' => 'names']);
    if (!empty($grant_prefecture) && !empty($user_context['prefecture'])) {
        if (in_array($user_context['prefecture'], $grant_prefecture)) {
            $score += 15;
        }
    }
    
    // 金額範囲マッチング
    $max_amount = get_field('max_amount_numeric', $post_id);
    if ($max_amount && !empty($user_context['budget_range'])) {
        $budget = $user_context['budget_range'];
        if ($max_amount >= $budget['min'] && $max_amount <= $budget['max']) {
            $score += 15;
        }
    } elseif ($max_amount > 10000000) {
        // 高額助成金は適合度アップ
        $score += 10;
    }
    
    return min(100, max(0, $score));
}

/**
 * ユーザーコンテキスト取得（検索履歴・プロフィールから）
 */
function gi_get_user_context() {
    $context = [
        'industry' => '',
        'prefecture' => '',
        'budget_range' => ['min' => 0, 'max' => PHP_INT_MAX],
        'search_history' => []
    ];
    
    // Cookie/SessionからContextを取得
    if (isset($_COOKIE['gi_user_industry'])) {
        $context['industry'] = sanitize_text_field($_COOKIE['gi_user_industry']);
    }
    if (isset($_COOKIE['gi_user_prefecture'])) {
        $context['prefecture'] = sanitize_text_field($_COOKIE['gi_user_prefecture']);
    }
    
    // 検索履歴から推測
    $search_history = get_transient('gi_user_search_' . session_id());
    if ($search_history) {
        $context['search_history'] = $search_history;
    }
    
    return $context;
}

/**
 * 提案2: AI申請難易度分析（1-5段階）
 */
function gi_calculate_difficulty_score($post_id) {
    $score = 3; // デフォルト: 普通
    
    // 必要書類数（ACFフィールド使用）
    $required_docs = get_field('required_documents', $post_id);
    $doc_count = !empty($required_docs) ? count(explode("\n", $required_docs)) : 0;
    
    if ($doc_count >= 10) {
        $score += 1;
    } elseif ($doc_count <= 3) {
        $score -= 1;
    }
    
    // 採択率（ACFフィールド名: adoption_rate）
    $success_rate = floatval(get_field('adoption_rate', $post_id));
    if ($success_rate > 70) {
        $score -= 1;
    } elseif ($success_rate < 30 && $success_rate > 0) {
        $score += 1;
    }
    
    // 対象条件の複雑さ（ACFフィールド使用）
    $target = get_field('grant_target', $post_id);
    if (strlen($target) > 200) {
        $score += 0.5;
    }
    
    $score = max(1, min(5, $score));
    
    $labels = [
        1 => ['label' => '非常に易しい', 'stars' => '1/5', 'class' => 'very-easy', 'dots' => 1],
        2 => ['label' => 'やや易しい', 'stars' => '2/5', 'class' => 'easy', 'dots' => 2],
        3 => ['label' => '普通', 'stars' => '3/5', 'class' => 'normal', 'dots' => 3],
        4 => ['label' => 'やや難しい', 'stars' => '4/5', 'class' => 'hard', 'dots' => 4],
        5 => ['label' => '非常に難しい', 'stars' => '5/5', 'class' => 'very-hard', 'dots' => 5]
    ];
    
    $difficulty = round($score);
    return array_merge(['score' => $difficulty], $labels[$difficulty]);
}

/**
 * 提案3: 類似助成金レコメンド（上位5件）
 */
function gi_get_similar_grants($post_id, $limit = 5) {
    $categories = wp_get_post_terms($post_id, 'grant_category', ['fields' => 'ids']);
    $prefecture = wp_get_post_terms($post_id, 'grant_prefecture', ['fields' => 'ids']);
    
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $limit + 1,
        'post__not_in' => [$post_id],
        'tax_query' => []
    ];
    
    if (!empty($categories)) {
        $args['tax_query'][] = [
            'taxonomy' => 'grant_category',
            'field' => 'term_id',
            'terms' => $categories
        ];
    }
    
    $query = new WP_Query($args);
    return $query->posts;
}

/**
 * 提案7: 期限アラート判定（ACFフィールド使用、アイコン・絵文字削除）
 */
function gi_get_deadline_urgency($post_id) {
    // ACFフィールドから締切日を取得
    $deadline_date = get_field('deadline_date', $post_id);
    if (empty($deadline_date)) {
        $deadline_date = get_field('deadline', $post_id);
    }
    
    if (empty($deadline_date)) {
        return null;
    }
    
    $deadline_timestamp = is_numeric($deadline_date) ? intval($deadline_date) : strtotime($deadline_date);
    if (!$deadline_timestamp) {
        return null;
    }
    
    $now = current_time('timestamp');
    $days_left = floor(($deadline_timestamp - $now) / (60 * 60 * 24));
    
    if ($days_left < 0) {
        return ['level' => 'expired', 'color' => '#999', 'text' => '期限切れ'];
    } elseif ($days_left <= 3) {
        return ['level' => 'critical', 'color' => '#dc2626', 'text' => "残り{$days_left}日！"];
    } elseif ($days_left <= 7) {
        return ['level' => 'urgent', 'color' => '#f59e0b', 'text' => "残り{$days_left}日"];
    } elseif ($days_left <= 30) {
        return ['level' => 'warning', 'color' => '#eab308', 'text' => "残り{$days_left}日"];
    } else {
        return ['level' => 'safe', 'color' => '#10b981', 'text' => "{$days_left}日"];
    }
}

/**
 * AJAX: チェックリスト生成
 */
add_action('wp_ajax_gi_generate_checklist', 'gi_handle_generate_checklist');
add_action('wp_ajax_nopriv_gi_generate_checklist', 'gi_handle_generate_checklist');

function gi_handle_generate_checklist() {
    check_ajax_referer('gi_ai_search_nonce', 'nonce');
    
    $post_id = intval($_POST['post_id']);
    $grant_title = get_the_title($post_id);
    
    // 基本的なチェックリスト項目
    $checklist = [
        ['id' => 1, 'text' => '事業計画書の作成', 'checked' => false, 'priority' => 'high'],
        ['id' => 2, 'text' => '見積書の取得（3社以上）', 'checked' => false, 'priority' => 'high'],
        ['id' => 3, 'text' => '登記簿謄本の準備', 'checked' => false, 'priority' => 'medium'],
        ['id' => 4, 'text' => '決算書（直近2期分）', 'checked' => false, 'priority' => 'medium'],
        ['id' => 5, 'text' => '納税証明書の取得', 'checked' => false, 'priority' => 'medium'],
        ['id' => 6, 'text' => '事業概要説明資料', 'checked' => false, 'priority' => 'low'],
        ['id' => 7, 'text' => '申請書類のレビュー', 'checked' => false, 'priority' => 'high']
    ];
    
    wp_send_json_success([
        'checklist' => $checklist,
        'title' => $grant_title
    ]);
}

/**
 * AJAX: AI比較分析
 */
add_action('wp_ajax_gi_compare_grants', 'gi_handle_compare_grants');
add_action('wp_ajax_nopriv_gi_compare_grants', 'gi_handle_compare_grants');

function gi_handle_compare_grants() {
    check_ajax_referer('gi_ai_search_nonce', 'nonce');
    
    $grant_ids = array_map('intval', $_POST['grant_ids']);
    $comparison = [];
    
    foreach ($grant_ids as $id) {
        $comparison[] = [
            'id' => $id,
            'title' => get_the_title($id),
            'amount' => get_post_meta($id, 'max_amount', true),
            'rate' => get_post_meta($id, 'grant_success_rate', true),
            'deadline' => get_post_meta($id, 'deadline', true),
            'match_score' => gi_calculate_match_score($id),
            'difficulty' => gi_calculate_difficulty_score($id)
        ];
    }
    
    // 最適な助成金を判定
    usort($comparison, function($a, $b) {
        return $b['match_score'] - $a['match_score'];
    });
    
    $recommendation = $comparison[0];
    
    wp_send_json_success([
        'comparison' => $comparison,
        'recommendation' => $recommendation
    ]);
}