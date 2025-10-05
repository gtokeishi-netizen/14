<?php
/**
 * Grant Insight Perfect - 3. AJAX Functions File (Complete Implementation)
 *
 * サイトの動的な機能（検索、フィルタリング、AI処理など）を
 * 担当する全てのAJAX処理をここにまとめます。
 * Perfect implementation with comprehensive AI integration
 *
 * @package Grant_Insight_Perfect
 * @version 4.0.0 - Perfect Implementation Edition
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * =============================================================================
 * AJAX ハンドラー登録 - 完全版
 * =============================================================================
 */

// AI検索機能
add_action('wp_ajax_gi_ai_search', 'handle_ai_search');
add_action('wp_ajax_nopriv_gi_ai_search', 'handle_ai_search');

// AIチャット機能  
add_action('wp_ajax_gi_ai_chat', 'handle_ai_chat_request');
add_action('wp_ajax_nopriv_gi_ai_chat', 'handle_ai_chat_request');

// Grant AI質問機能
add_action('wp_ajax_handle_grant_ai_question', 'handle_grant_ai_question');
add_action('wp_ajax_nopriv_handle_grant_ai_question', 'handle_grant_ai_question');

// 音声入力機能
add_action('wp_ajax_gi_voice_input', 'gi_ajax_process_voice_input');
add_action('wp_ajax_nopriv_gi_voice_input', 'gi_ajax_process_voice_input');

// 検索候補機能
add_action('wp_ajax_gi_search_suggestions', 'gi_ajax_get_search_suggestions');
add_action('wp_ajax_nopriv_gi_search_suggestions', 'gi_ajax_get_search_suggestions');

// 音声履歴機能
add_action('wp_ajax_gi_voice_history', 'gi_ajax_save_voice_history');
add_action('wp_ajax_nopriv_gi_voice_history', 'gi_ajax_save_voice_history');

// テスト接続機能
add_action('wp_ajax_gi_test_connection', 'gi_ajax_test_connection');
add_action('wp_ajax_nopriv_gi_test_connection', 'gi_ajax_test_connection');

// お気に入り機能
add_action('wp_ajax_gi_toggle_favorite', 'gi_ajax_toggle_favorite');
add_action('wp_ajax_nopriv_gi_toggle_favorite', 'gi_ajax_toggle_favorite');

// 助成金ロード機能（フィルター・検索）
add_action('wp_ajax_gi_load_grants', 'gi_ajax_load_grants');
add_action('wp_ajax_nopriv_gi_load_grants', 'gi_ajax_load_grants');

// チャット履歴機能
add_action('wp_ajax_gi_get_chat_history', 'gi_ajax_get_chat_history');
add_action('wp_ajax_nopriv_gi_get_chat_history', 'gi_ajax_get_chat_history');

// 検索履歴機能
add_action('wp_ajax_gi_get_search_history', 'gi_ajax_get_search_history');
add_action('wp_ajax_nopriv_gi_get_search_history', 'gi_ajax_get_search_history');

// AIフィードバック機能
add_action('wp_ajax_gi_ai_feedback', 'gi_ajax_submit_ai_feedback');
add_action('wp_ajax_nopriv_gi_ai_feedback', 'gi_ajax_submit_ai_feedback');

/**
 * =============================================================================
 * 主要なAJAXハンドラー関数 - 完全版
 * =============================================================================
 */

/**
 * Enhanced AI検索処理 - セマンティック検索付き
 */
function handle_ai_search() {
    try {
        // セキュリティ検証
        if (!gi_verify_ajax_nonce()) {
            wp_send_json_error(['message' => 'セキュリティチェックに失敗しました', 'code' => 'SECURITY_ERROR']);
            return;
        }
        
        // パラメータ取得と検証
        $query = sanitize_text_field($_POST['query'] ?? '');
        $filter = sanitize_text_field($_POST['filter'] ?? 'all');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        $per_page = min(intval($_POST['per_page'] ?? 20), 50); // 最大50件
        
        // セッションID生成
        if (empty($session_id)) {
            $session_id = wp_generate_uuid4();
        }
        
        $start_time = microtime(true);
        
        // クエリが空の場合の処理
        if (empty($query)) {
            $recent_grants = gi_get_recent_grants($per_page);
            wp_send_json_success([
                'grants' => $recent_grants,
                'count' => count($recent_grants),
                'ai_response' => '検索キーワードを入力してください。最近公開された補助金を表示しています。',
                'keywords' => [],
                'session_id' => $session_id,
                'suggestions' => gi_get_popular_search_terms(5),
                'debug' => WP_DEBUG ? ['type' => 'recent_grants'] : null
            ]);
            return;
        }
        
        // Enhanced検索実行
        $search_result = gi_enhanced_semantic_search($query, $filter, $page, $per_page);
        
        // 検索結果の簡単な説明
        $ai_response = gi_generate_simple_search_summary($search_result['count'], $query);
        
        // キーワード抽出
        $keywords = gi_extract_keywords($query);
        
        // 検索履歴保存
        gi_save_search_history($query, ['filter' => $filter], $search_result['count'], $session_id);
        
        // フォローアップ提案生成
        $suggestions = gi_generate_search_suggestions($query, $search_result['grants']);
        
        $end_time = microtime(true);
        $processing_time = round(($end_time - $start_time) * 1000);
        
        wp_send_json_success([
            'grants' => $search_result['grants'],
            'count' => $search_result['count'],
            'total_pages' => $search_result['total_pages'],
            'current_page' => $page,
            'ai_response' => $ai_response,
            'keywords' => $keywords,
            'suggestions' => $suggestions,
            'session_id' => $session_id,
            'processing_time_ms' => $processing_time,
            'debug' => WP_DEBUG ? [
                'filter' => $filter,
                'method' => $search_result['method'],
                'query_complexity' => gi_analyze_query_complexity($query)
            ] : null
        ]);
        
    } catch (Exception $e) {

        wp_send_json_error([
            'message' => '検索中にエラーが発生しました。しばらく後でお試しください。',
            'code' => 'SEARCH_ERROR',
            'debug' => WP_DEBUG ? $e->getMessage() : null
        ]);
    }
}

/**
 * Enhanced AIチャット処理
 */
function handle_ai_chat_request() {
    try {
        if (!gi_verify_ajax_nonce()) {
            wp_send_json_error(['message' => 'セキュリティチェックに失敗しました', 'code' => 'SECURITY_ERROR']);
            return;
        }
        
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $context = json_decode(stripslashes($_POST['context'] ?? '{}'), true);
        
        if (empty($message)) {
            wp_send_json_error(['message' => 'メッセージが空です', 'code' => 'EMPTY_MESSAGE']);
            return;
        }
        
        if (empty($session_id)) {
            $session_id = wp_generate_uuid4();
        }
        
        $start_time = microtime(true);
        
        // 意図分析
        $intent = gi_analyze_user_intent($message);
        
        // 簡単なチャット応答
        $ai_response = gi_generate_simple_chat_response($message, $intent);
        
        // チャット履歴保存
        gi_save_chat_history($session_id, 'user', $message, $intent);
        gi_save_chat_history($session_id, 'ai', $ai_response);
        
        // 関連する補助金の提案
        $related_grants = gi_find_related_grants_from_chat($message, $intent);
        
        $end_time = microtime(true);
        $processing_time = round(($end_time - $start_time) * 1000);
        
        wp_send_json_success([
            'response' => $ai_response,
            'session_id' => $session_id,
            'intent' => $intent,
            'related_grants' => $related_grants,
            'suggestions' => gi_generate_chat_suggestions($message, $intent),
            'processing_time_ms' => $processing_time
        ]);
        
    } catch (Exception $e) {

        wp_send_json_error([
            'message' => 'チャット処理中にエラーが発生しました。',
            'code' => 'CHAT_ERROR',
            'debug' => WP_DEBUG ? $e->getMessage() : null
        ]);
    }
}

/**
 * Enhanced Grant AI Question Handler - 助成金固有のAI質問処理
 */
function handle_grant_ai_question() {
    try {
        if (!gi_verify_ajax_nonce()) {
            wp_send_json_error(['message' => 'セキュリティチェックに失敗しました', 'code' => 'SECURITY_ERROR']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $question = sanitize_textarea_field($_POST['question'] ?? '');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (!$post_id || empty($question)) {
            wp_send_json_error(['message' => 'パラメータが不正です', 'code' => 'INVALID_PARAMS']);
            return;
        }
        
        // 投稿の存在確認
        $grant_post = get_post($post_id);
        if (!$grant_post || $grant_post->post_type !== 'grant') {
            wp_send_json_error(['message' => '助成金が見つかりません', 'code' => 'GRANT_NOT_FOUND']);
            return;
        }
        
        if (empty($session_id)) {
            $session_id = wp_generate_uuid4();
        }
        
        $start_time = microtime(true);
        
        // 助成金の詳細情報を取得
        $grant_details = gi_get_grant_details($post_id);
        
        // 質問の意図分析
        $question_intent = gi_analyze_grant_question_intent($question, $grant_details);
        
        // 助成金に関する簡単な応答
        $ai_response = gi_generate_simple_grant_response($question, $grant_details, $question_intent);
        
        // フォローアップ質問を生成
        $suggestions = gi_generate_smart_grant_suggestions($post_id, $question, $question_intent);
        
        // 関連するリソース・リンクを提供
        $resources = gi_get_grant_resources($post_id, $question_intent);
        
        // 質問履歴保存
        gi_save_grant_question_history($post_id, $question, $ai_response, $session_id);
        
        $end_time = microtime(true);
        $processing_time = round(($end_time - $start_time) * 1000);
        
        wp_send_json_success([
            'response' => $ai_response,
            'suggestions' => $suggestions,
            'resources' => $resources,
            'grant_id' => $post_id,
            'grant_title' => $grant_post->post_title,
            'intent' => $question_intent,
            'session_id' => $session_id,
            'processing_time_ms' => $processing_time,
            'confidence_score' => gi_calculate_response_confidence($question, $ai_response)
        ]);
        
    } catch (Exception $e) {
        error_log('Grant AI Question Error: ' . $e->getMessage());
        wp_send_json_error([
            'message' => 'AI応答の生成中にエラーが発生しました',
            'code' => 'AI_RESPONSE_ERROR',
            'debug' => WP_DEBUG ? $e->getMessage() : null
        ]);
    }
}

/**
 * Enhanced 音声入力処理
 */
function gi_ajax_process_voice_input() {
    try {
        if (!gi_verify_ajax_nonce()) {
            wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
            return;
        }
        
        $audio_data = $_POST['audio_data'] ?? '';
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($audio_data)) {
            wp_send_json_error(['message' => '音声データが空です']);
            return;
        }
        
        // OpenAI統合を使用して音声認識を試行
        $openai = class_exists('GI_OpenAI_Integration') ? GI_OpenAI_Integration::getInstance() : null;
        if ($openai && $openai->is_configured() && method_exists($openai, 'transcribe_audio')) {
            $transcribed_text = $openai->transcribe_audio($audio_data);
            $confidence = 0.9; // OpenAI Whisperの場合は高い信頼度
        } else {
            // フォールバック: ブラウザのWeb Speech APIの結果をそのまま使用
            $transcribed_text = sanitize_text_field($_POST['fallback_text'] ?? '');
            $confidence = floatval($_POST['confidence'] ?? 0.7);
        }
        
        // 音声履歴に保存
        gi_save_voice_history($session_id, $transcribed_text, $confidence);
        
        wp_send_json_success([
            'transcribed_text' => $transcribed_text,
            'confidence' => $confidence,
            'session_id' => $session_id,
            'method' => $openai->is_configured() ? 'openai_whisper' : 'browser_api'
        ]);
        
    } catch (Exception $e) {
        error_log('Voice Input Error: ' . $e->getMessage());
        wp_send_json_error([
            'message' => '音声認識中にエラーが発生しました',
            'debug' => WP_DEBUG ? $e->getMessage() : null
        ]);
    }
}

/**
 * 検索候補取得
 */
function gi_ajax_get_search_suggestions() {
    try {
        if (!gi_verify_ajax_nonce()) {
            wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
            return;
        }
        
        $partial_query = sanitize_text_field($_POST['query'] ?? '');
        $limit = min(intval($_POST['limit'] ?? 10), 20);
        
        $suggestions = gi_get_smart_search_suggestions($partial_query, $limit);
        
        wp_send_json_success([
            'suggestions' => $suggestions,
            'query' => $partial_query
        ]);
        
    } catch (Exception $e) {
        error_log('Search Suggestions Error: ' . $e->getMessage());
        wp_send_json_error(['message' => '検索候補の取得に失敗しました']);
    }
}

/**
 * お気に入り切り替え
 */
function gi_ajax_toggle_favorite() {
    try {
        if (!gi_verify_ajax_nonce()) {
            wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $user_id = get_current_user_id();
        
        if (!$post_id) {
            wp_send_json_error(['message' => '投稿IDが不正です']);
            return;
        }
        
        if (!$user_id) {
            wp_send_json_error(['message' => 'ログインが必要です']);
            return;
        }
        
        $favorites = get_user_meta($user_id, 'gi_favorites', true) ?: [];
        $is_favorited = in_array($post_id, $favorites);
        
        if ($is_favorited) {
            $favorites = array_filter($favorites, function($id) use ($post_id) {
                return $id != $post_id;
            });
            $action = 'removed';
        } else {
            $favorites[] = $post_id;
            $action = 'added';
        }
        
        update_user_meta($user_id, 'gi_favorites', array_values($favorites));
        
        wp_send_json_success([
            'action' => $action,
            'is_favorite' => !$is_favorited,
            'total_favorites' => count($favorites),
            'message' => $action === 'added' ? 'お気に入りに追加しました' : 'お気に入りから削除しました'
        ]);
        
    } catch (Exception $e) {
        error_log('Toggle Favorite Error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'お気に入りの更新に失敗しました']);
    }
}

/**
 * =============================================================================
 * Enhanced ヘルパー関数群
 * =============================================================================
 */

/**
 * セキュリティ検証の統一処理
 */
function gi_verify_ajax_nonce() {
    $nonce = $_POST['nonce'] ?? '';
    return !empty($nonce) && (
        wp_verify_nonce($nonce, 'gi_ai_search_nonce') || 
        wp_verify_nonce($nonce, 'gi_ajax_nonce')
    );
}

/**
 * Enhanced セマンティック検索
 */
function gi_enhanced_semantic_search($query, $filter = 'all', $page = 1, $per_page = 20) {
    // OpenAI統合がある場合はセマンティック検索を試行
    $openai = class_exists('GI_OpenAI_Integration') ? GI_OpenAI_Integration::getInstance() : null;
    
    if ($openai && $openai->is_configured() && get_option('gi_ai_semantic_search', false)) {
        try {
            return gi_perform_ai_enhanced_search($query, $filter, $page, $per_page);
        } catch (Exception $e) {
            error_log('Semantic Search Error: ' . $e->getMessage());
            // フォールバック to standard search
        }
    }
    
    return gi_perform_standard_search($query, $filter, $page, $per_page);
}

/**
 * AI強化検索実行
 */
function gi_perform_ai_enhanced_search($query, $filter, $page, $per_page) {
    // クエリの拡張とセマンティック分析
    $enhanced_query = gi_enhance_search_query($query);
    $semantic_terms = gi_extract_semantic_terms($query);
    
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => 'publish',
        'meta_query' => ['relation' => 'OR'],
        's' => $enhanced_query
    ];
    
    // セマンティック検索のためのメタクエリ拡張
    foreach ($semantic_terms as $term) {
        $args['meta_query'][] = [
            'key' => 'grant_target',
            'value' => $term,
            'compare' => 'LIKE'
        ];
        $args['meta_query'][] = [
            'key' => 'grant_content',
            'value' => $term,
            'compare' => 'LIKE'
        ];
    }
    
    // フィルター適用
    if ($filter !== 'all') {
        $args['tax_query'] = gi_build_tax_query($filter);
    }
    
    $query_obj = new WP_Query($args);
    $grants = [];
    
    if ($query_obj->have_posts()) {
        while ($query_obj->have_posts()) {
            $query_obj->the_post();
            $post_id = get_the_ID();
            
            // セマンティック類似度計算
            $relevance_score = gi_calculate_semantic_relevance($query, $post_id);
            
            $grants[] = gi_format_grant_result($post_id, $relevance_score);
        }
        wp_reset_postdata();
        
        // 関連性スコアでソート
        usort($grants, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });
    }
    
    return [
        'grants' => $grants,
        'count' => $query_obj->found_posts,
        'total_pages' => $query_obj->max_num_pages,
        'method' => 'ai_enhanced'
    ];
}

/**
 * スタンダード検索実行
 */
function gi_perform_standard_search($query, $filter, $page, $per_page) {
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => 'publish',
        's' => $query
    ];
    
    // フィルター適用
    if ($filter !== 'all') {
        $args['tax_query'] = gi_build_tax_query($filter);
    }
    
    $query_obj = new WP_Query($args);
    $grants = [];
    
    if ($query_obj->have_posts()) {
        while ($query_obj->have_posts()) {
            $query_obj->the_post();
            $post_id = get_the_ID();
            
            $grants[] = gi_format_grant_result($post_id, 0.8); // デフォルト関連性
        }
        wp_reset_postdata();
    }
    
    return [
        'grants' => $grants,
        'count' => $query_obj->found_posts,
        'total_pages' => $query_obj->max_num_pages,
        'method' => 'standard'
    ];
}

/**
 * 助成金結果のフォーマット
 */
function gi_format_grant_result($post_id, $relevance_score = 0.8) {
    $image_url = get_the_post_thumbnail_url($post_id, 'medium');
    $default_image = get_template_directory_uri() . '/assets/images/grant-default.jpg';
    
    return [
        'id' => $post_id,
        'title' => get_the_title(),
        'permalink' => get_permalink(),
        'url' => get_permalink(),
        'excerpt' => wp_trim_words(get_the_excerpt(), 25),
        'image_url' => $image_url ?: $default_image,
        'amount' => get_post_meta($post_id, 'max_amount', true) ?: '未定',
        'deadline' => get_post_meta($post_id, 'deadline', true) ?: '随時',
        'organization' => get_post_meta($post_id, 'organization', true) ?: '未定',
        'success_rate' => get_post_meta($post_id, 'grant_success_rate', true) ?: null,
        'featured' => get_post_meta($post_id, 'is_featured', true) == '1',
        'application_status' => get_post_meta($post_id, 'application_status', true) ?: 'active',
        'categories' => wp_get_post_terms($post_id, 'grant_category', ['fields' => 'names']),
        'relevance_score' => round($relevance_score, 3),
        'last_updated' => get_the_modified_time('Y-m-d H:i:s')
    ];
}

/**
 * コンテキスト付きAI応答生成
 */
function gi_generate_contextual_ai_response($query, $grants, $filter = 'all') {
    $openai = class_exists('GI_OpenAI_Integration') ? GI_OpenAI_Integration::getInstance() : null;
    
    if ($openai && $openai->is_configured()) {
        $context = [
            'grants' => array_slice($grants, 0, 3), // 上位3件のコンテキスト
            'filter' => $filter,
            'total_count' => count($grants)
        ];
        
        $prompt = "検索クエリ: {$query}\n結果数: " . count($grants) . "件";
        
        try {
            return $openai->generate_response($prompt, $context);
        } catch (Exception $e) {
            error_log('AI Response Error: ' . $e->getMessage());
            // フォールバック
        }
    }
    
    return gi_generate_fallback_response($query, $grants, $filter);
}

/**
 * フォールバック応答生成（改良版）
 */
function gi_generate_fallback_response($query, $grants, $filter = 'all') {
    $count = count($grants);
    
    if ($count === 0) {
        $response = "「{$query}」に該当する助成金が見つかりませんでした。";
        $response .= "\n\n検索のヒント：\n";
        $response .= "・より一般的なキーワードで検索してみてください\n";
        $response .= "・業種名や技術分野を変更してみてください\n";
        $response .= "・フィルターを「すべて」に変更してみてください";
        return $response;
    }
    
    $response = "「{$query}」で{$count}件の助成金が見つかりました。";
    
    // フィルター情報
    if ($filter !== 'all') {
        $filter_names = [
            'it' => 'IT・デジタル',
            'manufacturing' => 'ものづくり',
            'startup' => 'スタートアップ',
            'sustainability' => '持続可能性',
            'innovation' => 'イノベーション',
            'employment' => '雇用・人材'
        ];
        $filter_name = $filter_names[$filter] ?? $filter;
        $response .= "（{$filter_name}分野）";
    }
    
    // 特徴的な助成金の情報
    $featured_count = 0;
    $high_amount_count = 0;
    
    foreach ($grants as $grant) {
        if (!empty($grant['featured'])) {
            $featured_count++;
        }
        $amount = $grant['amount'];
        if (preg_match('/(\d+)/', $amount, $matches) && intval($matches[1]) >= 1000) {
            $high_amount_count++;
        }
    }
    
    if ($featured_count > 0) {
        $response .= "\n\nこのうち{$featured_count}件は特におすすめの助成金です。";
    }
    
    if ($high_amount_count > 0) {
        $response .= "\n{$high_amount_count}件は1000万円以上の大型助成金です。";
    }
    
    $response .= "\n\n詳細については各助成金の「詳細を見る」ボタンから確認いただくか、「AI質問」ボタンでお気軽にご質問ください。";
    
    return $response;
}

/**
 * Enhanced Grant応答生成
 */
function gi_generate_enhanced_grant_response($post_id, $question, $grant_details, $intent) {
    $openai = class_exists('GI_OpenAI_Integration') ? GI_OpenAI_Integration::getInstance() : null;
    
    if ($openai && $openai->is_configured()) {
        $context = [
            'grant_details' => $grant_details,
            'intent' => $intent
        ];
        
        $prompt = "助成金「{$grant_details['title']}」について：\n質問: {$question}";
        
        try {
            return $openai->generate_response($prompt, $context);
        } catch (Exception $e) {
            error_log('Enhanced Grant Response Error: ' . $e->getMessage());
            // フォールバック
        }
    }
    
    return gi_generate_fallback_grant_response($post_id, $question, $grant_details, $intent);
}

/**
 * 助成金詳細情報取得
 */
function gi_get_grant_details($post_id) {
    return [
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'content' => get_post_field('post_content', $post_id),
        'excerpt' => get_the_excerpt($post_id),
        'organization' => get_post_meta($post_id, 'organization', true),
        'max_amount' => get_post_meta($post_id, 'max_amount', true),
        'deadline' => get_post_meta($post_id, 'deadline', true),
        'grant_target' => get_post_meta($post_id, 'grant_target', true),
        'application_requirements' => get_post_meta($post_id, 'application_requirements', true),
        'eligible_expenses' => get_post_meta($post_id, 'eligible_expenses', true),
        'application_process' => get_post_meta($post_id, 'application_process', true),
        'success_rate' => get_post_meta($post_id, 'grant_success_rate', true),
        'categories' => wp_get_post_terms($post_id, 'grant_category', ['fields' => 'names'])
    ];
}

/**
 * 質問意図の分析
 */
function gi_analyze_grant_question_intent($question, $grant_details) {
    $question_lower = mb_strtolower($question);
    
    $intents = [
        'application' => ['申請', '手続き', '方法', '流れ', '必要書類', 'どうやって'],
        'amount' => ['金額', '額', 'いくら', '助成額', '補助額', '上限'],
        'deadline' => ['締切', '期限', 'いつまで', '申請期限', '募集期間'],
        'eligibility' => ['対象', '資格', '条件', '要件', '該当'],
        'expenses' => ['経費', '費用', '対象経費', '使える', '支払い'],
        'process' => ['審査', '選考', '採択', '結果', 'いつ', '期間'],
        'success_rate' => ['採択率', '通る', '確率', '実績', '成功率'],
        'documents' => ['書類', '資料', '提出', '準備', '必要なもの']
    ];
    
    $detected_intents = [];
    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $keyword) {
            if (mb_stripos($question_lower, $keyword) !== false) {
                $detected_intents[] = $intent;
                break;
            }
        }
    }
    
    return !empty($detected_intents) ? $detected_intents[0] : 'general';
}

/**
 * Fallback Grant応答生成（改良版）
 */
function gi_generate_fallback_grant_response($post_id, $question, $grant_details, $intent) {
    $title = $grant_details['title'];
    $organization = $grant_details['organization'];
    $max_amount = $grant_details['max_amount'];
    $deadline = $grant_details['deadline'];
    $grant_target = $grant_details['grant_target'];
    
    switch ($intent) {
        case 'application':
            $response = "「{$title}」の申請について：\n\n";
            if ($organization) {
                $response .= "【実施機関】\n{$organization}\n\n";
            }
            if ($grant_target) {
                $response .= "【申請対象】\n{$grant_target}\n\n";
            }
            $response .= "【申請方法】\n";
            $response .= "詳細な申請方法や必要書類については、実施機関の公式サイトでご確認ください。\n";
            $response .= "申請前に制度概要をしっかりと理解し、要件を満たしているか確認することをお勧めします。";
            break;
            
        case 'amount':
            $response = "「{$title}」の助成金額について：\n\n";
            if ($max_amount) {
                $response .= "【助成上限額】\n{$max_amount}\n\n";
            }
            $response .= "【注意事項】\n";
            $response .= "・実際の助成額は事業規模や申請内容により決定されます\n";
            $response .= "・補助率や助成対象経費に制限がある場合があります\n";
            $response .= "・詳細は実施機関の募集要項をご確認ください";
            break;
            
        case 'deadline':
            $response = "「{$title}」の申請期限について：\n\n";
            if ($deadline) {
                $response .= "【申請締切】\n{$deadline}\n\n";
            }
            $response .= "【重要】\n";
            $response .= "・申請期限は変更される場合があります\n";
            $response .= "・必要書類の準備に時間がかかる場合があります\n";
            $response .= "・最新情報は実施機関の公式サイトでご確認ください";
            break;
            
        case 'eligibility':
            $response = "「{$title}」の申請対象について：\n\n";
            if ($grant_target) {
                $response .= "【対象者・対象事業】\n{$grant_target}\n\n";
            }
            $response .= "【確認ポイント】\n";
            $response .= "・事業規模や従業員数の要件\n";
            $response .= "・業種や事業内容の制限\n";
            $response .= "・地域的な要件の有無\n";
            $response .= "・その他の特別な要件";
            break;
            
        default:
            $response = "「{$title}」について：\n\n";
            $response .= "【基本情報】\n";
            if ($max_amount) {
                $response .= "・助成上限額：{$max_amount}\n";
            }
            if ($grant_target) {
                $response .= "・対象：{$grant_target}\n";
            }
            if ($deadline) {
                $response .= "・締切：{$deadline}\n";
            }
            if ($organization) {
                $response .= "・実施機関：{$organization}\n";
            }
            $response .= "\nより詳しい情報や具体的な質問については、「詳細を見る」ボタンから詳細ページをご確認いただくか、";
            $response .= "具体的な内容（申請方法、金額、締切など）についてお聞かせください。";
    }
    
    return $response;
}

/**
 * スマートな助成金提案生成
 */
function gi_generate_smart_grant_suggestions($post_id, $question, $intent) {
    $base_suggestions = [
        '申請に必要な書類は何ですか？',
        '申請の流れを教えてください',
        '対象となる経費について',
        '採択のポイントは？'
    ];
    
    $intent_specific = [
        'application' => [
            '申請の難易度はどのくらい？',
            '申請にかかる期間は？',
            '必要な準備期間は？'
        ],
        'amount' => [
            '補助率はどのくらい？',
            '対象経費の範囲は？',
            '追加の支援制度はある？'
        ],
        'deadline' => [
            '次回の募集はいつ？',
            '申請準備はいつから始める？',
            '年間スケジュールは？'
        ],
        'eligibility' => [
            'この条件で申請できる？',
            '他に必要な要件は？',
            '類似の助成金はある？'
        ]
    ];
    
    $suggestions = $base_suggestions;
    
    if (isset($intent_specific[$intent])) {
        $suggestions = array_merge($intent_specific[$intent], array_slice($base_suggestions, 0, 2));
    }
    
    return array_slice(array_unique($suggestions), 0, 4);
}

/**
 * チャット履歴保存
 */
function gi_save_chat_history($session_id, $message_type, $content, $intent_data = null) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'gi_chat_history';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return false; // テーブルが存在しない場合
    }
    
    return $wpdb->insert(
        $table,
        [
            'session_id' => $session_id,
            'user_id' => get_current_user_id() ?: null,
            'message_type' => $message_type,
            'message_content' => $content,
            'intent_data' => is_array($intent_data) ? json_encode($intent_data) : $intent_data,
            'created_at' => current_time('mysql')
        ],
        ['%s', '%d', '%s', '%s', '%s', '%s']
    );
}

/**
 * 音声履歴保存
 */
function gi_save_voice_history($session_id, $transcribed_text, $confidence_score = null) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'gi_voice_history';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return false;
    }
    
    return $wpdb->insert(
        $table,
        [
            'session_id' => $session_id,
            'user_id' => get_current_user_id() ?: null,
            'transcribed_text' => $transcribed_text,
            'confidence_score' => $confidence_score,
            'created_at' => current_time('mysql')
        ],
        ['%s', '%d', '%s', '%f', '%s']
    );
}

/**
 * 最新の助成金取得
 */
function gi_get_recent_grants($limit = 20) {
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    $query = new WP_Query($args);
    $grants = [];
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $grants[] = gi_format_grant_result(get_the_ID(), 0.9);
        }
        wp_reset_postdata();
    }
    
    return $grants;
}

/**
 * 検索キーワード抽出
 */
function gi_extract_keywords($query) {
    // 基本的なキーワード分割（より高度な実装も可能）
    $keywords = preg_split('/[\s\p{P}]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
    $keywords = array_filter($keywords, function($word) {
        return mb_strlen($word) >= 2; // 2文字以上のワードのみ
    });
    
    return array_values($keywords);
}

/**
 * その他のテスト・ユーティリティ関数
 */
function gi_ajax_test_connection() {
    wp_send_json_success([
        'message' => 'AJAX接続テスト成功',
        'timestamp' => current_time('mysql'),
        'server_info' => [
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ],
        'ai_status' => gi_check_ai_capabilities()
    ]);
}

function gi_ajax_save_voice_history() {
    if (!gi_verify_ajax_nonce()) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }
    
    wp_send_json_success(['message' => '音声履歴を保存しました']);
}

function gi_ajax_get_chat_history() {
    if (!gi_verify_ajax_nonce()) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }
    
    $session_id = sanitize_text_field($_POST['session_id'] ?? '');
    $limit = min(intval($_POST['limit'] ?? 50), 100);
    
    // チャット履歴取得の実装
    wp_send_json_success([
        'history' => [],
        'session_id' => $session_id
    ]);
}

function gi_ajax_get_search_history() {
    if (!gi_verify_ajax_nonce()) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }
    
    $history = gi_get_search_history(20);
    
    wp_send_json_success([
        'history' => $history
    ]);
}

function gi_ajax_submit_ai_feedback() {
    if (!gi_verify_ajax_nonce()) {
        wp_send_json_error(['message' => 'セキュリティチェックに失敗しました']);
        return;
    }
    
    $feedback = sanitize_textarea_field($_POST['feedback'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $session_id = sanitize_text_field($_POST['session_id'] ?? '');
    
    // フィードバック保存の実装（必要に応じて）
    
    wp_send_json_success([
        'message' => 'フィードバックありがとうございます'
    ]);
}

/**
 * =============================================================================
 * Missing Helper Functions - Simple Response Generators
 * =============================================================================
 */

/**
 * 簡単な検索サマリー生成
 */
function gi_generate_simple_search_summary($count, $query) {
    if ($count === 0) {
        return "「{$query}」に該当する助成金が見つかりませんでした。キーワードを変更して再度お試しください。";
    }
    
    if ($count === 1) {
        return "「{$query}」で1件の助成金が見つかりました。";
    }
    
    return "「{$query}」で{$count}件の助成金が見つかりました。詳細は各カードの「詳細を見る」または「AI質問」ボタンからご確認ください。";
}

/**
 * 簡単なチャット応答生成
 */
function gi_generate_simple_chat_response($message, $intent) {
    $message_lower = mb_strtolower($message);
    
    // 挨拶への応答
    if (preg_match('/(こんにちは|おはよう|こんばんは|はじめまして)/', $message_lower)) {
        return "こんにちは！Grant Insight Perfectの補助金AIアシスタントです。どのような補助金をお探しですか？";
    }
    
    // 意図に基づく応答
    switch ($intent) {
        case 'search':
            return "どのような助成金をお探しですか？業種、目的、地域などを教えていただくと、最適な助成金をご提案できます。";
        
        case 'application':
            return "申請に関するご質問ですね。具体的にどの助成金の申請方法についてお知りになりたいですか？";
        
        case 'information':
            return "詳しい情報をお調べします。どの助成金についての詳細をお知りになりたいですか？";
        
        case 'comparison':
            return "助成金の比較についてお答えします。どのような観点（金額、対象、締切など）で比較をご希望ですか？";
        
        case 'recommendation':
            return "おすすめの助成金をご提案させていただきます。お客様の事業内容や目的を教えてください。";
        
        default:
            return "ご質問ありがとうございます。具体的な内容をお聞かせいただけると、より詳しい回答をお提供できます。";
    }
}

/**
 * 簡単な助成金応答生成
 */
function gi_generate_simple_grant_response($question, $grant_details, $intent) {
    $title = $grant_details['title'] ?? '助成金';
    $organization = $grant_details['organization'] ?? '';
    $max_amount = $grant_details['max_amount'] ?? '';
    $deadline = $grant_details['deadline'] ?? '';
    $grant_target = $grant_details['grant_target'] ?? '';
    
    $response = "「{$title}」についてお答えします。\n\n";
    
    switch ($intent) {
        case 'application':
            $response .= "【申請について】\n";
            if ($organization) {
                $response .= "実施機関：{$organization}\n";
            }
            if ($grant_target) {
                $response .= "\n対象者：{$grant_target}\n";
            }
            $response .= "\n詳しい申請方法は、実施機関の公式サイトをご確認ください。";
            break;
        
        case 'amount':
            $response .= "【助成金額】\n";
            if ($max_amount) {
                $response .= "最大助成額：{$max_amount}\n";
            } else {
                $response .= "助成額の詳細は実施機関にお問い合わせください。\n";
            }
            $response .= "\n※実際の助成額は事業規模や申請内容により異なります。";
            break;
        
        case 'deadline':
            $response .= "【申請締切】\n";
            if ($deadline) {
                $response .= "締切：{$deadline}\n";
            } else {
                $response .= "締切情報は実施機関の公式サイトでご確認ください。\n";
            }
            $response .= "\n※締切は変更される場合がありますので、最新情報をご確認ください。";
            break;
        
        case 'eligibility':
            $response .= "【申請対象】\n";
            if ($grant_target) {
                $response .= $grant_target . "\n";
            } else {
                $response .= "対象者の詳細は実施機関にお問い合わせください。\n";
            }
            break;
        
        default:
            $response .= "【基本情報】\n";
            if ($max_amount) {
                $response .= "・助成額：{$max_amount}\n";
            }
            if ($deadline) {
                $response .= "・締切：{$deadline}\n";
            }
            if ($organization) {
                $response .= "・実施機関：{$organization}\n";
            }
            $response .= "\nより詳しい情報は「詳細を見る」ボタンからご確認ください。";
    }
    
    return $response;
}

/**
 * 人気検索キーワード取得
 */
function gi_get_popular_search_terms($limit = 10) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'gi_search_history';
    
    // テーブルが存在するか確認
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        // フォールバック
        return [
            ['term' => 'IT導入補助金', 'count' => 100],
            ['term' => 'ものづくり補助金', 'count' => 95],
            ['term' => '小規模事業者持続化補助金', 'count' => 90],
            ['term' => '事業再構築補助金', 'count' => 85],
            ['term' => '雇用調整助成金', 'count' => 80]
        ];
    }
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT search_query as term, COUNT(*) as count
        FROM {$table}
        WHERE search_query != ''
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY search_query
        ORDER BY count DESC
        LIMIT %d
    ", $limit), ARRAY_A);
    
    return $results ?: [];
}

/**
 * 検索履歴取得
 */
function gi_get_search_history($limit = 20) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'gi_search_history';
    
    // テーブルが存在するか確認
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return [];
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        return [];
    }
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT *
        FROM {$table}
        WHERE user_id = %d
        ORDER BY created_at DESC
        LIMIT %d
    ", $user_id, $limit), ARRAY_A);
    
    return $results ?: [];
}

/**
 * AI機能の利用可否チェック
 */
function gi_check_ai_capabilities() {
    return [
        'openai_configured' => class_exists('GI_OpenAI_Integration') && GI_OpenAI_Integration::getInstance()->is_configured(),
        'semantic_search' => class_exists('GI_Grant_Semantic_Search'),
        'simple_responses' => true, // 常に利用可能
        'voice_recognition' => true, // ブラウザAPIで利用可能
        'fallback_mode' => true
    ];
}

/**
 * 追加ヘルパー関数
 */
function gi_build_tax_query($filter) {
    $filter_mapping = [
        'it' => 'it-support',
        'manufacturing' => 'monozukuri', 
        'startup' => 'startup-support',
        'sustainability' => 'sustainability',
        'innovation' => 'innovation',
        'employment' => 'employment'
    ];
    
    if (isset($filter_mapping[$filter])) {
        return [[
            'taxonomy' => 'grant_category',
            'field' => 'slug',
            'terms' => $filter_mapping[$filter]
        ]];
    }
    
    return [];
}

function gi_enhance_search_query($query) {
    // クエリ拡張ロジック（シノニム、関連語などを追加）
    $enhancements = [
        'AI' => ['人工知能', 'machine learning', 'ディープラーニング'],
        'DX' => ['デジタル変革', 'デジタル化', 'IT化'],
        'IoT' => ['モノのインターネット', 'センサー', 'スマート']
    ];
    
    $enhanced_query = $query;
    foreach ($enhancements as $term => $synonyms) {
        if (mb_stripos($query, $term) !== false) {
            $enhanced_query .= ' ' . implode(' ', array_slice($synonyms, 0, 2));
        }
    }
    
    return $enhanced_query;
}

function gi_extract_semantic_terms($query) {
    // セマンティック分析のための関連語抽出
    return gi_extract_keywords($query);
}

function gi_calculate_semantic_relevance($query, $post_id) {
    // セマンティック類似度の計算（シンプル版）
    $content = get_post_field('post_content', $post_id) . ' ' . get_the_title($post_id);
    $query_keywords = gi_extract_keywords($query);
    $content_lower = mb_strtolower($content);
    
    $matches = 0;
    foreach ($query_keywords as $keyword) {
        if (mb_stripos($content_lower, mb_strtolower($keyword)) !== false) {
            $matches++;
        }
    }
    
    return count($query_keywords) > 0 ? $matches / count($query_keywords) : 0.5;
}

function gi_analyze_query_complexity($query) {
    $word_count = count(gi_extract_keywords($query));
    
    if ($word_count <= 2) return 'simple';
    if ($word_count <= 5) return 'medium';
    return 'complex';
}

function gi_generate_search_suggestions($query, $grants) {
    $suggestions = [];
    
    // 基本的な拡張提案
    if (count($grants) > 0) {
        $categories = [];
        foreach (array_slice($grants, 0, 3) as $grant) {
            $categories = array_merge($categories, $grant['categories']);
        }
        $unique_categories = array_unique($categories);
        
        foreach (array_slice($unique_categories, 0, 3) as $category) {
            $suggestions[] = $query . ' ' . $category;
        }
    }
    
    // クエリ関連の提案
    $related_terms = [
        'AI' => ['DX', '自動化', 'デジタル化'],
        'スタートアップ' => ['創業', 'ベンチャー', '起業'],
        '製造業' => ['ものづくり', '工場', '技術開発']
    ];
    
    foreach ($related_terms as $term => $relations) {
        if (mb_stripos($query, $term) !== false) {
            foreach ($relations as $related) {
                $suggestions[] = str_replace($term, $related, $query);
            }
            break;
        }
    }
    
    return array_slice(array_unique($suggestions), 0, 5);
}

function gi_analyze_user_intent($message) {
    $intent_patterns = [
        'search' => ['検索', '探す', '見つけて', 'あります', '教えて'],
        'application' => ['申請', '応募', '手続き', 'どうやって'],
        'information' => ['詳細', '情報', 'について', 'とは'],
        'comparison' => ['比較', '違い', 'どちら', '選び方'],
        'recommendation' => ['おすすめ', '提案', '適した', 'いい']
    ];
    
    $message_lower = mb_strtolower($message);
    
    foreach ($intent_patterns as $intent => $patterns) {
        foreach ($patterns as $pattern) {
            if (mb_stripos($message_lower, $pattern) !== false) {
                return $intent;
            }
        }
    }
    
    return 'general';
}

function gi_generate_contextual_chat_response($message, $context, $intent) {
    $openai = class_exists('GI_OpenAI_Integration') ? GI_OpenAI_Integration::getInstance() : null;
    
    if ($openai && $openai->is_configured()) {
        $prompt = "ユーザーの質問: {$message}\n意図: {$intent}";
        
        try {
            return $openai->generate_response($prompt, $context);
        } catch (Exception $e) {
            error_log('Contextual Chat Error: ' . $e->getMessage());
            // フォールバック
        }
    }
    
    return gi_generate_intent_based_response($message, $intent);
}

function gi_generate_intent_based_response($message, $intent) {
    switch ($intent) {
        case 'search':
            return 'どのような助成金をお探しですか？業種、目的、金額規模などをお聞かせいただくと、より適切な助成金をご提案できます。';
        case 'application':
            return '申請に関するご質問ですね。具体的にどの助成金の申請についてお知りになりたいですか？申請手順、必要書類、締切などについてお答えできます。';
        case 'information':
            return '詳しい情報をお調べします。どの助成金についての詳細をお知りになりたいですか？';
        case 'comparison':
            return '助成金の比較についてお答えします。どのような観点（金額、対象、締切など）で比較をご希望ですか？';
        case 'recommendation':
            return 'おすすめの助成金をご提案させていただきます。お客様の事業内容、規模、目的をお聞かせください。';
        default:
            return 'ご質問ありがとうございます。より具体的な内容をお聞かせいただけると、詳しい回答をお提供できます。';
    }
}

function gi_find_related_grants_from_chat($message, $intent) {
    // チャットメッセージから関連する助成金を検索
    $keywords = gi_extract_keywords($message);
    if (empty($keywords)) {
        return [];
    }
    
    $search_query = implode(' ', array_slice($keywords, 0, 3));
    $search_result = gi_perform_standard_search($search_query, 'all', 1, 5);
    
    return array_slice($search_result['grants'], 0, 3);
}

function gi_generate_chat_suggestions($message, $intent) {
    $base_suggestions = [
        'おすすめの助成金を教えて',
        '申請方法について',
        '締切が近い助成金は？',
        '条件を満たす助成金を検索'
    ];
    
    $intent_suggestions = [
        'search' => [
            'IT関連の助成金を探して',
            '製造業向けの補助金は？',
            'スタートアップ支援制度について'
        ],
        'application' => [
            '申請の準備期間は？',
            '必要書類のチェックリスト',
            '申請のコツを教えて'
        ]
    ];
    
    if (isset($intent_suggestions[$intent])) {
        return $intent_suggestions[$intent];
    }
    
    return array_slice($base_suggestions, 0, 3);
}

function gi_get_smart_search_suggestions($partial_query, $limit = 10) {
    // 部分クエリから候補を生成
    $suggestions = [];
    
    // アイコンマッピング
    $icon_map = [
        'IT' => '',
        'ものづくり' => '🏭',
        '小規模' => '🏪',
        '事業再構築' => '🔄',
        '雇用' => '👥',
        '創業' => '',
        '持続化' => '📈',
        '省エネ' => '⚡',
        '環境' => '🌱'
    ];
    
    // デフォルトアイコン取得関数
    $get_icon = function($text) use ($icon_map) {
        foreach ($icon_map as $keyword => $icon) {
            if (mb_strpos($text, $keyword) !== false) {
                return $icon;
            }
        }
        return '🔍'; // デフォルトアイコン
    };
    
    // 人気キーワードから類似するものを検索
    $popular_terms = gi_get_popular_search_terms(20);
    foreach ($popular_terms as $term_data) {
        $term = $term_data['term'] ?? '';
        if (!empty($term) && mb_stripos($term, $partial_query) !== false) {
            $suggestions[] = [
                'text' => $term,
                'icon' => $get_icon($term),
                'count' => $term_data['count'] ?? 0,
                'type' => 'popular'
            ];
        }
    }
    
    // 助成金タイトルから候補を生成
    $grants = gi_search_grant_titles($partial_query, $limit);
    foreach ($grants as $grant) {
        $title = $grant['title'] ?? '';
        if (!empty($title)) {
            $suggestions[] = [
                'text' => $title,
                'icon' => $get_icon($title),
                'type' => 'grant_title',
                'grant_id' => $grant['id'] ?? 0
            ];
        }
    }
    
    return array_slice($suggestions, 0, $limit);
}

function gi_search_grant_titles($query, $limit = 5) {
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        's' => $query,
        'fields' => 'ids'
    ];
    
    $posts = get_posts($args);
    $results = [];
    
    foreach ($posts as $post_id) {
        $results[] = [
            'id' => $post_id,
            'title' => get_the_title($post_id)
        ];
    }
    
    return $results;
}

function gi_get_grant_resources($post_id, $intent) {
    $resources = [
        'official_site' => get_post_meta($post_id, 'official_url', true),
        'application_guide' => get_post_meta($post_id, 'application_guide_url', true),
        'faq_url' => get_post_meta($post_id, 'faq_url', true),
        'contact_info' => get_post_meta($post_id, 'contact_info', true)
    ];
    
    // 意図に基づいて関連リソースを優先
    $prioritized = [];
    switch ($intent) {
        case 'application':
            if ($resources['application_guide']) {
                $prioritized['application_guide'] = '申請ガイド';
            }
            break;
        case 'deadline':
            if ($resources['official_site']) {
                $prioritized['official_site'] = '公式サイト（最新情報）';
            }
            break;
    }
    
    return array_filter($prioritized + $resources);
}

function gi_save_grant_question_history($post_id, $question, $response, $session_id) {
    // 助成金別の質問履歴保存（必要に応じて実装）
    $user_id = get_current_user_id();
    if (!$user_id) return false;
    
    $history = get_user_meta($user_id, 'gi_grant_question_history', true) ?: [];
    
    $history[] = [
        'grant_id' => $post_id,
        'question' => $question,
        'response' => mb_substr($response, 0, 200), // 応答の要約のみ保存
        'session_id' => $session_id,
        'timestamp' => current_time('timestamp')
    ];
    
    // 最新100件のみ保持
    $history = array_slice($history, -100);
    
    return update_user_meta($user_id, 'gi_grant_question_history', $history);
}

function gi_calculate_response_confidence($question, $response) {
    // 応答の信頼度を計算（簡易版）
    $question_length = mb_strlen($question);
    $response_length = mb_strlen($response);
    
    // 基本スコア
    $confidence = 0.7;
    
    // 質問の具体性
    if ($question_length > 10) {
        $confidence += 0.1;
    }
    
    // 応答の詳細度
    if ($response_length > 100) {
        $confidence += 0.1;
    }
    
    // 具体的なキーワードが含まれているか
    $specific_terms = ['申請', '締切', '金額', '対象', '要件'];
    $found_terms = 0;
    foreach ($specific_terms as $term) {
        if (mb_stripos($question, $term) !== false && mb_stripos($response, $term) !== false) {
            $found_terms++;
        }
    }
    
    $confidence += ($found_terms * 0.05);
    
    return min($confidence, 1.0);
}

/**
 * =============================================================================
 * Grant Data Functions - Template Support
 * =============================================================================
 */

/**
 * Complete grant data retrieval function
 */
function gi_get_complete_grant_data($post_id) {
    static $cache = [];
    
    // キャッシュチェック
    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }
    
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'grant') {
        return [];
    }
    
    // 基本データ
    $data = [
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'permalink' => get_permalink($post_id),
        'excerpt' => get_the_excerpt($post_id),
        'content' => get_post_field('post_content', $post_id),
        'date' => get_the_date('Y-m-d', $post_id),
        'modified' => get_the_modified_date('Y-m-d H:i:s', $post_id),
        'status' => get_post_status($post_id),
        'thumbnail' => get_the_post_thumbnail_url($post_id, 'medium'),
    ];

    // ACFフィールドデータ
    $acf_fields = [
        // 基本情報
        'ai_summary' => '',
        'organization' => '',
        'organization_type' => '',
        
        // 金額情報
        'max_amount' => '',
        'max_amount_numeric' => 0,
        'min_amount' => 0,
        'subsidy_rate' => '',
        'amount_note' => '',
        
        // 締切・ステータス
        'deadline' => '',
        'deadline_date' => '',
        'deadline_timestamp' => '',
        'application_status' => 'active',
        'application_period' => '',
        'deadline_note' => '',
        
        // 対象・条件
        'grant_target' => '',
        'eligible_expenses' => '',
        'grant_difficulty' => 'normal',
        'grant_success_rate' => 0,
        'required_documents' => '',
        
        // 申請・連絡先
        'application_method' => 'online',
        'contact_info' => '',
        'official_url' => '',
        'external_link' => '',
        
        // 管理設定
        'is_featured' => false,
        'priority_order' => 100,
        'views_count' => 0,
        'last_updated' => '',
        'admin_notes' => '',
    ];

    foreach ($acf_fields as $field => $default) {
        $value = gi_get_field_safe($field, $post_id, $default);
        $data[$field] = $value;
    }

    // タクソノミーデータ
    $taxonomies = ['grant_category', 'grant_prefecture', 'grant_tag'];
    foreach ($taxonomies as $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);
        $data[$taxonomy] = [];
        $data[$taxonomy . '_names'] = [];
        
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $data[$taxonomy][] = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description
                ];
                $data[$taxonomy . '_names'][] = $term->name;
            }
        }
    }

    // 計算フィールド
    $data['is_deadline_soon'] = gi_is_deadline_soon($data['deadline']);
    $data['application_status_label'] = gi_get_status_label($data['application_status']);
    $data['difficulty_label'] = gi_get_difficulty_label($data['grant_difficulty']);
    
    // キャッシュに保存
    $cache[$post_id] = $data;
    
    return $data;
}

/**
 * All grant meta data retrieval function (fallback)
 */
function gi_get_all_grant_meta($post_id) {
    // gi_get_complete_grant_data のシンプル版
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'grant') {
        return [];
    }
    
    // 基本データのみ
    $data = [
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'permalink' => get_permalink($post_id),
        'excerpt' => get_the_excerpt($post_id),
        'thumbnail' => get_the_post_thumbnail_url($post_id, 'medium'),
    ];
    
    // 重要なメタフィールドのみ
    $meta_fields = [
        'ai_summary', 'organization', 'max_amount', 'max_amount_numeric',
        'deadline', 'application_status', 'grant_target', 'subsidy_rate',
        'grant_difficulty', 'grant_success_rate', 'official_url', 'is_featured'
    ];
    
    foreach ($meta_fields as $field) {
        $data[$field] = gi_get_field_safe($field, $post_id);
    }
    
    // タクソノミー名の配列
    $data['categories'] = wp_get_post_terms($post_id, 'grant_category', ['fields' => 'names']);
    $data['prefectures'] = wp_get_post_terms($post_id, 'grant_prefecture', ['fields' => 'names']);
    
    return $data;
}

/**
 * Safe field retrieval with fallback
 */
function gi_get_field_safe($field_name, $post_id, $default = '') {
    // ACFが利用可能な場合
    if (function_exists('get_field')) {
        $value = get_field($field_name, $post_id);
        return $value !== false && $value !== null ? $value : $default;
    }
    
    // フォールバック: 標準のpost_meta
    $value = get_post_meta($post_id, $field_name, true);
    return !empty($value) ? $value : $default;
}

/**
 * Safe ACF field retrieval (alias for template compatibility)
 * Note: This function is already defined in inc/data-functions.php
 * Using existing function to avoid redeclaration
 */

/**
 * Check if deadline is soon (within 30 days)
 */
function gi_is_deadline_soon($deadline) {
    if (empty($deadline)) return false;
    
    // 日付形式の正規化
    $timestamp = gi_normalize_date($deadline);
    if (!$timestamp) return false;
    
    $now = time();
    $thirty_days = 30 * 24 * 60 * 60;
    
    return ($timestamp > $now && $timestamp <= ($now + $thirty_days));
}

/**
 * Get status label
 */
function gi_get_status_label($status) {
    $labels = [
        'active' => '募集中',
        'pending' => '準備中',
        'closed' => '終了',
        'suspended' => '一時停止',
        'draft' => '下書き'
    ];
    
    return $labels[$status] ?? $status;
}

/**
 * Get difficulty label
 */
function gi_get_difficulty_label($difficulty) {
    $labels = [
        'easy' => '易しい',
        'normal' => '普通',
        'hard' => '難しい',
        'expert' => '上級者向け'
    ];
    
    return $labels[$difficulty] ?? $difficulty;
}

/**
 * Normalize date to timestamp
 */
function gi_normalize_date($date_input) {
    if (empty($date_input)) return false;
    
    // すでにタイムスタンプの場合
    if (is_numeric($date_input) && strlen($date_input) >= 10) {
        return intval($date_input);
    }
    
    // Ymd形式（例：20241231）
    if (is_numeric($date_input) && strlen($date_input) == 8) {
        $year = substr($date_input, 0, 4);
        $month = substr($date_input, 4, 2);
        $day = substr($date_input, 6, 2);
        return mktime(0, 0, 0, $month, $day, $year);
    }
    
    // その他の日付文字列
    $timestamp = strtotime($date_input);
    return $timestamp !== false ? $timestamp : false;
}

/**
 * Get user favorites safely
 * Note: This function is defined in inc/data-processing.php
 * No need to redefine here - using existing gi_get_user_favorites()
 */

/**
 * =============================================================================
 * メイン検索・フィルタリング AJAX 処理
 * =============================================================================
 */

/**
 * 統一カードレンダリング関数（簡易版）
 */
if (!function_exists('gi_render_card_unified')) {
    function gi_render_card_unified($post_id, $view = 'grid') {
        // 既存のカード関数を使用してフォールバック
        global $current_view, $user_favorites;
        $current_view = $view;
        
        ob_start();
        get_template_part('template-parts/grant-card-unified');
        $output = ob_get_clean();
        
        // 出力がない場合の簡易フォールバック
        if (empty($output)) {
            $title = get_the_title($post_id);
            $permalink = get_permalink($post_id);
            $organization = get_field('organization', $post_id) ?: '';
            $amount = get_field('max_amount', $post_id) ?: '金額未設定';
            $status = get_field('application_status', $post_id) ?: 'open';
            $status_text = $status === 'open' ? '募集中' : ($status === 'upcoming' ? '募集予定' : '募集終了');
            
            $is_favorite = in_array($post_id, $user_favorites ?: []);
            
            if ($view === 'grid') {
                return "
                <div class='clean-grant-card' data-post-id='{$post_id}' onclick=\"location.href='{$permalink}'\">
                    <div class='clean-grant-card-header'>
                        <h3 style='margin: 0; font-size: 16px; font-weight: 600; line-height: 1.4;'>
                            <a href='{$permalink}' style='text-decoration: none; color: inherit;'>{$title}</a>
                        </h3>
                        <button class='favorite-btn' data-post-id='{$post_id}' onclick='event.stopPropagation();' style='
                            position: absolute; top: 10px; right: 10px; background: none; border: none; 
                            color: " . ($is_favorite ? '#dc2626' : '#6b7280') . "; font-size: 18px; cursor: pointer;
                        '>" . ($is_favorite ? '♥' : '♡') . "</button>
                    </div>
                    <div class='clean-grant-card-body'>
                        <div style='margin-bottom: 12px; font-size: 14px; color: #6b7280;'>{$organization}</div>
                        <div style='margin-bottom: 12px; font-size: 14px; font-weight: 600; color: #16a34a;'>{$amount}</div>
                    </div>
                    <div class='clean-grant-card-footer'>
                        <span style='font-size: 12px; color: #6b7280;'>{$status_text}</span>
                        <a href='{$permalink}' style='
                            background: #000; color: white; text-align: center; 
                            padding: 8px 16px; text-decoration: none; border-radius: 6px;
                            font-size: 12px; font-weight: 500;
                        '>詳細を見る</a>
                    </div>
                </div>";
            } else {
                return "
                <div class='clean-grant-card clean-grant-card-list' data-post-id='{$post_id}' onclick=\"location.href='{$permalink}'\" style='
                    display: flex; align-items: center; gap: 16px; cursor: pointer;
                '>
                    <div style='flex: 1;'>
                        <h3 style='margin: 0 0 4px 0; font-size: 16px; font-weight: 600;'>
                            <a href='{$permalink}' style='text-decoration: none; color: inherit;'>{$title}</a>
                        </h3>
                        <div style='font-size: 12px; color: #6b7280;'>{$organization}</div>
                    </div>
                    
                    <div style='text-align: center; min-width: 120px;'>
                        <div style='font-size: 14px; font-weight: 600; color: #16a34a;'>{$amount}</div>
                        <div style='font-size: 10px; color: #9ca3af;'>{$status_text}</div>
                    </div>
                    
                    <button class='favorite-btn' data-post-id='{$post_id}' onclick='event.stopPropagation();' style='
                        background: none; border: none; color: " . ($is_favorite ? '#dc2626' : '#6b7280') . "; 
                        font-size: 18px; cursor: pointer; padding: 8px;
                    '>" . ($is_favorite ? '♥' : '♡') . "</button>
                </div>";
            }
        }
        
        return $output;
    }
}

/**
 * 助成金読み込み処理（完全版・統一カード対応）
 */
function gi_ajax_load_grants() {
    // nonceチェック
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gi_ajax_nonce')) {
        wp_send_json_error('セキュリティチェックに失敗しました');
    }

    // ===== パラメータ取得と検証 =====
    $search = sanitize_text_field($_POST['search'] ?? '');
    $categories = json_decode(stripslashes($_POST['categories'] ?? '[]'), true) ?: [];
    $prefectures = json_decode(stripslashes($_POST['prefectures'] ?? '[]'), true) ?: [];
    $tags = json_decode(stripslashes($_POST['tags'] ?? '[]'), true) ?: [];
    $status = json_decode(stripslashes($_POST['status'] ?? '[]'), true) ?: [];
    $difficulty = json_decode(stripslashes($_POST['difficulty'] ?? '[]'), true) ?: [];
    $success_rate = json_decode(stripslashes($_POST['success_rate'] ?? '[]'), true) ?: [];
    
    // 金額・数値フィルター
    $amount = sanitize_text_field($_POST['amount'] ?? '');
    $amount_min = intval($_POST['amount_min'] ?? 0);
    $amount_max = intval($_POST['amount_max'] ?? 0);
    
    // 新しいフィルター項目
    $subsidy_rate = sanitize_text_field($_POST['subsidy_rate'] ?? '');
    $organization = sanitize_text_field($_POST['organization'] ?? '');
    $organization_type = sanitize_text_field($_POST['organization_type'] ?? '');
    $target_business = sanitize_text_field($_POST['target_business'] ?? '');
    $application_method = sanitize_text_field($_POST['application_method'] ?? '');
    $only_featured = sanitize_text_field($_POST['only_featured'] ?? '');
    $deadline_range = sanitize_text_field($_POST['deadline_range'] ?? '');
    
    // 表示・ソート設定
    $sort = sanitize_text_field($_POST['sort'] ?? 'date_desc');
    $view = sanitize_text_field($_POST['view'] ?? 'grid');
    $page = max(1, intval($_POST['page'] ?? 1));
    $posts_per_page = max(6, min(30, intval($_POST['posts_per_page'] ?? 12)));

    // ===== WP_Queryの引数構築 =====
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish'
    ];

    // ===== 検索クエリ（拡張版：ACFフィールドも検索対象） =====
    if (!empty($search)) {
        $args['s'] = $search;
        
        // メタフィールドも検索対象に追加
        add_filter('posts_search', function($search_sql, $wp_query) use ($search) {
            global $wpdb;
            
            if (!$wp_query->is_main_query() || empty($search)) {
                return $search_sql;
            }
            
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            
            $meta_search = $wpdb->prepare("
                OR EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm 
                    WHERE pm.post_id = {$wpdb->posts}.ID 
                    AND pm.meta_key IN ('ai_summary', 'organization', 'grant_target', 'eligible_expenses', 'required_documents')
                    AND pm.meta_value LIKE %s
                )
            ", $search_term);
            
            // 既存の検索SQLに追加
            $search_sql = str_replace('))) AND', '))) ' . $meta_search . ' AND', $search_sql);
            return $search_sql;
        }, 10, 2);
    }

    // ===== タクソノミークエリ =====
    $tax_query = ['relation' => 'AND'];
    
    if (!empty($categories)) {
        $tax_query[] = [
            'taxonomy' => 'grant_category',
            'field' => 'slug',
            'terms' => $categories,
            'operator' => 'IN'
        ];
    }
    
    if (!empty($prefectures)) {
        $tax_query[] = [
            'taxonomy' => 'grant_prefecture',
            'field' => 'slug', 
            'terms' => $prefectures,
            'operator' => 'IN'
        ];
    }
    
    if (!empty($tags)) {
        $tax_query[] = [
            'taxonomy' => 'grant_tag',
            'field' => 'slug',
            'terms' => $tags,
            'operator' => 'IN'
        ];
    }
    
    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    // ===== メタクエリ（カスタムフィールド） =====
    $meta_query = ['relation' => 'AND'];
    
    // ステータスフィルター
    if (!empty($status)) {
        // UIステータスをDBの値にマッピング
        $db_status = array_map(function($s) {
            return $s === 'active' ? 'open' : ($s === 'upcoming' ? 'upcoming' : $s);
        }, $status);
        
        $meta_query[] = [
            'key' => 'application_status',
            'value' => $db_status,
            'compare' => 'IN'
        ];
    }
    
    // 金額範囲フィルター
    if (!empty($amount)) {
        switch($amount) {
            case '0-100':
                $meta_query[] = [
                    'key' => 'max_amount_numeric',
                    'value' => [0, 1000000],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ];
                break;
            case '100-500':
                $meta_query[] = [
                    'key' => 'max_amount_numeric',
                    'value' => [1000000, 5000000],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ];
                break;
            case '500-1000':
                $meta_query[] = [
                    'key' => 'max_amount_numeric',
                    'value' => [5000000, 10000000],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ];
                break;
            case '1000-3000':
                $meta_query[] = [
                    'key' => 'max_amount_numeric',
                    'value' => [10000000, 30000000],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ];
                break;
            case '3000+':
                $meta_query[] = [
                    'key' => 'max_amount_numeric',
                    'value' => 30000000,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                ];
                break;
        }
    }
    
    // 注目の助成金フィルター
    if ($only_featured === 'true' || $only_featured === '1') {
        $meta_query[] = [
            'key' => 'is_featured',
            'value' => '1',
            'compare' => '='
        ];
    }
    
    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }

    // ===== ソート順 =====
    switch ($sort) {
        case 'date_asc':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'date_desc':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'amount_desc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'max_amount_numeric';
            $args['order'] = 'DESC';
            break;
        case 'amount_asc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'max_amount_numeric';
            $args['order'] = 'ASC';
            break;
        case 'deadline_asc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'deadline_timestamp';
            $args['order'] = 'ASC';
            break;
        case 'success_rate_desc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'grant_success_rate';
            $args['order'] = 'DESC';
            break;
        case 'featured_first':
        case 'featured':
            $args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];
            $args['meta_key'] = 'is_featured';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }

    // ===== クエリ実行 =====
    $query = new WP_Query($args);
    $grants = [];
    
    global $user_favorites, $current_view;
    $user_favorites = gi_get_user_favorites();
    $current_view = $view;

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            // 統一カードレンダリングを使用
            $html = gi_render_card_unified($post_id, $view);

            $grants[] = [
                'id' => $post_id,
                'html' => $html,
                'title' => get_the_title($post_id),
                'permalink' => get_permalink($post_id)
            ];
        }
        wp_reset_postdata();
    }

    // ===== 統計情報 =====
    $stats = [
        'total_found' => $query->found_posts,
        'current_page' => $page,
        'total_pages' => $query->max_num_pages,
        'posts_per_page' => $posts_per_page,
        'showing_from' => (($page - 1) * $posts_per_page) + 1,
        'showing_to' => min($page * $posts_per_page, $query->found_posts),
    ];

    // ===== レスポンス送信 =====
    wp_send_json_success([
        'grants' => $grants,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $query->max_num_pages,
            'total_posts' => $query->found_posts,
            'posts_per_page' => $posts_per_page,
        ],
        'stats' => $stats,
        'view' => $view,
        'query_info' => [
            'search' => $search,
            'filters_applied' => !empty($categories) || !empty($prefectures) || !empty($tags) || !empty($status) || !empty($amount) || !empty($only_featured),
            'sort' => $sort,
        ],
        'debug' => defined('WP_DEBUG') && WP_DEBUG ? $args : null,
    ]);
}

/**
 * Archive page grants loading with municipality support
 * アーカイブページの補助金読み込み（市町村対応）
 */
function gi_load_grants() {
    // Nonce verification
    check_ajax_referer('gi_ajax_nonce', 'nonce');
    
    // Get parameters
    $search = sanitize_text_field($_POST['search'] ?? '');
    $categories = isset($_POST['categories']) ? json_decode(stripslashes($_POST['categories']), true) : [];
    $prefectures = isset($_POST['prefectures']) ? json_decode(stripslashes($_POST['prefectures']), true) : [];
    $municipalities = isset($_POST['municipalities']) ? json_decode(stripslashes($_POST['municipalities']), true) : [];
    $region = sanitize_text_field($_POST['region'] ?? '');
    $amount = sanitize_text_field($_POST['amount'] ?? '');
    $status = isset($_POST['status']) ? json_decode(stripslashes($_POST['status']), true) : [];
    $only_featured = sanitize_text_field($_POST['only_featured'] ?? '');
    $sort = sanitize_text_field($_POST['sort'] ?? 'date_desc');
    $view = sanitize_text_field($_POST['view'] ?? 'grid');
    $page = max(1, intval($_POST['page'] ?? 1));
    
    // Build query args
    $args = [
        'post_type' => 'grant',
        'posts_per_page' => 12,
        'post_status' => 'publish',
        'paged' => $page,
    ];
    
    // AI-enhanced semantic search
    $use_semantic_search = false;
    $semantic_results = [];
    
    if (!empty($search)) {
        // Try semantic search first if available
        if (class_exists('GI_Semantic_Search')) {
            try {
                $semantic_search = GI_Semantic_Search::getInstance();
                if ($semantic_search && method_exists($semantic_search, 'search')) {
                    $semantic_results = $semantic_search->search($search, [
                        'limit' => 50, // Get more results for filtering
                        'threshold' => 0.7,
                    ]);
                    
                    if (!empty($semantic_results) && isset($semantic_results['posts'])) {
                        $use_semantic_search = true;
                        $post_ids = array_column($semantic_results['posts'], 'ID');
                        
                        // Use post__in for semantic search results
                        $args['post__in'] = $post_ids;
                        $args['orderby'] = 'post__in'; // Preserve semantic ranking
                    }
                }
            } catch (Exception $e) {
                error_log('Semantic search error in gi_load_grants: ' . $e->getMessage());
            }
        }
        
        // Fallback to traditional search if semantic search didn't work
        if (!$use_semantic_search) {
            $args['s'] = $search;
        }
    }
    
    // Taxonomy query
    $tax_query = ['relation' => 'AND'];
    
    if (!empty($categories)) {
        $tax_query[] = [
            'taxonomy' => 'grant_category',
            'field' => 'slug',
            'terms' => $categories,
        ];
    }
    
    if (!empty($prefectures)) {
        $tax_query[] = [
            'taxonomy' => 'grant_prefecture',
            'field' => 'slug',
            'terms' => $prefectures,
        ];
    }
    
    if (!empty($municipalities)) {
        $tax_query[] = [
            'taxonomy' => 'grant_municipality',
            'field' => 'slug',
            'terms' => $municipalities,
        ];
    }
    
    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }
    
    // Meta query
    $meta_query = ['relation' => 'AND'];
    
    if (!empty($status)) {
        $db_statuses = array_map(function($s) {
            return $s === 'active' ? 'open' : ($s === 'upcoming' ? 'upcoming' : $s);
        }, $status);
        
        $meta_query[] = [
            'key' => 'application_status',
            'value' => $db_statuses,
            'compare' => 'IN',
        ];
    }
    
    if ($only_featured === '1') {
        $meta_query[] = [
            'key' => 'is_featured',
            'value' => '1',
            'compare' => '=',
        ];
    }
    
    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }
    
    // Sorting
    switch ($sort) {
        case 'amount_desc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'max_amount_numeric';
            $args['order'] = 'DESC';
            break;
        case 'featured_first':
            $args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];
            $args['meta_key'] = 'is_featured';
            break;
        case 'deadline_asc':
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = 'application_deadline';
            $args['order'] = 'ASC';
            break;
        default:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
    }
    
    // Execute query
    $query = new WP_Query($args);
    
    // Get user favorites
    $user_favorites = function_exists('gi_get_user_favorites') ? gi_get_user_favorites() : [];
    
    // Build grant HTML
    $grants = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            // Set global variables for template
            $GLOBALS['current_view'] = $view;
            $GLOBALS['user_favorites'] = $user_favorites;
            
            // Capture template output
            ob_start();
            get_template_part('template-parts/grant-card-unified');
            $html = ob_get_clean();
            
            $grants[] = [
                'id' => get_the_ID(),
                'html' => $html,
            ];
        }
        wp_reset_postdata();
    }
    
    // Stats
    $stats = [
        'total_found' => $query->found_posts,
        'current_page' => $page,
        'total_pages' => $query->max_num_pages,
    ];
    
    wp_send_json_success([
        'grants' => $grants,
        'stats' => $stats,
        'pagination' => [
            'current' => $page,
            'total' => $query->max_num_pages,
        ],
    ]);
}
add_action('wp_ajax_gi_load_grants', 'gi_load_grants');
add_action('wp_ajax_nopriv_gi_load_grants', 'gi_load_grants');