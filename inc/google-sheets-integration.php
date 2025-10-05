<?php
/**
 * Google Sheets Integration (Consolidated)
 * 
 * All Google Sheets functionality in one file:
 * - GoogleSheetsSync: Main sync class
 * - SheetsWebhookHandler: Webhook processing
 * - SheetsInitializer: Sheet initialization
 * - SheetsAdminUI: Admin interface
 * 
 * @package Grant_Insight_Perfect
 * @version 2.0.0 (Consolidated Edition - 4 files merged)
 */

if (!defined('ABSPATH')) {
    exit;
}

class GoogleSheetsSync {
    
    private static $instance = null;
    private $service_account_key;
    private $spreadsheet_id;
    private $sheet_name;
    private $access_token;
    private $token_expires_at;
    
    // Google Sheets API設定
    const SHEETS_API_URL = 'https://sheets.googleapis.com/v4/spreadsheets/';
    const AUTH_SCOPE = 'https://www.googleapis.com/auth/spreadsheets';
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_settings();
        $this->add_hooks();
    }
    
    /**
     * 設定の初期化
     */
    private function init_settings() {
        // サービスアカウントキー（セキュアに保存）
        $this->service_account_key = array(
            "type" => "service_account",
            "project_id" => "grant-sheets-integration",
            "private_key_id" => "c0fdd6753a43e1c51cbc1854c4ce53cb461b0136",
            "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC+Ba+i0O4k0Jta\n17u3D/hJaqkLuptpyknOhjeQLzOGl9GtRP88KYX+NpKO1RxuuZMmlBt/7ShlXDPk\nJXdOtOjPlMzHZeh32M/f+98L9S9PVfapGUKRV0p4XJmExljmP7AVnXaMjlXqm9BJ\ngvO7K898LApyAsdrtcOYgt371LWZbQdTqpNWQemfJcYnTndwMcYzv6Snm/lOUruD\nrV2VOhvsMfqwVOaKywhE6rvUrF1ARaT3meQJyF9CpqFcb947f5phRUVD1QEdQp1K\nfGeFmMqR3nT4sY6I7VVqnseyr7v6U4i9V2aaL8KhUmH895xRlL6cc+QR7lgPtkT3\nZ8FJdseLAgMBAAECggEAWj9OFrg+2jo/Bmp+SyepBolDJwBl7lz2J8Fj4zUfthUl\nrrKdu9+GtWEKww5g1g+J3SErXFrwvA8J0BmhK77M8UWc6jiyqzTMKXcwjDfS082i\ne9Y04N1Bz58/BCnFr/jgcquZ0ZCKKoX86uToR+U7QiCSh2pddwDZF/ZTYla4NtiZ\nP/uZBAIuO/Fz2bLnjzQrQ1tLBdgY3mWx/wChi6+JhqubiNTnrWqy8qXG8P2OieZS\nQxU31/EjOp8rK4ErxqN5WDS0BRhIKM0DTN3WXwB8Sb5JCSluxksdICvNshiilsVF\nQGsXF3pGZA6Okv9cJS0u6vUoYVMMSzeWQvyM0tKwuQKBgQDgrUS2K21sVun+mI3L\niQ99XlMDT0AhsDaSWyenqveNawosoKz3ueBXEwkpOcM8DdcTDKbZVohM7h1cTEax\nPobdj2bQdUFWkzup5kekVBu88bIPthTMK5IuTUcHYyfiH8V7vsEtrX184UAiET/p\nXmHZ+lcUCuL+8+uKogEdvy/1UwKBgQDYg5eJlQ0hoOH0VP8HkSeJSn246X8CdeHT\n1kgkymJcLwWYr+EKngTQrSkLkIfxBER3UMfHtla95IL4qGC/iNcIWbie2Gtc2wXz\nWvwpaoliReoKOYyFG94Fl5zdcp5xYi2oA2qB9LM+eyCqqEEkVhpg3w61Xfj03wMI\n6Ibxc0al6QKBgQC7KVut7WtP7u8qOWcVgG244BSDE0e3SJWNQgY8tD1YPyzQlGDC\nVMM/hgoBn661nknmAooTTvRoMYuf0aKqEA5FDyp0yNjPCAORutU/XRlmQmk0kVet\n5TX3AEUFMGKPCix2syc1p+p7VyEXwArfmtIkxVg4yADkpck3SVFouFV5JQKBgDcz\njb45L0jkoNdPmFoQixj40gcEGSrCbVo6JtiidON15aJhLSos0aN2kqFtLwum/+G/\nyb/EYGc3zKCjJU+QDusFHQn6uZzKBsFd8C6LCA3zL1F+DLKfQUMBva/EGltkIanV\nfSE3B0Al2lVIYptmDIGoPTLGi8O63CY4SrdioZ+JAoGAMjzeU4jqFtkXaiRBTa+v\njspaqbk1rq1x4ZmnPMZzMQnZLStP9QP7SQn5/my/ZSWcnmjxW8ZgMdfWB1TD51RC\n4HYL/jGrjOUmumshQmiA1a7zCvr8yVJFkOVcYpCWl6TT5hiFbqrW82Dw73JFHTuK\n30Chu7ki9aOiJJeMmHaOfOU=\n-----END PRIVATE KEY-----\n",
            "client_email" => "grant-sheets-service@grant-sheets-integration.iam.gserviceaccount.com",
            "client_id" => "109769300820349787611",
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/grant-sheets-service%40grant-sheets-integration.iam.gserviceaccount.com",
            "universe_domain" => "googleapis.com"
        );
        
        // スプレッドシートの設定
        $this->spreadsheet_id = '1kGc1Eb4AYvURkSfdzMwipNjfe8xC6iGCM2q1sUgIfWg';
        $this->sheet_name = 'grant_import';
        
        // 既存のアクセストークンを確認
        $stored_token = get_option('gi_sheets_access_token');
        $stored_expires = get_option('gi_sheets_token_expires');
        
        if ($stored_token && $stored_expires && time() < $stored_expires) {
            $this->access_token = $stored_token;
            $this->token_expires_at = $stored_expires;
        }
    }
    
    /**
     * WordPressフックの追加（手動同期のみ）
     */
    private function add_hooks() {
        // 自動同期機能は削除しました - 手動同期のみ利用可能
        
        // 既存のCronスケジュールをクリア
        wp_clear_scheduled_hook('gi_sheets_sync_cron');
        
        // AJAX ハンドラー（手動同期用のみ）
        add_action('wp_ajax_gi_manual_sheets_sync', array($this, 'ajax_manual_sync'));
        add_action('wp_ajax_gi_test_sheets_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_gi_setup_field_validation', array($this, 'ajax_setup_field_validation'));
        add_action('wp_ajax_gi_test_specific_fields', array($this, 'ajax_test_specific_fields'));
    }
    
    /**
     * Google Sheets APIアクセストークンを取得
     */
    private function get_access_token() {
        gi_log_error('Getting access token', array(
            'has_existing_token' => !empty($this->access_token),
            'token_expires_at' => $this->token_expires_at,
            'current_time' => time(),
            'token_still_valid' => ($this->token_expires_at && time() < ($this->token_expires_at - 300))
        ));
        
        // 既存のトークンが有効な場合はそれを使用
        if ($this->access_token && $this->token_expires_at && time() < ($this->token_expires_at - 300)) {
            gi_log_error('Using existing valid token');
            return $this->access_token;
        }
        
        gi_log_error('Generating new access token');
        
        // JWTを作成
        $jwt = $this->create_jwt();
        if (!$jwt) {
            gi_log_error('JWT creation failed');
            return false;
        }
        
        gi_log_error('JWT created successfully', array('jwt_length' => strlen($jwt)));
        
        // トークンリクエスト
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ),
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            gi_log_error('Google Sheets Token Request Failed', array(
                'error' => $response->get_error_message()
            ));
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        gi_log_error('Token request response', array(
            'response_code' => $response_code,
            'body' => $body
        ));
        
        $token_data = json_decode($body, true);
        
        if (!isset($token_data['access_token'])) {
            gi_log_error('Invalid Token Response', array(
                'response_code' => $response_code,
                'response' => $body,
                'parsed_data' => $token_data
            ));
            return false;
        }
        
        // トークンを保存
        $this->access_token = $token_data['access_token'];
        $this->token_expires_at = time() + ($token_data['expires_in'] - 300); // 5分早めに期限切れとする
        
        update_option('gi_sheets_access_token', $this->access_token);
        update_option('gi_sheets_token_expires', $this->token_expires_at);
        
        gi_log_error('New access token obtained and saved', array(
            'expires_at' => $this->token_expires_at,
            'expires_in' => $token_data['expires_in']
        ));
        
        return $this->access_token;
    }
    
    /**
     * シート名を取得
     */
    public function get_sheet_name() {
        return $this->sheet_name;
    }
    
    /**
     * スプレッドシートIDを取得
     */
    public function get_spreadsheet_id() {
        return $this->spreadsheet_id;
    }
    
    /**
     * JWT（JSON Web Token）を作成
     */
    private function create_jwt() {
        try {
            gi_log_error('Creating JWT', array(
                'client_email' => $this->service_account_key['client_email'],
                'has_private_key' => !empty($this->service_account_key['private_key'])
            ));
            
            $header = json_encode(array(
                'alg' => 'RS256',
                'typ' => 'JWT'
            ));
            
            $now = time();
            $payload = json_encode(array(
                'iss' => $this->service_account_key['client_email'],
                'scope' => self::AUTH_SCOPE,
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ));
            
            gi_log_error('JWT payload created', array(
                'iss' => $this->service_account_key['client_email'],
                'scope' => self::AUTH_SCOPE,
                'now' => $now,
                'exp' => $now + 3600
            ));
            
            $base64_header = $this->base64url_encode($header);
            $base64_payload = $this->base64url_encode($payload);
            
            $signature_input = $base64_header . '.' . $base64_payload;
            
            // 秘密鍵で署名
            $private_key = $this->service_account_key['private_key'];
            
            if (empty($private_key)) {
                gi_log_error('Private key is empty');
                return false;
            }
            
            // OpenSSL署名の実行
            $sign_result = openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
            
            if (!$sign_result) {
                gi_log_error('OpenSSL signing failed', array(
                    'openssl_error' => openssl_error_string(),
                    'private_key_length' => strlen($private_key)
                ));
                return false;
            }
            
            gi_log_error('JWT signing successful', array(
                'signature_length' => strlen($signature)
            ));
            
            $base64_signature = $this->base64url_encode($signature);
            
            $final_jwt = $signature_input . '.' . $base64_signature;
            
            gi_log_error('JWT created successfully', array(
                'jwt_length' => strlen($final_jwt)
            ));
            
            return $final_jwt;
            
        } catch (Exception $e) {
            gi_log_error('JWT creation failed', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
            return false;
        }
    }
    
    /**
     * Base64URL エンコード
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * スプレッドシートからデータを読み取り
     */
    public function read_sheet_data($range = null) {
        gi_log_error('Starting read_sheet_data', array('requested_range' => $range));
        
        $access_token = $this->get_access_token();
        if (!$access_token) {
            gi_log_error('read_sheet_data: No access token available');
            return false;
        }
        
        if (!$range) {
            $range = $this->get_sheet_name() . '!A:AE'; // 全データを取得（AE列まで）31列対応
        }
        
        gi_log_error('Reading from sheets', array(
            'range' => $range,
            'spreadsheet_id' => $this->spreadsheet_id
        ));
        
        $url = self::SHEETS_API_URL . $this->spreadsheet_id . '/values/' . urlencode($range);
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            gi_log_error('Sheets Read Request Failed', array(
                'error' => $response->get_error_message(),
                'url' => $url
            ));
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        gi_log_error('Sheets read response', array(
            'response_code' => $response_code,
            'body_length' => strlen($body)
        ));
        
        if ($response_code !== 200) {
            gi_log_error('Sheets Read Failed - Bad Response Code', array(
                'response_code' => $response_code,
                'response_body' => $body
            ));
            return false;
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            gi_log_error('JSON decode failed', array(
                'json_error' => json_last_error_msg(),
                'response_body' => $body
            ));
            return false;
        }
        
        $values = isset($data['values']) ? $data['values'] : array();
        
        gi_log_error('Read sheet data completed', array(
            'rows_count' => count($values),
            'first_row_columns' => !empty($values) ? count($values[0]) : 0
        ));
        
        return $values;
    }
    
    /**
     * スプレッドシートにデータを書き込み
     */
    public function write_sheet_data($range, $values, $input_option = 'RAW') {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            gi_log_error('Write Sheet Data: No access token available');
            return false;
        }
        
        gi_log_error('Writing to sheets', array(
            'range' => $range,
            'values_count' => count($values),
            'spreadsheet_id' => $this->spreadsheet_id,
            'sheet_name' => $this->sheet_name
        ));
        
        $url = self::SHEETS_API_URL . $this->spreadsheet_id . '/values/' . urlencode($range) . '?valueInputOption=' . $input_option;
        
        $request_body = array(
            'range' => $range,
            'majorDimension' => 'ROWS',
            'values' => $values
        );
        
        gi_log_error('Sheets API request details', array(
            'url' => $url,
            'request_body' => $request_body
        ));
        
        $response = wp_remote_request($url, array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($request_body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            gi_log_error('Sheets Write Request Failed', array(
                'error' => $response->get_error_message(),
                'range' => $range,
                'url' => $url
            ));
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        gi_log_error('Sheets write response', array(
            'response_code' => $response_code,
            'response_body' => $response_body,
            'range' => $range
        ));
        
        if ($response_code < 200 || $response_code >= 300) {
            gi_log_error('Sheets Write Failed - Bad Response Code', array(
                'response_code' => $response_code,
                'response_body' => $response_body,
                'range' => $range
            ));
            return false;
        }
        
        gi_log_error('Sheets write successful', array('range' => $range));
        return true;
    }
    
    /**
     * スプレッドシートに行を追加
     */
    public function append_sheet_data($values, $input_option = 'RAW') {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return false;
        }
        
        $url = self::SHEETS_API_URL . $this->spreadsheet_id . '/values/' . urlencode($this->sheet_name) . ':append?valueInputOption=' . $input_option;
        
        $request_body = array(
            'range' => $this->sheet_name,
            'majorDimension' => 'ROWS',
            'values' => array($values)
        );
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($request_body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            gi_log_error('Sheets Append Failed', array(
                'error' => $response->get_error_message()
            ));
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        return $response_code >= 200 && $response_code < 300;
    }
    
    /**
     * 投稿データをスプレッドシート用に変換
     */
    private function convert_post_to_sheet_row($post_id) {
        try {
            gi_log_error('Converting post to sheet row', array('post_id' => $post_id));
            
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'grant') {
                gi_log_error('Invalid post for conversion', array('post_id' => $post_id, 'post_type' => $post ? $post->post_type : 'null'));
                return false;
            }
            
            // 基本データ (A-G列)
            $row = array(
                $post_id, // A: ID
                $post->post_title, // B: タイトル
                wp_strip_all_tags($post->post_content), // C: 内容（HTMLタグを除去）
                $post->post_excerpt, // D: 抜粋
                $post->post_status, // E: ステータス
                $post->post_date, // F: 作成日
                $post->post_modified, // G: 更新日
            );
            
            // ACFフィールドを追加 (H-Q列)
            $acf_fields = array(
                'max_amount',              // H: 助成金額（表示用）
                'max_amount_numeric',      // I: 助成金額（数値）
                'deadline',                // J: 申請期限（表示用）
                'deadline_date',           // K: 申請期限（日付）
                'organization',            // L: 実施組織
                'organization_type',       // M: 組織タイプ
                'grant_target',            // N: 対象者・対象事業
                'application_method',      // O: 申請方法
                'contact_info',            // P: 問い合わせ先
                'official_url'             // Q: 公式URL
            );
            
            foreach ($acf_fields as $field) {
                $value = get_field($field, $post_id);
                $row[] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
            }
            
            // R列: 地域制限 (ACFフィールド)
            $regional_limitation = get_field('regional_limitation', $post_id);
            $row[] = (string)$regional_limitation;
            
            // S列: 申請ステータス (ACFフィールド)
            $application_status = get_field('application_status', $post_id);
            $row[] = (string)$application_status;
            
            // T列: 都道府県 (タクソノミー) ★完全連携
            $prefectures = wp_get_post_terms($post_id, 'grant_prefecture', array('fields' => 'names'));
            $row[] = (is_array($prefectures) && !is_wp_error($prefectures)) ? implode(', ', $prefectures) : '';
            
            // U列: 市町村 (タクソノミー) ★完全連携
            $municipalities = wp_get_post_terms($post_id, 'grant_municipality', array('fields' => 'names'));
            $row[] = (is_array($municipalities) && !is_wp_error($municipalities)) ? implode(', ', $municipalities) : '';
            
            // V列: カテゴリ (タクソノミー) ★完全連携
            $categories = wp_get_post_terms($post_id, 'grant_category', array('fields' => 'names'));
            $row[] = (is_array($categories) && !is_wp_error($categories)) ? implode(', ', $categories) : '';
            
            // W列: タグ (タクソノミー) ★完全連携
            $tags = wp_get_post_terms($post_id, 'grant_tag', array('fields' => 'names'));
            $row[] = (is_array($tags) && !is_wp_error($tags)) ? implode(', ', $tags) : '';
            
            // 新規フィールド (X-AD列) ★31列対応（修正版）
            $new_acf_fields = array(
                'external_link',               // X: 外部リンク
                'area_notes',                  // Y: 地域に関する備考（修正）
                'required_documents_detailed', // Z: 必要書類（修正）
                'adoption_rate',               // AA: 採択率（%）
                'difficulty_level',            // AB: 申請難易度（修正）
                'eligible_expenses_detailed',  // AC: 対象経費（修正）
                'subsidy_rate_detailed'        // AD: 補助率（修正）
            );
            
            foreach ($new_acf_fields as $field) {
                $value = get_field($field, $post_id);
                $row[] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
            }
            
            // AE列: シート更新日
            $row[] = current_time('mysql');
            
            gi_log_error('Post converted to sheet row successfully', array('post_id' => $post_id, 'columns' => count($row)));
            
            return $row;
            
        } catch (Exception $e) {
            gi_log_error('convert_post_to_sheet_row failed', array(
                'post_id' => $post_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            return false;
        }
    }
    
    /**
     * スプレッドシートのヘッダー行を設定
     */
    public function setup_sheet_headers() {
        try {
            gi_log_error('Setting up sheet headers');
            
            $headers = array(
                'ID (自動入力)',                          // A列 - WordPress投稿ID
                'タイトル',                               // B列 - 助成金名
                '内容・詳細',                            // C列 - 助成金の詳細説明
                '抜粋・概要',                            // D列 - 簡単な概要
                'ステータス (draft/publish/private)',     // E列 - 投稿ステータス
                '作成日 (自動入力)',                      // F列 - WordPress作成日
                '更新日 (自動入力)',                      // G列 - WordPress更新日
                '助成金額 (例: 300万円)',                 // H列 - 表示用金額
                '助成金額数値 (例: 3000000)',             // I列 - ソート用数値
                '申請期限 (例: 令和6年3月31日)',          // J列 - 表示用期限
                '申請期限日付 (YYYY-MM-DD)',             // K列 - ソート用日付
                '実施組織名',                            // L列 - 実施する組織名
                '組織タイプ (national/prefecture/city/public_org/private_org/other)', // M列 - 組織分類
                '対象者・対象事業',                      // N列 - 助成対象の詳細
                '申請方法 (online/mail/visit/mixed)',     // O列 - 申請方法
                '問い合わせ先',                          // P列 - 連絡先情報
                '公式URL',                               // Q列 - 公式サイトURL
                '地域制限 (nationwide/prefecture_only/municipality_only/region_group/specific_area)', // R列 - 地域制限タイプ
                '申請ステータス (open/upcoming/closed/suspended)', // S列 - 募集状況
                '都道府県 (例: 東京都)',                  // T列 - 都道府県名 ★完全連携
                '市町村 (例: 新宿区,渋谷区)',            // U列 - 市町村名 ★完全連携
                'カテゴリ (例: ビジネス支援,IT関連)',     // V列 - 分類カテゴリ ★完全連携
                'タグ (例: スタートアップ,中小企業)',     // W列 - タグ ★完全連携
                '外部リンク',                            // X列 - 参考リンク
                '地域に関する備考',                      // Y列 - 地域制限の詳細
                '必要書類',                              // Z列 - 申請に必要な書類
                '採択率（%）',                          // AA列 - 採択率の数値
                '申請難易度 (easy/normal/hard/very_hard)', // AB列 - 難易度評価
                '対象経費',                              // AC列 - 補助対象経費の詳細
                '補助率 (例: 2/3, 50%)',                // AD列 - 補助率・補助割合
                'シート更新日 (自動入力)'                // AE列 - 最終同期日時
            );
            
            gi_log_error('Headers array created', array('count' => count($headers)));
            
            $range = $this->sheet_name . '!A1:AE1';
            gi_log_error('Writing headers to range', array('range' => $range));
            
            $result = $this->write_sheet_data($range, array($headers));
            
            gi_log_error('Headers setup result', array('success' => $result));
            
            return $result;
            
        } catch (Exception $e) {
            gi_log_error('setup_sheet_headers failed', array(
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            return false;
        }
    }
    
    // 自動同期メソッドは削除されました - 手動同期のみ利用可能
    
    /**
     * スプレッドシートから投稿IDの行番号を検索
     */
    private function find_post_row_in_sheet($post_id, $sheet_data) {
        if (empty($sheet_data)) {
            return false;
        }
        
        foreach ($sheet_data as $index => $row) {
            if (isset($row[0]) && intval($row[0]) === intval($post_id)) {
                return $index + 1; // 1-based indexing
            }
        }
        
        return false;
    }
    
    // 自動削除・ステータス変更同期メソッドは削除されました
    
    /**
     * スプレッドシートからWordPressへの同期
     */
    public function sync_sheets_to_wp() {
        try {
            gi_log_error('Starting sync_sheets_to_wp');
            
            $sheet_data = $this->read_sheet_data();
            if (empty($sheet_data)) {
                gi_log_error('No sheet data found');
                return 0;
            }
            
            gi_log_error('Sheet data retrieved', array('row_count' => count($sheet_data)));
            
            $headers = array_shift($sheet_data); // ヘッダー行を除去
            $synced_count = 0;
            $new_post_ids_to_update = array(); // 新規作成された投稿のIDと行番号を記録
        
        foreach ($sheet_data as $row_index => $row) {
            if (empty($row) || count($row) < 5) {
                continue; // 不完全な行をスキップ
            }
            
            $original_post_id = intval($row[0]); // 元のpost_id（空の場合は0）
            $post_id = $original_post_id;
            $title = isset($row[1]) ? sanitize_text_field($row[1]) : '';
            $content = isset($row[2]) ? wp_kses_post($row[2]) : '';
            $excerpt = isset($row[3]) ? sanitize_textarea_field($row[3]) : '';
            $status = isset($row[4]) ? sanitize_text_field($row[4]) : 'draft';
            
            // 削除されたアイテムの処理
            if ($status === 'deleted') {
                if ($post_id && get_post($post_id)) {
                    wp_delete_post($post_id, true);
                    $synced_count++;
                }
                continue;
            }
            
            $was_new_post = false; // 新規投稿かどうかのフラグ
            
            // 既存投稿の更新または新規作成
            if ($post_id && get_post($post_id)) {
                // 既存投稿を更新
                $updated_post = array(
                    'ID' => $post_id,
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_excerpt' => $excerpt,
                    'post_status' => $status,
                );
                
                wp_update_post($updated_post);
                gi_log_error('Updated existing post', array('post_id' => $post_id, 'title' => $title));
            } else {
                // 新規投稿を作成
                $new_post = array(
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_excerpt' => $excerpt,
                    'post_status' => $status,
                    'post_type' => 'grant'
                );
                
                $post_id = wp_insert_post($new_post);
                $was_new_post = true;
                
                if ($post_id && !is_wp_error($post_id)) {
                    // 新規投稿が作成されたので、後でスプレッドシートのA列を更新する必要がある
                    $sheet_row_number = $row_index + 2; // ヘッダー行を考慮して+2（配列は0ベース、Sheetsは1ベース+ヘッダー）
                    $new_post_ids_to_update[$sheet_row_number] = $post_id;
                    gi_log_error('Created new post, will update spreadsheet', array(
                        'post_id' => $post_id, 
                        'title' => $title, 
                        'sheet_row' => $sheet_row_number
                    ));
                }
            }
            
            if ($post_id && !is_wp_error($post_id)) {
                // ACFフィールドを更新（タクソノミー化されたフィールドは除外）
                $acf_fields = array(
                    'max_amount' => isset($row[7]) ? $row[7] : '',
                    'max_amount_numeric' => isset($row[8]) ? intval($row[8]) : 0,
                    'deadline' => isset($row[9]) ? $row[9] : '',
                    'deadline_date' => isset($row[10]) ? $row[10] : '',
                    'organization' => isset($row[11]) ? $row[11] : '',
                    'organization_type' => isset($row[12]) ? $row[12] : 'national',
                    'grant_target' => isset($row[13]) ? $row[13] : '',
                    'application_method' => isset($row[14]) ? $row[14] : 'online',
                    'contact_info' => isset($row[15]) ? $row[15] : '',
                    'official_url' => isset($row[16]) ? $row[16] : '',
                    'regional_limitation' => isset($row[17]) ? $row[17] : 'nationwide', // 新R列
                    'application_status' => isset($row[18]) ? $row[18] : 'open', // 新S列
                );
                
                // ACFフィールドの同期ログ
                gi_log_error('Syncing ACF fields', array(
                    'post_id' => $post_id,
                    'row_index' => $row_index,
                    'acf_fields_count' => count($acf_fields),
                    'row_length' => count($row)
                ));
                
                // ACFフィールドを更新
                foreach ($acf_fields as $field => $value) {
                    $update_result = update_field($field, $value, $post_id);
                }
                
                // タクソノミーデータの同期（都道府県・市町村・カテゴリー）
                
                // ★完全連携: スプレッドシートからタクソノミーデータを同期
                
                // 都道府県を設定（T列のデータから） ★完全連携
                if (isset($row[19]) && !empty($row[19])) {
                    $prefectures = array_map('trim', explode(',', $row[19]));
                    $prefecture_result = gi_set_terms_with_auto_create($post_id, $prefectures, 'grant_prefecture');
                    
                    gi_log_error('Prefecture sync result', array(
                        'post_id' => $post_id,
                        'raw_prefecture_data' => $row[19],
                        'prefectures_array' => $prefectures,
                        'set_terms_result' => $prefecture_result
                    ));
                }
                
                // 市町村を設定（U列のデータから） ★完全連携
                if (isset($row[20]) && !empty($row[20])) {
                    $municipalities = array_map('trim', explode(',', $row[20]));
                    $municipality_result = gi_set_terms_with_auto_create($post_id, $municipalities, 'grant_municipality');
                    
                    gi_log_error('Municipality sync result', array(
                        'post_id' => $post_id,
                        'raw_municipality_data' => $row[20],
                        'municipalities_array' => $municipalities,
                        'set_terms_result' => $municipality_result
                    ));
                }
                
                // 都道府県から市町村への自動同期
                if (function_exists('gi_sync_prefecture_to_municipality')) {
                    gi_sync_prefecture_to_municipality($post_id, get_post($post_id), true);
                }
                
                // カテゴリを設定（V列のデータから） ★完全連携 + 自動作成
                if (isset($row[21]) && !empty($row[21])) {
                    $categories = array_map('trim', explode(',', $row[21]));
                    $category_result = gi_set_terms_with_auto_create($post_id, $categories, 'grant_category');
                    
                    gi_log_error('Category sync result', array(
                        'post_id' => $post_id,
                        'raw_category_data' => $row[21],
                        'categories_array' => $categories,
                        'set_terms_result' => $category_result
                    ));
                }
                
                // タグを設定（W列のデータから） ★完全連携 + 自動作成
                if (isset($row[22]) && !empty($row[22])) {
                    $tags = array_map('trim', explode(',', $row[22]));
                    gi_set_terms_with_auto_create($post_id, $tags, 'grant_tag');
                }
                
                // 新規ACFフィールドの同期 (X-AD列) ★31列対応（修正版）
                $new_acf_fields = array(
                    'external_link' => isset($row[23]) ? $row[23] : '',                   // X列: 外部リンク
                    'area_notes' => isset($row[24]) ? $row[24] : '',                      // Y列: 地域に関する備考（修正）
                    'required_documents_detailed' => isset($row[25]) ? $row[25] : '',     // Z列: 必要書類（修正）
                    'adoption_rate' => isset($row[26]) ? floatval($row[26]) : 0,          // AA列: 採択率（%）
                    'difficulty_level' => isset($row[27]) ? $row[27] : '中級',             // AB列: 申請難易度（修正）
                    'eligible_expenses_detailed' => isset($row[28]) ? $row[28] : '',      // AC列: 対象経費（修正）
                    'subsidy_rate_detailed' => isset($row[29]) ? $row[29] : '',           // AD列: 補助率（修正）
                );
                
                // 新規ACFフィールドを更新
                foreach ($new_acf_fields as $field => $value) {
                    $update_result = update_field($field, $value, $post_id);
                    gi_log_error('New ACF field updated', array(
                        'post_id' => $post_id,
                        'field' => $field,
                        'value' => $value,
                        'update_result' => $update_result
                    ));
                }
                
                $synced_count++;
            }
        }
        
        // 新規作成された投稿のIDをスプレッドシートに書き戻し
        if (!empty($new_post_ids_to_update)) {
            gi_log_error('Updating spreadsheet with new post IDs', array('count' => count($new_post_ids_to_update)));
            
            foreach ($new_post_ids_to_update as $sheet_row => $new_post_id) {
                try {
                    // A列（post_id列）のみを更新
                    $range = $this->get_sheet_name() . '!A' . $sheet_row;
                    $success = $this->write_sheet_data($range, array(array($new_post_id)));
                    
                    if ($success) {
                        gi_log_error('Updated post ID in spreadsheet', array(
                            'post_id' => $new_post_id, 
                            'row' => $sheet_row, 
                            'range' => $range
                        ));
                    } else {
                        gi_log_error('Failed to update post ID in spreadsheet', array(
                            'post_id' => $new_post_id, 
                            'row' => $sheet_row
                        ));
                    }
                } catch (Exception $e) {
                    gi_log_error('Exception while updating post ID in spreadsheet', array(
                        'post_id' => $new_post_id,
                        'row' => $sheet_row,
                        'error' => $e->getMessage()
                    ));
                }
            }
        }
        
        gi_log_error('sync_sheets_to_wp completed', array(
            'synced_count' => $synced_count,
            'new_posts_updated' => count($new_post_ids_to_update)
        ));
        return $synced_count;
        
        } catch (Exception $e) {
            gi_log_error('sync_sheets_to_wp failed', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
            throw $e;
        }
    }
    
    /**
     * 完全双方向同期
     */
    public function full_bidirectional_sync() {
        gi_log_error('Starting full bidirectional sync');
        
        try {
            // Step 1: スプレッドシートからWordPressに同期（既存データの更新）
            gi_log_error('Step 1: Sheets to WordPress sync');
            $sheets_synced = 0;
            try {
                $sheets_synced = $this->sync_sheets_to_wp();
                gi_log_error('Sheets to WP sync completed', array('sheets_synced' => $sheets_synced));
            } catch (Exception $e) {
                gi_log_error('Sheets to WP sync failed, continuing with WP to Sheets', array(
                    'error' => $e->getMessage()
                ));
                // スプレッドシート→WordPress同期が失敗しても、WordPress→スプレッドシート同期は実行
            }
            
            // Step 2: WordPressからスプレッドシートに同期（新規データの追加）
            gi_log_error('Step 2: WordPress to Sheets sync');
            $wp_synced = $this->sync_all_posts_to_sheets();
            
            $result = array(
                'sheets_to_wp' => $sheets_synced,
                'wp_to_sheets' => $wp_synced,
                'total_synced' => $sheets_synced + $wp_synced
            );
            
            // 同期結果をログに記録
            $this->log_sync_result('scheduled', 'success', 
                "双方向同期完了: Sheets→WP({$sheets_synced}件), WP→Sheets({$wp_synced}件)");
            
            gi_log_error('Full bidirectional sync completed', $result);
            
            return $result;
            
        } catch (Exception $e) {
            // 同期失敗をログに記録
            $this->log_sync_result('scheduled', 'failed', $e->getMessage());
            
            gi_log_error('Full bidirectional sync failed', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ));
            throw $e;
        }
    }
    
    /**
     * 全投稿をスプレッドシートに同期
     */
    public function sync_all_posts_to_sheets() {
        gi_log_error('Starting sync_all_posts_to_sheets');
        
        // 全件取得（バッチ処理で分割して同期）
        $posts = get_posts(array(
            'post_type' => 'grant',
            'post_status' => array('publish', 'draft', 'private'),
            'numberposts' => -1
        ));
        
        gi_log_error('Found posts to sync', array('count' => count($posts)));
        
        if (empty($posts)) {
            gi_log_error('No posts found to sync');
            return 0;
        }
        
        // まず既存データをクリア
        gi_log_error('Clearing existing sheet data');
        $clear_result = $this->clear_sheet_range('A:AE'); // 31列対応
        gi_log_error('Clear result', array('success' => $clear_result));
        
        // ヘッダーを設定
        gi_log_error('Setting up sheet headers');
        $header_result = $this->setup_sheet_headers();
        gi_log_error('Header setup result', array('success' => $header_result));
        
        if (!$header_result) {
            throw new Exception('ヘッダーの設定に失敗しました');
        }
        
        // バッチサイズを設定（Google Sheets APIの制限を考慮）
        $batch_size = 100; // 一度に100件まで
        $total_synced = 0;
        $all_data = array();
        
        // 全データを準備
        foreach ($posts as $post) {
            try {
                gi_log_error('Preparing post data', array('post_id' => $post->ID, 'title' => $post->post_title));
                $row_data = $this->convert_post_to_sheet_row($post->ID);
                if ($row_data) {
                    $all_data[] = $row_data;
                }
            } catch (Exception $e) {
                gi_log_error('Failed to prepare individual post', array(
                    'post_id' => $post->ID,
                    'error' => $e->getMessage()
                ));
                // 個別の投稿の失敗では全体を停止させない
                continue;
            }
        }
        
        gi_log_error('Prepared all data', array('total_posts' => count($all_data)));
        
        // バッチごとに分割して書き込み
        if (!empty($all_data)) {
            $batches = array_chunk($all_data, $batch_size);
            $sheet_name = $this->get_sheet_name();
            $current_row = 2; // ヘッダー行の次から
            
            foreach ($batches as $batch_index => $batch_data) {
                gi_log_error('Processing batch', array(
                    'batch_index' => $batch_index + 1,
                    'batch_size' => count($batch_data),
                    'start_row' => $current_row
                ));
                
                $end_row = $current_row + count($batch_data) - 1;
                $range = $sheet_name . "!A{$current_row}:AE{$end_row}"; // 31列対応
                
                $result = $this->write_sheet_data($range, $batch_data);
                
                if ($result) {
                    $total_synced += count($batch_data);
                    $current_row = $end_row + 1;
                    gi_log_error('Batch write successful', array(
                        'batch_synced' => count($batch_data),
                        'total_synced' => $total_synced
                    ));
                } else {
                    gi_log_error('Batch write failed', array('batch_index' => $batch_index + 1));
                    throw new Exception("バッチ " . ($batch_index + 1) . " の書き込みに失敗しました");
                }
                
                // API制限を考慮して少し待機
                if (count($batches) > 1 && $batch_index < count($batches) - 1) {
                    sleep(1);
                }
            }
            
            gi_log_error('All batches completed', array('total_synced' => $total_synced));
            return $total_synced;
        }
        
        gi_log_error('No data to sync');
        return 0;
    }
    
    /**
     * 手動同期のAJAXハンドラー
     */
    public function ajax_manual_sync() {
        // タイムアウトとメモリ制限の拡張
        set_time_limit(300); // 5分
        ini_set('memory_limit', '256M');
        
        // 全体をtry-catchでラップして500エラーを防ぐ
        try {
            // デバッグ: AJAXリクエストが到達したことをログに記録
            gi_log_error('AJAX manual sync request received', array(
                'user_id' => get_current_user_id(),
                'post_data' => $_POST,
                'request_method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'UNKNOWN'
            ));
            
            // Nonce検証
            try {
                check_ajax_referer('gi_sheets_nonce', 'nonce');
                gi_log_error('Nonce verification passed');
            } catch (Exception $e) {
                gi_log_error('Nonce verification failed', array('error' => $e->getMessage()));
                wp_send_json_error('Nonce verification failed: ' . $e->getMessage());
                return;
            }
            
            // 権限チェック
            if (!current_user_can('edit_posts')) {
                gi_log_error('Permission denied', array('user_id' => get_current_user_id()));
                wp_send_json_error('Permission denied');
                return;
            }
            
            gi_log_error('Permission check passed');
            
            // 同期方向を取得
            $sync_direction = isset($_POST['direction']) ? sanitize_text_field($_POST['direction']) : 'both';
            gi_log_error('Sync direction determined', array('direction' => $sync_direction));
            
            // 同期処理を実行
            gi_log_error('Manual sync started', array('direction' => $sync_direction));
            
            switch ($sync_direction) {
                case 'wp_to_sheets':
                    gi_log_error('Starting WP to Sheets sync');
                    $this->sync_all_posts_to_sheets();
                    $message = 'WordPressからスプレッドシートへの同期が完了しました。';
                    break;
                
                case 'sheets_to_wp':
                    gi_log_error('Starting Sheets to WP sync');
                    $synced = $this->sync_sheets_to_wp();
                    $message = "スプレッドシートからWordPressへ {$synced} 件同期しました。";
                    break;
                
                case 'both':
                default:
                    gi_log_error('Starting bidirectional sync');
                    $result = $this->full_bidirectional_sync();
                    $message = "双方向同期が完了しました。Sheets→WP: {$result['sheets_to_wp']}件、WP→Sheets: {$result['wp_to_sheets']}件";
                    break;
            }
            
            // 同期結果をログに記録
            $this->log_sync_result('manual', 'success', $message);
            
            gi_log_error('Manual sync completed successfully');
            wp_send_json_success($message);
            
        } catch (Exception $e) {
            // 同期失敗をログに記録
            $this->log_sync_result('manual', 'failed', $e->getMessage());
            
            gi_log_error('Manual sync exception caught', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error('同期に失敗しました: ' . $e->getMessage());
            
        } catch (Error $e) {
            gi_log_error('Manual sync fatal error caught', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error('同期中に致命的エラーが発生しました: ' . $e->getMessage());
            
        } catch (Throwable $e) {
            // PHP 7+ のすべてのエラーをキャッチ
            gi_log_error('Manual sync throwable caught', array(
                'error' => $e->getMessage(),
                'file' => method_exists($e, 'getFile') ? $e->getFile() : 'unknown',
                'line' => method_exists($e, 'getLine') ? $e->getLine() : 'unknown',
                'trace' => method_exists($e, 'getTraceAsString') ? $e->getTraceAsString() : 'no trace'
            ));
            wp_send_json_error('予期しないエラーが発生しました: ' . $e->getMessage());
        }
    }
    
    /**
     * 接続テストのAJAXハンドラー
     */
    public function ajax_test_connection() {
        // デバッグ: 接続テストリクエストが到達
        gi_log_error('AJAX test connection request received', array(
            'user_id' => get_current_user_id(),
            'post_data' => $_POST
        ));
        
        check_ajax_referer('gi_sheets_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            gi_log_error('Permission denied for connection test', array('user_id' => get_current_user_id()));
            wp_send_json_error('Permission denied');
        }
        
        try {
            $access_token = $this->get_access_token();
            if (!$access_token) {
                wp_send_json_error('認証に失敗しました。');
                return;
            }
            
            // テスト読み取り
            $test_data = $this->read_sheet_data($this->sheet_name . '!A1:A1');
            
            if ($test_data !== false) {
                wp_send_json_success('Google Sheetsへの接続に成功しました。');
            } else {
                wp_send_json_error('スプレッドシートの読み取りに失敗しました。');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('接続テストに失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     * スプレッドシートの範囲をクリア
     */
    public function clear_sheet_range($range) {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            gi_log_error('Failed to get access token for clear operation');
            return false;
        }
        
        $url = self::SHEETS_API_URL . $this->spreadsheet_id . '/values/' . urlencode($this->sheet_name . '!' . $range) . ':clear';
        
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => '{}',
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            gi_log_error('Clear Sheet Range Request Failed', array(
                'error' => $response->get_error_message(),
                'range' => $range
            ));
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            gi_log_error('Clear Sheet Range Failed', array(
                'response_code' => $response_code,
                'response_body' => wp_remote_retrieve_body($response),
                'range' => $range
            ));
            return false;
        }
        
        gi_log_error('Sheet range cleared successfully', array('range' => $range));
        return true;
    }
    
    /**
     * 📋 フィールドバリデーション設定のAJAXハンドラー (31列完全対応)
     */
    public function ajax_setup_field_validation() {
        // タイムアウトとメモリ制限の拡張
        set_time_limit(300); // 5分
        ini_set('memory_limit', '256M');
        
        try {
            gi_log_error('AJAX field validation setup request received', array(
                'user_id' => get_current_user_id(),
                'post_data' => $_POST
            ));
            
            // Nonce検証
            check_ajax_referer('gi_sheets_nonce', 'nonce');
            
            // 権限チェック
            if (!current_user_can('edit_posts')) {
                gi_log_error('Permission denied for field validation setup', array('user_id' => get_current_user_id()));
                wp_send_json_error('権限がありません');
                return;
            }
            
            gi_log_error('Setting up comprehensive field validation for 31-column structure');
            
            // 完全な31列フィールドマッピング情報を取得
            $field_mappings = $this->get_field_validation_mappings();
            
            // バリデーション統計情報を準備
            $validation_stats = array(
                'total_fields' => count($field_mappings),
                'field_types' => array(),
                'validation_fields' => array(),
                'taxonomy_fields' => array(),
                'readonly_fields' => array()
            );
            
            foreach ($field_mappings as $column => $field) {
                $type = $field['type'];
                $validation_stats['field_types'][$type] = ($validation_stats['field_types'][$type] ?? 0) + 1;
                
                if ($type === 'select' || $type === 'number') {
                    $validation_stats['validation_fields'][] = $column . '(' . $field['field_name'] . ')';
                }
                
                if ($type === 'taxonomy') {
                    $validation_stats['taxonomy_fields'][] = $column . '(' . $field['field_name'] . ')';
                }
                
                if ($type === 'readonly') {
                    $validation_stats['readonly_fields'][] = $column . '(' . $field['field_name'] . ')';
                }
            }
            
            // Google Apps Scriptでの設定情報
            $validation_info = array(
                'spreadsheet_id' => $this->spreadsheet_id,
                'sheet_name' => $this->sheet_name,
                'field_mappings' => $field_mappings,
                'validation_stats' => $validation_stats,
                'column_range' => 'A:AE', // 31列対応
                'setup_instructions' => array(
                    'step1' => '🔗 スプレッドシートを開く',
                    'step2' => '📋 メニューから「🏛️ 助成金管理システム」→「WordPress連携」→「🔧 フィールドバリデーション設定」を選択',
                    'step3' => '✅ 31列全体のバリデーション設定が自動実行されます',
                    'step4' => '🎨 設定完了後、選択肢フィールド（E, M, O, R, S, AB列）が青色背景で表示',
                    'step5' => '🔢 数値フィールド（I, AA列）に範囲制限が適用',
                    'step6' => '🔒 読み取り専用フィールド（A, F, G, AE列）がグレー表示',
                    'step7' => '🌐 URL フィールド（Q, X列）にリンク検証が追加'
                ),
                'validation_features' => array(
                    'dropdown_validation' => 'プルダウンメニューによる入力制限',
                    'number_validation' => '数値範囲の制限（採択率: 0-100%等）',
                    'url_validation' => 'URL形式の検証',
                    'date_validation' => '日付形式の検証',
                    'required_validation' => '必須項目の設定',
                    'readonly_protection' => '自動入力フィールドの保護'
                )
            );
            
            gi_log_error('Comprehensive field validation info prepared', array(
                'total_mappings' => count($field_mappings),
                'validation_fields' => count($validation_stats['validation_fields']),
                'taxonomy_fields' => count($validation_stats['taxonomy_fields']),
                'readonly_fields' => count($validation_stats['readonly_fields'])
            ));
            
            wp_send_json_success(array(
                'message' => '📋 31列完全対応フィールドバリデーション設定情報を準備しました',
                'validation_info' => $validation_info,
                'setup_guide' => $validation_info['setup_instructions'],
                'features' => $validation_info['validation_features'],
                'statistics' => $validation_stats
            ));
            
        } catch (Exception $e) {
            gi_log_error('Field validation setup failed', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error('フィールドバリデーション設定に失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     * 📋 フィールドマッピング & バリデーション設定 (31列完全対応)
     */
    private function get_field_validation_mappings() {
        return array(
            // 基本情報フィールド
            'A' => array(
                'field_name' => 'ID (自動入力)',
                'field_key' => 'post_id',
                'type' => 'readonly',
                'description' => 'WordPress投稿ID（自動生成・編集不可）'
            ),
            'B' => array(
                'field_name' => 'タイトル',
                'field_key' => 'post_title',
                'type' => 'text',
                'required' => true,
                'description' => '助成金名・制度名'
            ),
            'C' => array(
                'field_name' => '内容・詳細',
                'field_key' => 'post_content',
                'type' => 'textarea',
                'description' => '助成金の詳細説明・概要'
            ),
            'D' => array(
                'field_name' => '抜粋・概要',
                'field_key' => 'post_excerpt',
                'type' => 'text',
                'description' => '簡潔な概要文'
            ),
            'E' => array(
                'field_name' => 'ステータス',
                'field_key' => 'post_status',
                'type' => 'select',
                'choices' => array('draft', 'publish', 'private', 'deleted'),
                'description' => 'WordPressの投稿ステータス'
            ),
            'F' => array(
                'field_name' => '作成日 (自動入力)',
                'field_key' => 'post_date',
                'type' => 'readonly',
                'description' => 'WordPress作成日時（自動記録）'
            ),
            'G' => array(
                'field_name' => '更新日 (自動入力)',
                'field_key' => 'post_modified',
                'type' => 'readonly',
                'description' => 'WordPress更新日時（自動記録）'
            ),
            
            // 助成金詳細フィールド (H-Q)
            'H' => array(
                'field_name' => '助成金額 (表示用)',
                'field_key' => 'max_amount',
                'type' => 'text',
                'description' => '助成金額の表示用テキスト（例：300万円）'
            ),
            'I' => array(
                'field_name' => '助成金額数値',
                'field_key' => 'max_amount_numeric',
                'type' => 'number',
                'validation' => array('min' => 0, 'max' => 999999999),
                'description' => 'ソート・計算用の数値（例：3000000）'
            ),
            'J' => array(
                'field_name' => '申請期限 (表示用)',
                'field_key' => 'deadline',
                'type' => 'text',
                'description' => '申請期限の表示用テキスト（例：令和6年3月31日）'
            ),
            'K' => array(
                'field_name' => '申請期限日付',
                'field_key' => 'deadline_date',
                'type' => 'date',
                'format' => 'YYYY-MM-DD',
                'description' => 'ソート・検索用の日付形式'
            ),
            'L' => array(
                'field_name' => '実施組織名',
                'field_key' => 'organization',
                'type' => 'text',
                'description' => '助成金を実施する組織・機関名'
            ),
            'M' => array(
                'field_name' => '組織タイプ',
                'field_key' => 'organization_type', 
                'type' => 'select',
                'choices' => array('national', 'prefecture', 'city', 'public_org', 'private_org', 'foundation', 'other'),
                'description' => '実施組織の分類'
            ),
            'N' => array(
                'field_name' => '対象者・対象事業',
                'field_key' => 'grant_target',
                'type' => 'textarea',
                'description' => '助成対象となる事業・対象者の詳細'
            ),
            'O' => array(
                'field_name' => '申請方法',
                'field_key' => 'application_method',
                'type' => 'select', 
                'choices' => array('online', 'mail', 'visit', 'mixed'),
                'description' => '助成金の申請方法'
            ),
            'P' => array(
                'field_name' => '問い合わせ先',
                'field_key' => 'contact_info',
                'type' => 'textarea',
                'description' => '連絡先情報・問い合わせ窓口'
            ),
            'Q' => array(
                'field_name' => '公式URL',
                'field_key' => 'official_url',
                'type' => 'url',
                'description' => '公式サイト・詳細ページのURL'
            ),
            
            // 地域・ステータス情報 (R-S)
            'R' => array(
                'field_name' => '地域制限',
                'field_key' => 'regional_limitation',
                'type' => 'select',
                'choices' => array('nationwide', 'prefecture_only', 'municipality_only', 'region_group', 'specific_area'),
                'description' => '地域制限のタイプ'
            ),
            'S' => array(
                'field_name' => '申請ステータス',
                'field_key' => 'application_status',
                'type' => 'select',
                'choices' => array('open', 'upcoming', 'closed', 'suspended'),
                'description' => '現在の募集状況'
            ),
            
            // タクソノミー情報 (T-W) ★完全連携
            'T' => array(
                'field_name' => '都道府県',
                'field_key' => 'grant_prefecture',
                'type' => 'taxonomy',
                'taxonomy_name' => 'grant_prefecture',
                'description' => '対象都道府県（タクソノミー連携）'
            ),
            'U' => array(
                'field_name' => '市町村',
                'field_key' => 'grant_municipality',
                'type' => 'taxonomy',
                'taxonomy_name' => 'grant_municipality',
                'description' => '対象市町村（タクソノミー連携）'
            ),
            'V' => array(
                'field_name' => 'カテゴリ',
                'field_key' => 'grant_category',
                'type' => 'taxonomy',
                'taxonomy_name' => 'grant_category',
                'description' => '助成金カテゴリ（タクソノミー連携）'
            ),
            'W' => array(
                'field_name' => 'タグ',
                'field_key' => 'grant_tag',
                'type' => 'taxonomy',
                'taxonomy_name' => 'grant_tag',
                'description' => '助成金タグ（タクソノミー連携）'
            ),
            
            // ★ 新規拡張フィールド (X-AD) - 31列対応
            'X' => array(
                'field_name' => '外部リンク',
                'field_key' => 'external_link',
                'type' => 'url',
                'description' => '参考リンク・関連情報URL'
            ),
            'Y' => array(
                'field_name' => '地域に関する備考',
                'field_key' => 'area_notes',
                'type' => 'textarea',
                'description' => '地域制限の詳細説明・備考'
            ),
            'Z' => array(
                'field_name' => '必要書類',
                'field_key' => 'required_documents',
                'type' => 'textarea',
                'description' => '申請に必要な書類一覧'
            ),
            'AA' => array(
                'field_name' => '採択率（%）',
                'field_key' => 'adoption_rate',
                'type' => 'number',
                'validation' => array('min' => 0, 'max' => 100, 'step' => 0.1),
                'description' => '採択率の数値（0-100%）'
            ),
            'AB' => array(
                'field_name' => '申請難易度',
                'field_key' => 'difficulty_level',
                'type' => 'select',
                'choices' => array('easy', 'normal', 'hard', 'very_hard'),
                'description' => '申請の難易度レベル（簡単〜非常に困難）'
            ),
            'AC' => array(
                'field_name' => '対象経費',
                'field_key' => 'eligible_expenses_detailed',
                'type' => 'textarea',
                'description' => '補助対象となる経費の詳細'
            ),
            'AD' => array(
                'field_name' => '補助率',
                'field_key' => 'subsidy_rate_detailed',
                'type' => 'text',
                'description' => '補助率・補助割合（例：2/3、50%）'
            ),
            
            // システム情報 (AE)
            'AE' => array(
                'field_name' => 'シート更新日 (自動入力)',
                'field_key' => 'sheet_updated_at',
                'type' => 'readonly',
                'description' => '最終同期日時（自動記録）'
            )
        );
    }
    
    /**
     * 特定フィールドのテストAJAXハンドラー
     */
    public function ajax_test_specific_fields() {
        try {
            gi_log_error('AJAX specific field test request received', array(
                'user_id' => get_current_user_id(),
                'post_data' => $_POST
            ));
            
            // Nonce検証
            check_ajax_referer('gi_sheets_nonce', 'nonce');
            
            // 権限チェック
            if (!current_user_can('edit_posts')) {
                gi_log_error('Permission denied for specific field test', array('user_id' => get_current_user_id()));
                wp_send_json_error('権限がありません');
                return;
            }
            
            $results = $this->test_specific_field_sync();
            
            if (isset($results['error'])) {
                wp_send_json_error($results['error']);
            } else {
                wp_send_json_success($results);
            }
            
        } catch (Exception $e) {
            gi_log_error('Specific field test failed', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error('フィールドテストに失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     *  31列対応フィールド同期状態テスト
     */
    public function test_specific_field_sync() {
        $sheet_data = $this->read_sheet_data();
        
        if ($sheet_data === false) {
            return array('error' => 'スプレッドシートデータの読み取りに失敗しました');
        }
        
        if (empty($sheet_data) || count($sheet_data) < 2) {
            return array('error' => 'データ行が見つかりません');
        }
        
        // ヘッダー行を除去
        $headers = array_shift($sheet_data);
        
        // フィールドマッピング情報を取得
        $field_mappings = $this->get_field_validation_mappings();
        
        $results = array(
            'total_rows' => count($sheet_data),
            'total_columns' => count($headers),
            'headers' => $headers,
            'field_mappings_count' => count($field_mappings),
            'test_results' => array(),
            'field_analysis' => array()
        );
        
        // 重要フィールドのテスト対象を31列対応で定義
        $critical_test_fields = array(
            // 基本情報
            'post_title' => 1,              // B列: タイトル
            'post_status' => 4,             // E列: ステータス
            
            // ACFフィールド
            'max_amount' => 7,              // H列: 助成金額
            'organization_type' => 12,       // M列: 組織タイプ
            'application_method' => 14,      // O列: 申請方法
            'regional_limitation' => 17,     // R列: 地域制限
            'application_status' => 18,      // S列: 申請ステータス
            
            // 新規フィールド (31列対応)
            'external_link' => 23,          // X列: 外部リンク
            'area_notes' => 24,             // Y列: 地域に関する備考
            'required_documents_detailed' => 25,  // Z列: 必要書類
            'adoption_rate' => 26,          // AA列: 採択率
            'difficulty_level' => 27,        // AB列: 申請難易度
            'eligible_expenses_detailed' => 28, // AC列: 対象経費
            'subsidy_rate_detailed' => 29,  // AD列: 補助率
        );
        
        // タクソノミーフィールドのテスト
        $taxonomy_fields = array(
            'grant_prefecture' => 19,       // T列: 都道府県
            'grant_municipality' => 20,     // U列: 市町村
            'grant_category' => 21,         // V列: カテゴリ
            'grant_tag' => 22,              // W列: タグ
        );
        
        // 最初の3行をテスト（処理時間を考慮）
        foreach (array_slice($sheet_data, 0, 3) as $index => $row) {
            $post_id = intval($row[0] ?? 0);
            
            if (!$post_id || !get_post($post_id)) {
                continue;
            }
            
            $row_result = array(
                'post_id' => $post_id,
                'post_title' => get_the_title($post_id),
                'sheet_row' => $index + 2,
                'acf_fields' => array(),
                'taxonomy_fields' => array(),
                'sync_status' => array()
            );
            
            // ACFフィールドの同期状態をテスト
            foreach ($critical_test_fields as $field_key => $column_index) {
                $sheet_value = $row[$column_index] ?? '';
                
                if ($field_key === 'post_title') {
                    $wp_value = get_the_title($post_id);
                } elseif ($field_key === 'post_status') {
                    $wp_value = get_post_status($post_id);
                } else {
                    $wp_value = get_field($field_key, $post_id);
                }
                
                $column_letter = $this->number_to_column($column_index + 1);
                
                $row_result['acf_fields'][$field_key] = array(
                    'column' => $column_letter,
                    'column_index' => $column_index,
                    'sheet_value' => $sheet_value,
                    'wp_value' => $wp_value,
                    'matches' => (string)$sheet_value === (string)$wp_value,
                    'sheet_empty' => empty($sheet_value),
                    'wp_empty' => empty($wp_value),
                    'field_type' => $field_mappings[$column_letter]['type'] ?? 'unknown'
                );
            }
            
            // タクソノミーフィールドの同期状態をテスト
            foreach ($taxonomy_fields as $taxonomy => $column_index) {
                $sheet_value = $row[$column_index] ?? '';
                $wp_terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));
                $wp_value = is_array($wp_terms) && !is_wp_error($wp_terms) ? implode(', ', $wp_terms) : '';
                
                $column_letter = $this->number_to_column($column_index + 1);
                
                $row_result['taxonomy_fields'][$taxonomy] = array(
                    'column' => $column_letter,
                    'column_index' => $column_index,
                    'sheet_value' => $sheet_value,
                    'wp_value' => $wp_value,
                    'matches' => $sheet_value === $wp_value,
                    'sheet_empty' => empty($sheet_value),
                    'wp_empty' => empty($wp_value),
                    'terms_count' => is_array($wp_terms) ? count($wp_terms) : 0
                );
            }
            
            // 同期状態の統計
            $total_tested = count($row_result['acf_fields']) + count($row_result['taxonomy_fields']);
            $matched_fields = 0;
            
            foreach ($row_result['acf_fields'] as $field_data) {
                if ($field_data['matches']) $matched_fields++;
            }
            
            foreach ($row_result['taxonomy_fields'] as $field_data) {
                if ($field_data['matches']) $matched_fields++;
            }
            
            $row_result['sync_status'] = array(
                'total_tested' => $total_tested,
                'matched_fields' => $matched_fields,
                'sync_rate' => $total_tested > 0 ? round(($matched_fields / $total_tested) * 100, 2) : 0,
                'has_issues' => $matched_fields < $total_tested
            );
            
            $results['test_results'][] = $row_result;
        }
        
        // フィールド分析統計を追加
        $results['field_analysis'] = array(
            'tested_acf_fields' => count($critical_test_fields),
            'tested_taxonomy_fields' => count($taxonomy_fields),
            'total_columns_available' => 31, // AE列まで
            'coverage_percentage' => round(((count($critical_test_fields) + count($taxonomy_fields)) / 31) * 100, 2)
        );
        
        return $results;
    }
    
    /**
     * 数値を列文字に変換（1=A, 2=B, ..., 27=AA, 28=AB, etc.）
     */
    private function number_to_column($number) {
        $column = '';
        while ($number > 0) {
            $number--;
            $column = chr(65 + ($number % 26)) . $column;
            $number = intval($number / 26);
        }
        return $column;
    }
    
    // 自動同期設定メソッドは削除されました - 手動同期のみ
    
    // 自動同期設定AJAXハンドラーは削除されました - 手動同期のみ
    
    // Cronスケジュール機能は削除されました - 手動同期のみ
    
    /**
     * 同期結果をログに記録
     */
    private function log_sync_result($sync_type, $result, $message = '') {
        try {
            // 同期時刻を記録
            update_option('gi_sheets_last_sync_time', current_time('mysql'));
            update_option('gi_sheets_last_sync_result', $result);
            
            // 日次カウント
            $today = date('Y-m-d');
            $count_key = 'gi_sheets_sync_count_' . $today;
            $current_count = get_option($count_key, 0);
            update_option($count_key, $current_count + 1);
            
            // 詳細ログ
            gi_log_error('Sync result logged', array(
                'type' => $sync_type,
                'result' => $result,
                'message' => $message,
                'timestamp' => current_time('mysql'),
                'daily_count' => $current_count + 1
            ));
            
            // 古いログの清理（30日以上前）
            $cleanup_date = date('Y-m-d', strtotime('-30 days'));
            $old_count_key = 'gi_sheets_sync_count_' . $cleanup_date;
            delete_option($old_count_key);
            
        } catch (Exception $e) {
            gi_log_error('Failed to log sync result', array(
                'error' => $e->getMessage()
            ));
        }
    }
}

// Cronスケジュールは削除されました

/**
 * タクソノミータームを自動作成して設定するヘルパー関数
 * 
 * @param int $post_id 投稿ID
 * @param array|string $terms タームの配列または文字列
 * @param string $taxonomy タクソノミー名
 * @return array|WP_Error 設定されたタームIDの配列、またはエラー
 */
function gi_set_terms_with_auto_create($post_id, $terms, $taxonomy) {
    if (empty($terms)) {
        return wp_set_post_terms($post_id, array(), $taxonomy);
    }
    
    // 文字列の場合は配列に変換
    if (!is_array($terms)) {
        $terms = array($terms);
    }
    
    $term_ids = array();
    
    foreach ($terms as $term_name) {
        $term_name = trim($term_name);
        if (empty($term_name)) {
            continue;
        }
        
        // タームが存在するか確認
        $existing_term = term_exists($term_name, $taxonomy);
        
        if ($existing_term) {
            // 既存のタームIDを使用
            $term_ids[] = (int) $existing_term['term_id'];
        } else {
            // 新しいタームを作成
            $new_term = wp_insert_term($term_name, $taxonomy);
            
            if (!is_wp_error($new_term)) {
                $term_ids[] = (int) $new_term['term_id'];
                
                gi_log_error('New taxonomy term created', array(
                    'taxonomy' => $taxonomy,
                    'term_name' => $term_name,
                    'term_id' => $new_term['term_id']
                ));
            } else {
                gi_log_error('Failed to create taxonomy term', array(
                    'taxonomy' => $taxonomy,
                    'term_name' => $term_name,
                    'error' => $new_term->get_error_message()
                ));
            }
        }
    }
    
    // タームIDの配列で投稿に設定
    return wp_set_post_terms($post_id, $term_ids, $taxonomy);
}

// インスタンスを初期化
function gi_init_google_sheets_sync() {
    return GoogleSheetsSync::getInstance();
}

// テーマ読み込み時に初期化
add_action('init', 'gi_init_google_sheets_sync');

// ==================== WEBHOOK HANDLER ====================

class SheetsWebhookHandler {
    
    private static $instance = null;
    private $webhook_secret;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->webhook_secret = $this->get_or_generate_webhook_secret();
        $this->add_hooks();
    }
    
    /**
     * WordPressフックの追加
     */
    private function add_hooks() {
        // Webhook エンドポイント
        add_action('init', array($this, 'handle_webhook_request'));
        
        // REST API エンドポイント
        add_action('rest_api_init', array($this, 'register_webhook_endpoint'));
        
        // 管理画面にWebhook URL表示
        add_action('admin_notices', array($this, 'show_webhook_setup_notice'));
    }
    
    /**
     * Webhook シークレットを取得または生成
     */
    private function get_or_generate_webhook_secret() {
        $secret = get_option('gi_sheets_webhook_secret');
        
        if (!$secret) {
            $secret = wp_generate_password(32, false);
            update_option('gi_sheets_webhook_secret', $secret);
        }
        
        return $secret;
    }
    
    /**
     * Webhook リクエストの処理
     */
    public function handle_webhook_request() {
        // 特定のクエリパラメータをチェック
        if (!isset($_GET['gi_sheets_webhook']) || $_GET['gi_sheets_webhook'] !== 'true') {
            return;
        }
        
        // POST リクエストのみ受け付け
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            wp_die('Method Not Allowed', '', array('response' => 405));
        }
        
        // JSON データを取得
        $raw_data = file_get_contents('php://input');
        $data = json_decode($raw_data, true);
        
        if (!$data) {
            http_response_code(400);
            wp_die('Invalid JSON', '', array('response' => 400));
        }
        
        // セキュリティ検証
        if (!$this->verify_webhook_security($data)) {
            http_response_code(403);
            wp_die('Forbidden', '', array('response' => 403));
        }
        
        // Webhook データを処理
        $result = $this->process_webhook_data($data);
        
        if ($result['success']) {
            http_response_code(200);
            wp_send_json_success($result['message']);
        } else {
            http_response_code(500);
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * REST API エンドポイントの登録
     */
    public function register_webhook_endpoint() {
        register_rest_route('gi/v1', '/sheets-webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_webhook_handler'),
            'permission_callback' => '__return_true', // セキュリティは独自に検証
        ));
        
        // Google Apps Script用のエクスポートエンドポイント
        register_rest_route('gi/v1', '/export-grants', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_grants_handler'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * REST API Webhook ハンドラー
     */
    public function rest_webhook_handler($request) {
        $data = $request->get_json_params();
        
        if (!$data) {
            return new WP_Error('invalid_json', 'Invalid JSON data', array('status' => 400));
        }
        
        // セキュリティ検証
        if (!$this->verify_webhook_security($data)) {
            return new WP_Error('forbidden', 'Security verification failed', array('status' => 403));
        }
        
        // データを処理
        $result = $this->process_webhook_data($data);
        
        if ($result['success']) {
            return rest_ensure_response(array(
                'success' => true,
                'message' => $result['message']
            ));
        } else {
            return new WP_Error('processing_failed', $result['message'], array('status' => 500));
        }
    }
    
    /**
     * Webhook セキュリティ検証
     */
    private function verify_webhook_security($data) {
        // 必須フィールドの確認
        if (!isset($data['timestamp']) || !isset($data['signature']) || !isset($data['payload'])) {
            return false;
        }
        
        // タイムスタンプ検証（5分以内のリクエストのみ受け付け）
        $current_time = time();
        $request_time = intval($data['timestamp']);
        
        if (abs($current_time - $request_time) > 300) { // 5分
            return false;
        }
        
        // 署名検証
        $payload_string = json_encode($data['payload']);
        $expected_signature = hash_hmac('sha256', $request_time . $payload_string, $this->webhook_secret);
        
        return hash_equals($expected_signature, $data['signature']);
    }
    
    /**
     * Webhook データの処理
     */
    private function process_webhook_data($data) {
        try {
            $payload = $data['payload'];
            
            // アクションタイプに基づいて処理分岐
            switch ($payload['action']) {
                case 'row_updated':
                    return $this->handle_row_update($payload);
                    
                case 'row_added':
                    return $this->handle_row_add($payload);
                    
                case 'row_deleted':
                    return $this->handle_row_delete($payload);
                    
                case 'bulk_update':
                    return $this->handle_bulk_update($payload);
                    
                default:
                    return array(
                        'success' => false,
                        'message' => 'Unknown action: ' . $payload['action']
                    );
            }
            
        } catch (Exception $e) {
            // ログに記録
            gi_log_error('Webhook processing failed', array(
                'error' => $e->getMessage(),
                'data' => $data
            ));
            
            return array(
                'success' => false,
                'message' => 'Processing failed: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * 行更新の処理
     */
    private function handle_row_update($payload) {
        if (!isset($payload['row_data']) || !isset($payload['row_number'])) {
            return array('success' => false, 'message' => 'Missing row data');
        }
        
        $row_data = $payload['row_data'];
        $post_id = intval($row_data[0]); // A列はID
        
        // 既存投稿の確認
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'grant') {
            return array('success' => false, 'message' => 'Post not found');
        }
        
        // 投稿データを更新
        $updated_post = array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field($row_data[1]),
            'post_content' => wp_kses_post($row_data[2]),
            'post_excerpt' => sanitize_textarea_field($row_data[3]),
            'post_status' => sanitize_text_field($row_data[4]),
        );
        
        $result = wp_update_post($updated_post);
        
        if (is_wp_error($result)) {
            return array('success' => false, 'message' => $result->get_error_message());
        }
        
        // ACFフィールドを更新
        $this->update_acf_fields($post_id, $row_data);
        
        // 完全なタクソノミー統合処理
        $this->update_taxonomies_complete($post_id, $row_data);
        
        // ログ追加
        if (class_exists('SheetsAdminUI') && method_exists('SheetsAdminUI', 'add_log_entry')) {
            SheetsAdminUI::add_log_entry("投稿 ID:{$post_id} をWebhookで更新しました", 'success');
        }
        
        return array(
            'success' => true,
            'message' => "Post {$post_id} updated successfully"
        );
    }
    
    /**
     * 新規行追加の処理
     */
    private function handle_row_add($payload) {
        if (!isset($payload['row_data'])) {
            return array('success' => false, 'message' => 'Missing row data');
        }
        
        $row_data = $payload['row_data'];
        
        // 新規投稿を作成
        $new_post = array(
            'post_title' => sanitize_text_field($row_data[1]),
            'post_content' => wp_kses_post($row_data[2]),
            'post_excerpt' => sanitize_textarea_field($row_data[3]),
            'post_status' => sanitize_text_field($row_data[4]),
            'post_type' => 'grant'
        );
        
        $post_id = wp_insert_post($new_post);
        
        if (is_wp_error($post_id)) {
            return array('success' => false, 'message' => $post_id->get_error_message());
        }
        
        // ACFフィールドを設定
        $this->update_acf_fields($post_id, $row_data);
        
        // 完全なタクソノミー統合処理
        $this->update_taxonomies_complete($post_id, $row_data);
        
        // スプレッドシートにIDを書き戻し（非同期で実行）
        wp_schedule_single_event(time() + 10, 'gi_update_sheet_id', array($post_id, $payload['row_number']));
        
        // ログ追加
        if (class_exists('SheetsAdminUI') && method_exists('SheetsAdminUI', 'add_log_entry')) {
            SheetsAdminUI::add_log_entry("投稿 ID:{$post_id} をWebhookで作成しました", 'success');
        }
        
        return array(
            'success' => true,
            'message' => "Post {$post_id} created successfully"
        );
    }
    
    /**
     * 行削除の処理
     */
    private function handle_row_delete($payload) {
        if (!isset($payload['post_id'])) {
            return array('success' => false, 'message' => 'Missing post ID');
        }
        
        $post_id = intval($payload['post_id']);
        
        // 投稿の存在確認
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'grant') {
            return array('success' => false, 'message' => 'Post not found');
        }
        
        // 投稿を削除
        $result = wp_delete_post($post_id, true);
        
        if (!$result) {
            return array('success' => false, 'message' => 'Failed to delete post');
        }
        
        // ログ追加
        if (class_exists('SheetsAdminUI') && method_exists('SheetsAdminUI', 'add_log_entry')) {
            SheetsAdminUI::add_log_entry("投稿 ID:{$post_id} をWebhookで削除しました", 'success');
        }
        
        return array(
            'success' => true,
            'message' => "Post {$post_id} deleted successfully"
        );
    }
    
    /**
     * 一括更新の処理
     */
    private function handle_bulk_update($payload) {
        if (!isset($payload['updates']) || !is_array($payload['updates'])) {
            return array('success' => false, 'message' => 'Missing updates data');
        }
        
        $success_count = 0;
        $error_count = 0;
        
        foreach ($payload['updates'] as $update) {
            try {
                switch ($update['action']) {
                    case 'update':
                        $result = $this->handle_row_update($update);
                        break;
                    case 'add':
                        $result = $this->handle_row_add($update);
                        break;
                    case 'delete':
                        $result = $this->handle_row_delete($update);
                        break;
                    default:
                        continue 2; // 次のループへ
                }
                
                if ($result['success']) {
                    $success_count++;
                } else {
                    $error_count++;
                }
                
            } catch (Exception $e) {
                $error_count++;
                gi_log_error('Bulk update item failed', array(
                    'error' => $e->getMessage(),
                    'update' => $update
                ));
            }
        }
        
        // ログ追加
        if (class_exists('SheetsAdminUI') && method_exists('SheetsAdminUI', 'add_log_entry')) {
            SheetsAdminUI::add_log_entry("一括更新完了: 成功 {$success_count}件, エラー {$error_count}件", 'info');
        }
        
        return array(
            'success' => true,
            'message' => "Bulk update completed: {$success_count} success, {$error_count} errors"
        );
    }
    
    /**
     * ACFフィールドの更新
     * 31列完全対応版 (A-AE列)
     */
    private function update_acf_fields($post_id, $row_data) {
        // 完全な31列対応マッピング (Google Apps Scriptと整合)
        $acf_mapping = array(
            // 基本情報 (A-G列はWordPressのpost_*フィールドで処理)
            // 助成金詳細情報 (H-N列)
            7  => 'max_amount',              // H列: 助成金額（表示用）
            8  => 'max_amount_numeric',      // I列: 助成金額（数値）
            9  => 'deadline',               // J列: 申請期限（表示用）
            10 => 'deadline_date',          // K列: 申請期限（日付）
            11 => 'organization',           // L列: 実施組織
            12 => 'organization_type',      // M列: 組織タイプ
            13 => 'grant_target',           // N列: 対象者・対象事業
            
            // 申請・連絡情報 (O-S列)
            14 => 'application_method',     // O列: 申請方法
            15 => 'contact_info',           // P列: 問い合わせ先
            16 => 'official_url',           // Q列: 公式URL
            17 => 'regional_limitation',    // R列: 地域制限
            18 => 'application_status',     // S列: 申請ステータス
            
            // タクソノミー情報 (T-W列は update_taxonomies_complete()で処理)
            // 19 => 都道府県 (T列) - タクソノミーで処理、ACFフィールド削除
            // 20 => 市町村 (U列) - タクソノミーで処理、ACFフィールド削除  
            // 21 => カテゴリ (V列) - grant_category タクソノミーで処理
            // 22 => タグ (W列) - grant_tag タクソノミーで処理
            
            // ★新規追加フィールド (X-AD列)
            23 => 'external_link',          // X列: 外部リンク
            24 => 'area_notes',             // Y列: 地域に関する備考
            25 => 'required_documents_detailed', // Z列: 必要書類（詳細）
            26 => 'adoption_rate',          // AA列: 採択率（%）
            27 => 'difficulty_level',       // AB列: 申請難易度
            28 => 'eligible_expenses_detailed', // AC列: 対象経費（詳細）
            29 => 'subsidy_rate_detailed',  // AD列: 補助率（詳細）
            // AE列(30): シート更新日 - システム情報のため処理しない
        );
        
        foreach ($acf_mapping as $col_index => $field_name) {
            if (isset($row_data[$col_index])) {
                $value = $row_data[$col_index];
                
                // 特別な処理が必要なフィールド
                switch ($field_name) {
                    case 'max_amount_numeric':
                    case 'adoption_rate':
                        // 数値フィールドの処理
                        $value = is_numeric($value) ? floatval($value) : 0;
                        break;
                        
                    case 'deadline_date':
                        // 日付フィールドの処理
                        if (!empty($value) && $value !== '0000-00-00') {
                            // 日付形式を統一
                            $timestamp = strtotime($value);
                            if ($timestamp !== false) {
                                $value = date('Y-m-d', $timestamp);
                            }
                        }
                        break;
                        
                    case 'official_url':
                    case 'external_link':
                        // URL フィールドの検証
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                            // 無効なURLの場合は空にする
                            $value = '';
                        }
                        break;
                        
                    case 'organization_type':
                        // 組織タイプのデフォルト値設定
                        if (empty($value)) {
                            $value = 'national';
                        }
                        break;
                        
                    case 'application_method':
                        // 申請方法のデフォルト値設定
                        if (empty($value)) {
                            $value = 'online';
                        }
                        break;
                        
                    case 'regional_limitation':
                        // 地域制限のデフォルト値設定
                        if (empty($value)) {
                            $value = 'nationwide';
                        }
                        break;
                        
                    case 'application_status':
                        // 申請ステータスのデフォルト値設定
                        if (empty($value)) {
                            $value = 'open';
                        }
                        break;
                        
                    case 'difficulty_level':
                        // 申請難易度のデフォルト値設定
                        if (empty($value)) {
                            $value = '中級';
                        }
                        break;
                }
                
                // JSON文字列の場合はデコード
                if (is_string($value) && ($decoded = json_decode($value, true)) !== null) {
                    $value = $decoded;
                }
                
                // ACFフィールドを更新
                update_field($field_name, $value, $post_id);
            }
        }
        
        // 新規フィールドの後処理
        $this->post_process_new_fields($post_id, $row_data);
    }
    
    /**
     * 新規追加フィールドの後処理
     */
    private function post_process_new_fields($post_id, $row_data) {
        // 採択率の%記号処理
        if (isset($row_data[26])) { // AA列: 採択率
            $adoption_rate = floatval($row_data[26]);
            if ($adoption_rate > 0) {
                // メタ情報として%付きの表示用値も保存
                update_post_meta($post_id, '_adoption_rate_display', $adoption_rate . '%');
            }
        }
        
        // 地域制限と地域備考の連携処理
        if (isset($row_data[17]) && isset($row_data[24])) { // R列とY列
            $regional_limitation = $row_data[17];
            $area_notes = $row_data[24];
            
            // 地域制限が特定地域の場合、備考を強調表示用メタとして保存
            if (in_array($regional_limitation, ['prefecture_only', 'municipality_only', 'specific_area']) && !empty($area_notes)) {
                update_post_meta($post_id, '_regional_highlight', true);
            }
        }
        
        // 必要書類の構造化処理
        if (isset($row_data[25])) { // Z列: 必要書類
            $documents = $row_data[25];
            if (!empty($documents)) {
                // カンマ区切りの場合は配列に変換
                if (is_string($documents) && strpos($documents, ',') !== false) {
                    $documents_array = array_map('trim', explode(',', $documents));
                    update_post_meta($post_id, '_required_documents_list', $documents_array);
                }
            }
        }
    }
    
    /**
     * 完全なタクソノミー統合処理（31列対応）
     */
    private function update_taxonomies_complete($post_id, $row_data) {
        // 都道府県タクソノミー（T列 = インデックス19） + 自動作成
        if (isset($row_data[19])) {
            $prefectures = array_filter(array_map('trim', explode(',', (string)$row_data[19])), 'strlen');
            gi_set_terms_with_auto_create($post_id, $prefectures, 'grant_prefecture');
            // 重複ACFフィールドを削除
            delete_field('target_prefecture', $post_id);
            delete_field('prefecture_name', $post_id);
        }
        
        // 市町村タクソノミー（U列 = インデックス20） + 自動作成
        if (isset($row_data[20])) {
            $municipalities = array_filter(array_map('trim', explode(',', (string)$row_data[20])), 'strlen');
            gi_set_terms_with_auto_create($post_id, $municipalities, 'grant_municipality');
            // 重複ACFフィールドを削除
            delete_field('target_municipality', $post_id);
        }
        
        // カテゴリ（V列 = インデックス21） + 自動作成
        if (isset($row_data[21])) {
            $categories = array_filter(array_map('trim', explode(',', (string)$row_data[21])), 'strlen');
            gi_set_terms_with_auto_create($post_id, $categories, 'grant_category');
        }
        
        // タグ（W列 = インデックス22） + 自動作成
        if (isset($row_data[22])) {
            $tags = array_filter(array_map('trim', explode(',', (string)$row_data[22])), 'strlen');
            gi_set_terms_with_auto_create($post_id, $tags, 'grant_tag');
        }
        
        // タクソノミー統合ログ
        if (class_exists('SheetsAdminUI') && method_exists('SheetsAdminUI', 'add_log_entry')) {
            SheetsAdminUI::add_log_entry("投稿 ID:{$post_id} のタクソノミー統合が完了しました", 'success');
        }
    }
    
    /**
     * Webhook URL を取得
     */
    public function get_webhook_url() {
        return home_url('/?gi_sheets_webhook=true');
    }
    
    /**
     * REST API Webhook URL を取得
     */
    public function get_rest_webhook_url() {
        return rest_url('gi/v1/sheets-webhook');
    }
    
    /**
     * Webhook シークレットを取得
     */
    public function get_webhook_secret() {
        return $this->webhook_secret;
    }
    
    /**
     * 管理画面にWebhook設定通知を表示
     */
    public function show_webhook_setup_notice() {
        $screen = get_current_screen();
        
        // Sheets設定ページでのみ表示
        if (!$screen || strpos($screen->id, 'grant-sheets-sync') === false) {
            return;
        }
        
        $webhook_url = $this->get_webhook_url();
        $rest_webhook_url = $this->get_rest_webhook_url();
        $secret = $this->get_webhook_secret();
        
        ?>
        <div class="notice notice-info">
            <h3>Google Apps Script Webhook設定</h3>
            <p>リアルタイム同期を有効にするため、以下の情報をGoogle Apps Scriptに設定してください：</p>
            <ul>
                <li><strong>Webhook URL:</strong> <code><?php echo esc_html($webhook_url); ?></code></li>
                <li><strong>REST API URL:</strong> <code><?php echo esc_html($rest_webhook_url); ?></code></li>
                <li><strong>Secret Key:</strong> <code><?php echo esc_html($secret); ?></code></li>
            </ul>
            <p>
                <a href="#" onclick="navigator.clipboard.writeText('<?php echo esc_js($webhook_url); ?>'); alert('Webhook URLをコピーしました');" class="button button-secondary">Webhook URLをコピー</a>
                <a href="#" onclick="navigator.clipboard.writeText('<?php echo esc_js($secret); ?>'); alert('Secret Keyをコピーしました');" class="button button-secondary">Secret Keyをコピー</a>
            </p>
        </div>
        <?php
    }
    
    /**
     * 助成金データエクスポートハンドラー（Google Apps Script用）
     */
    public function export_grants_handler($request) {
        try {
            // 助成金投稿を取得
            $posts = get_posts(array(
                'post_type' => 'grant',
                'post_status' => array('publish', 'draft', 'private'),
                'numberposts' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            
            $exported_data = array();
            
            foreach ($posts as $post) {
                $post_id = $post->ID;
                
                // 投稿データをスプレッドシート形式に変換
                $row_data = $this->convert_post_to_export_row($post_id);
                if ($row_data) {
                    $exported_data[] = $row_data;
                }
            }
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => 'Posts exported successfully',
                'count' => count($exported_data),
                'data' => $exported_data
            ));
            
        } catch (Exception $e) {
            return new WP_Error('export_failed', $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * 投稿データをエクスポート用の行データに変換
     */
    private function convert_post_to_export_row($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'grant') {
            return false;
        }

        $prefectures = wp_get_post_terms($post_id, 'grant_prefecture', array('fields' => 'names'));
        $municipalities = wp_get_post_terms($post_id, 'grant_municipality', array('fields' => 'names'));
        $categories = wp_get_post_terms($post_id, 'grant_category', array('fields' => 'names'));
        $tags = wp_get_post_terms($post_id, 'grant_tag', array('fields' => 'names'));

        $max_amount = get_field('max_amount', $post_id);
        $max_amount_numeric = get_field('max_amount_numeric', $post_id);
        $deadline_display = get_field('deadline', $post_id);
        $deadline_date = get_field('deadline_date', $post_id);
        $organization = get_field('organization', $post_id);
        $organization_type = get_field('organization_type', $post_id) ?: 'national';
        $grant_target = get_field('grant_target', $post_id);
        $application_method = get_field('application_method', $post_id) ?: 'online';
        $contact_info = get_field('contact_info', $post_id);
        $official_url = get_field('official_url', $post_id);
        $regional_limitation = get_field('regional_limitation', $post_id) ?: 'nationwide';
        $application_status = get_field('application_status', $post_id) ?: 'open';
        $external_link = get_field('external_link', $post_id);
        $area_notes = get_field('area_notes', $post_id);
        $required_documents = get_field('required_documents_detailed', $post_id);
        $adoption_rate = get_field('adoption_rate', $post_id);
        $difficulty_level = get_field('difficulty_level', $post_id) ?: '中級';
        $eligible_expenses = get_field('eligible_expenses_detailed', $post_id);
        $subsidy_rate = get_field('subsidy_rate_detailed', $post_id);

        $prefecture_value = (is_array($prefectures) && !is_wp_error($prefectures)) ? implode(', ', $prefectures) : '';
        $municipality_value = (is_array($municipalities) && !is_wp_error($municipalities)) ? implode(', ', $municipalities) : '';
        $category_value = (is_array($categories) && !is_wp_error($categories)) ? implode(', ', $categories) : '';
        $tag_value = (is_array($tags) && !is_wp_error($tags)) ? implode(', ', $tags) : '';

        return array(
            $post_id,                                                    // A: ID
            $post->post_title,                                           // B: タイトル
            wp_strip_all_tags($post->post_content),                      // C: 内容（HTML除去）
            $post->post_excerpt,                                         // D: 抜粋
            $post->post_status,                                          // E: ステータス
            $post->post_date,                                            // F: 作成日
            $post->post_modified,                                        // G: 更新日
            $max_amount ?: '',                                           // H: 助成金額（表示用）
            ($max_amount_numeric !== null && $max_amount_numeric !== '') ? $max_amount_numeric : '', // I: 助成金額（数値）
            $deadline_display ?: '',                                     // J: 申請期限（表示用）
            $deadline_date ?: '',                                        // K: 申請期限（日付）
            $organization ?: '',                                         // L: 実施組織
            $organization_type,                                          // M: 組織タイプ
            $grant_target ?: '',                                         // N: 対象者・対象事業
            $application_method,                                         // O: 申請方法
            $contact_info ?: '',                                         // P: 問い合わせ先
            $official_url ?: '',                                         // Q: 公式URL
            $regional_limitation,                                        // R: 地域制限
            $application_status,                                         // S: 申請ステータス
            $prefecture_value,                                           // T: 都道府県（タクソノミー）
            $municipality_value,                                         // U: 市町村（タクソノミー）
            $category_value,                                             // V: カテゴリ
            $tag_value,                                                  // W: タグ
            $external_link ?: '',                                        // X: 外部リンク
            $area_notes ?: '',                                           // Y: 地域備考
            $required_documents ?: '',                                   // Z: 必要書類
            ($adoption_rate !== null && $adoption_rate !== '') ? $adoption_rate : '', // AA: 採択率
            $difficulty_level,                                           // AB: 申請難易度
            $eligible_expenses ?: '',                                    // AC: 対象経費
            $subsidy_rate ?: '',                                         // AD: 補助率
            current_time('mysql')                                        // AE: シート更新日
        );
    }
    
    /**
     * スプレッドシートIDの書き戻しアクション
     */
    public function update_sheet_id_callback($post_id, $row_number) {
        $sheets_sync = GoogleSheetsSync::getInstance();
        
        // スプレッドシートのA列にIDを書き込み
        $range = "grant_import!A{$row_number}";
        $sheets_sync->write_sheet_data($range, array(array($post_id)));
        
        // ログ追加
        if (class_exists('SheetsAdminUI') && method_exists('SheetsAdminUI', 'add_log_entry')) {
            SheetsAdminUI::add_log_entry("投稿ID {$post_id} をスプレッドシートに書き戻しました", 'info');
        }
    }
}

// スプレッドシートID書き戻しのアクション登録
add_action('gi_update_sheet_id', array('SheetsWebhookHandler', 'update_sheet_id_callback'), 10, 2);

// インスタンスを初期化
function gi_init_sheets_webhook() {
    return SheetsWebhookHandler::getInstance();
}
add_action('init', 'gi_init_sheets_webhook', 5);

// ==================== INITIALIZER ====================

class SheetsInitializer {
    
    private static $instance = null;
    private $sheets_sync;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // GoogleSheetsSync インスタンスを取得
        add_action('init', array($this, 'init_after_sheets_sync'), 15);
        
        // AJAX ハンドラー
        add_action('wp_ajax_gi_initialize_sheet', array($this, 'ajax_initialize_sheet'));
        add_action('wp_ajax_gi_export_all_posts', array($this, 'ajax_export_all_posts'));
    }
    
    /**
     * Sheets同期後の初期化
     */
    public function init_after_sheets_sync() {
        $this->sheets_sync = GoogleSheetsSync::getInstance();
    }
    
    /**
     * スプレッドシートの初期化
     */
    public function initialize_sheet() {
        try {
            gi_log_error('Starting sheet initialization process');
            
            // Sheets Syncインスタンスの確認
            if (!$this->sheets_sync) {
                gi_log_error('SheetsSync instance not available, attempting to get instance');
                if (class_exists('GoogleSheetsSync')) {
                    $this->sheets_sync = GoogleSheetsSync::getInstance();
                    gi_log_error('SheetsSync instance obtained');
                } else {
                    throw new Exception('GoogleSheetsSync クラスが利用できません');
                }
            }
            
            // 1. ヘッダー行を設定
            gi_log_error('Step 1: Setting up headers');
            $this->setup_headers();
            gi_log_error('Headers setup completed');
            
            // 2. バリデーションルールを設定
            gi_log_error('Step 2: Setting up validation rules');
            $this->setup_validation_rules();
            gi_log_error('Validation rules setup completed');
            
            // 3. 既存の投稿データをエクスポート
            gi_log_error('Step 3: Exporting existing posts');
            $this->export_existing_posts();
            gi_log_error('Existing posts export completed');
            
            // 4. フォーマット設定
            gi_log_error('Step 4: Setting up formatting');
            $this->setup_formatting();
            gi_log_error('Formatting setup completed');
            
            gi_log_error('Sheet initialization completed successfully');
            return array('success' => true, 'message' => 'スプレッドシートの初期化が完了しました');
            
        } catch (Exception $e) {
            gi_log_error('Sheet initialization failed', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            
            return array('success' => false, 'message' => '初期化に失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     * ヘッダー行の設定
     */
    private function setup_headers() {
        $headers = array(
            'ID (自動入力)' => 'WordPress投稿ID（自動入力）',
            'タイトル' => '助成金名・タイトル',
            '内容・詳細' => '助成金の詳細説明（HTML可）',
            '抜粋・概要' => '一覧表示用の簡潔な概要',
            'ステータス (draft/publish/private)' => '投稿ステータス（publish/draft/private/deleted）',
            '作成日 (自動入力)' => '投稿作成日時（自動入力）',
            '更新日 (自動入力)' => '投稿更新日時（自動入力）',
            '助成金額 (例: 300万円)' => '表示用の助成金額',
            '助成金額数値 (例: 3000000)' => '数値での助成金額（円）',
            '申請期限 (例: 令和6年3月31日)' => '表示用の申請期限',
            '申請期限日付 (YYYY-MM-DD)' => 'YYYY-MM-DD形式の締切日',
            '実施組織名' => '助成金を実施する組織名',
            '組織タイプ (national/prefecture/city/public_org/private_org/other)' => '組織のタイプ',
            '対象者・対象事業' => '助成対象の詳細',
            '申請方法 (online/mail/visit/mixed)' => '申請方法',
            '問い合わせ先' => '連絡先情報',
            '公式URL' => '公式サイトURL',
            '地域制限 (nationwide/prefecture_only/municipality_only/region_group/specific_area)' => '地域制限のタイプ',
            '申請ステータス (open/upcoming/closed/suspended)' => '募集状況',
            '都道府県 (例: 東京都)' => '対象となる都道府県名（複数可、カンマ区切り）',
            '市町村 (例: 新宿区,渋谷区)' => '対象となる市区町村（カンマ区切り）',
            'カテゴリ (例: ビジネス支援,IT関連)' => 'grant_categoryタクソノミー名（複数可）',
            'タグ (例: スタートアップ,中小企業)' => 'grant_tagタクソノミー名（複数可）',
            '外部リンク' => '関連する外部リンクURL',
            '地域に関する備考' => '地域制限や対象地域に関する補足',
            '必要書類' => '申請に必要な書類（複数はカンマ区切り）',
            '採択率（%）' => '採択率（0-100）',
            '申請難易度 (easy/normal/hard/very_hard)' => '申請難易度の目安',
            '対象経費' => '助成対象となる経費の詳細',
            '補助率 (例: 2/3, 50%)' => '補助率・補助割合の詳細',
            'シート更新日 (自動入力)' => 'スプレッドシート最終更新日時（自動入力）'
        );

        // ヘッダー行を書き込み
        $header_values = array_keys($headers);
        $sheet_name = $this->sheets_sync->get_sheet_name();
        $result = $this->sheets_sync->write_sheet_data(
            $sheet_name . '!A1:AE1',
            array($header_values)
        );

        if (!$result) {
            throw new Exception('ヘッダー行の設定に失敗しました');
        }

        // 2行目に説明を追加
        $descriptions = array_values($headers);
        $this->sheets_sync->write_sheet_data(
            $sheet_name . '!A2:AE2',
            array($descriptions)
        );

        return true;
    }
    
    /**
     * バリデーションルールの設定（Google Sheets API v4では制限あり）
     */
    private function setup_validation_rules() {
        // Google Sheets APIでは高度なバリデーション設定が難しいため、サンプル行で想定値を提示
        $sample_row = array(
            '', // ID（自動入力）
            'サンプル助成金タイトル', // タイトル
            'この助成金の詳細な説明をここに記載します。', // 内容
            '短い概要説明', // 抜粋
            'draft', // ステータス
            '', // 作成日（自動入力）
            '', // 更新日（自動入力）
            '最大100万円', // 助成金額（表示用）
            '1000000', // 助成金額（数値）
            '2024年12月31日', // 申請期限（表示用）
            '2024-12-31', // 申請期限（日付）
            '◯◯財団', // 実施組織
            'public_org', // 組織タイプ
            '中小企業向け地域振興事業', // 対象者・対象事業
            'online', // 申請方法
            'info@example.org', // 問い合わせ先
            'https://example.org', // 公式URL
            'prefecture_only', // 地域制限
            'open', // 申請ステータス
            '東京都', // 都道府県
            '新宿区, 渋谷区', // 市町村
            '地域振興, 社会貢献', // カテゴリ
            'NPO, 助成金', // タグ
            'https://external.example.com', // 外部リンク
            '東京都内の中小企業が対象', // 地域備考
            '事業計画書, 決算書', // 必要書類
            '85', // 採択率
            '中級', // 申請難易度
            '人件費, 設備費', // 対象経費
            '1/2以内', // 補助率
            '' // シート更新日（自動入力）
        );

        $sheet_name = $this->sheets_sync->get_sheet_name();
        $this->sheets_sync->write_sheet_data(
            $sheet_name . '!A3:AE3',
            array($sample_row)
        );

        return true;
    }
    
    /**
     * 既存投稿のエクスポート
     */
    private function export_existing_posts() {
        $posts = get_posts(array(
            'post_type' => 'grant',
            'post_status' => array('publish', 'draft', 'private'),
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if (empty($posts)) {
            return true; // 投稿がない場合はそのまま成功
        }
        
        $rows = array();
        $start_row = 4; // 4行目から開始（ヘッダー、説明、サンプルの後）
        
        foreach ($posts as $post) {
            $row_data = $this->convert_post_to_row($post->ID);
            if ($row_data) {
                $rows[] = $row_data;
            }
        }
        
        if (!empty($rows)) {
            // 一括で書き込み
            $end_row = $start_row + count($rows) - 1;
            $sheet_name = $this->sheets_sync->get_sheet_name();
            $range = $sheet_name . "!A{$start_row}:AE{$end_row}";
            
            $result = $this->sheets_sync->write_sheet_data($range, $rows);
            
            if (!$result) {
                throw new Exception('既存投稿のエクスポートに失敗しました');
            }
        }
        
        return true;
    }
    
    /**
     * 投稿データを行データに変換
     */
    private function convert_post_to_row($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'grant') {
            return false;
        }

        $prefectures = wp_get_post_terms($post_id, 'grant_prefecture', array('fields' => 'names'));
        $municipalities = wp_get_post_terms($post_id, 'grant_municipality', array('fields' => 'names'));
        $categories = wp_get_post_terms($post_id, 'grant_category', array('fields' => 'names'));
        $tags = wp_get_post_terms($post_id, 'grant_tag', array('fields' => 'names'));

        $max_amount = get_field('max_amount', $post_id);
        $max_amount_numeric = get_field('max_amount_numeric', $post_id);
        $deadline_display = get_field('deadline', $post_id);
        $deadline_date = get_field('deadline_date', $post_id);
        $organization = get_field('organization', $post_id);
        $organization_type = get_field('organization_type', $post_id) ?: 'national';
        $grant_target = get_field('grant_target', $post_id);
        $application_method = get_field('application_method', $post_id) ?: 'online';
        $contact_info = get_field('contact_info', $post_id);
        $official_url = get_field('official_url', $post_id);
        $regional_limitation = get_field('regional_limitation', $post_id) ?: 'nationwide';
        $application_status = get_field('application_status', $post_id) ?: 'open';
        $external_link = get_field('external_link', $post_id);
        $area_notes = get_field('area_notes', $post_id);
        $required_documents = get_field('required_documents_detailed', $post_id);
        $adoption_rate = get_field('adoption_rate', $post_id);
        $difficulty_level = get_field('difficulty_level', $post_id) ?: '中級';
        $eligible_expenses = get_field('eligible_expenses_detailed', $post_id);
        $subsidy_rate = get_field('subsidy_rate_detailed', $post_id);

        $prefecture_value = is_array($prefectures) && !is_wp_error($prefectures) ? implode(', ', $prefectures) : '';
        $municipality_value = is_array($municipalities) && !is_wp_error($municipalities) ? implode(', ', $municipalities) : '';
        $category_value = is_array($categories) && !is_wp_error($categories) ? implode(', ', $categories) : '';
        $tag_value = is_array($tags) && !is_wp_error($tags) ? implode(', ', $tags) : '';

        $row = array(
            $post_id,
            $post->post_title,
            $post->post_content,
            $post->post_excerpt,
            $post->post_status,
            $post->post_date,
            $post->post_modified,
            $max_amount ?: '',
            ($max_amount_numeric !== null && $max_amount_numeric !== '') ? $max_amount_numeric : '',
            $deadline_display ?: '',
            $deadline_date ?: '',
            $organization ?: '',
            $organization_type,
            $grant_target ?: '',
            $application_method,
            $contact_info ?: '',
            $official_url ?: '',
            $regional_limitation,
            $application_status,
            $prefecture_value,
            $municipality_value,
            $category_value,
            $tag_value,
            $external_link ?: '',
            $area_notes ?: '',
            $required_documents ?: '',
            ($adoption_rate !== null && $adoption_rate !== '') ? $adoption_rate : '',
            $difficulty_level,
            $eligible_expenses ?: '',
            $subsidy_rate ?: '',
            current_time('mysql')
        );

        return $row;
    }
    
    /**
     * フォーマット設定
     */
    private function setup_formatting() {
        // Google Sheets API v4では詳細なフォーマット設定は制限的
        // 基本的なフォーマットのみ設定可能
        
        // 今回は省略（将来的にはGoogle Apps Scriptで実装を推奨）
        return true;
    }
    
    /**
     * 統計情報の取得
     */
    public function get_sync_stats() {
        // WordPress側の統計
        $wp_posts_count = wp_count_posts('grant');
        
        // スプレッドシート側の統計を取得
        $sheet_data = $this->sheets_sync->read_sheet_data();
        $sheet_rows_count = is_array($sheet_data) ? count($sheet_data) - 1 : 0; // ヘッダー行を除外
        
        return array(
            'wordpress' => array(
                'publish' => $wp_posts_count->publish ?? 0,
                'draft' => $wp_posts_count->draft ?? 0,
                'private' => $wp_posts_count->private ?? 0,
                'total' => ($wp_posts_count->publish ?? 0) + ($wp_posts_count->draft ?? 0) + ($wp_posts_count->private ?? 0)
            ),
            'spreadsheet' => array(
                'total_rows' => $sheet_rows_count,
                'last_updated' => get_option('gi_sheets_last_sync', '未同期')
            ),
            'sync_status' => array(
                'auto_sync_enabled' => get_option('gi_sheets_config', array())['auto_sync_enabled'] ?? true,
                'last_sync' => get_option('gi_sheets_last_full_sync', '未実行'),
                'errors_count' => count(get_option('gi_sheets_sync_log', array()))
            )
        );
    }
    
    /**
     * AJAX: スプレッドシート初期化
     */
    public function ajax_initialize_sheet() {
        // タイムアウトとメモリ制限の拡張
        set_time_limit(300); // 5分
        ini_set('memory_limit', '256M');
        
        try {
            gi_log_error('AJAX initialize_sheet started', array(
                'user_id' => get_current_user_id(),
                'post_data' => $_POST
            ));
            
            // Nonce検証
            check_ajax_referer('gi_sheets_nonce', 'nonce');
            gi_log_error('Nonce verification passed for initialization');
            
            // 権限チェック
            if (!current_user_can('edit_posts')) {
                gi_log_error('Permission denied for initialization', array('user_id' => get_current_user_id()));
                wp_send_json_error('Permission denied');
                return;
            }
            
            gi_log_error('Starting sheet initialization');
            
            // 初期化処理を実行
            $result = $this->initialize_sheet();
            
            gi_log_error('Sheet initialization result', $result);
            
            if ($result && isset($result['success']) && $result['success']) {
                wp_send_json_success($result['message']);
            } else {
                $error_message = isset($result['message']) ? $result['message'] : '初期化に失敗しました';
                wp_send_json_error($error_message);
            }
            
        } catch (Exception $e) {
            gi_log_error('AJAX initialize_sheet exception caught', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error('初期化中にエラーが発生しました: ' . $e->getMessage());
            
        } catch (Error $e) {
            gi_log_error('AJAX initialize_sheet fatal error caught', array(
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error('初期化中に致命的エラーが発生しました: ' . $e->getMessage());
            
        } catch (Throwable $e) {
            gi_log_error('AJAX initialize_sheet throwable caught', array(
                'error' => $e->getMessage(),
                'file' => method_exists($e, 'getFile') ? $e->getFile() : 'unknown',
                'line' => method_exists($e, 'getLine') ? $e->getLine() : 'unknown',
                'trace' => method_exists($e, 'getTraceAsString') ? $e->getTraceAsString() : 'no trace'
            ));
            wp_send_json_error('初期化中に予期しないエラーが発生しました: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX: 全投稿エクスポート
     */
    public function ajax_export_all_posts() {
        check_ajax_referer('gi_sheets_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        try {
            $this->export_existing_posts();
            wp_send_json_success('全投稿をスプレッドシートにエクスポートしました');
            
        } catch (Exception $e) {
            wp_send_json_error('エクスポートに失敗しました: ' . $e->getMessage());
        }
    }
    
    /**
     * スプレッドシートをクリア
     */
    public function clear_sheet() {
        try {
            gi_log_error('Starting sheet clear process');
            
            // Sheets Syncインスタンスの確認
            if (!$this->sheets_sync) {
                if (class_exists('GoogleSheetsSync')) {
                    $this->sheets_sync = GoogleSheetsSync::getInstance();
                } else {
                    throw new Exception('GoogleSheetsSync クラスが利用できません');
                }
            }
            
            // スプレッドシートのデータをクリア（ヘッダー行は残す）
            $range = 'A2:AE1000'; // 31列、1000行までクリア
            $clear_data = $this->sheets_sync->clear_sheet_range($range);
            
            if ($clear_data) {
                gi_log_error('Sheet clear completed successfully');
                return array(
                    'success' => true,
                    'message' => 'スプレッドシートのデータをクリアしました'
                );
            } else {
                throw new Exception('スプレッドシートのクリアに失敗しました');
            }
            
        } catch (Exception $e) {
            gi_log_error('Sheet clear failed', array('error' => $e->getMessage()));
            return array(
                'success' => false,
                'message' => 'クリアに失敗しました: ' . $e->getMessage()
            );
        }
    }
}

// AJAX ハンドラーを追加
add_action('wp_ajax_gi_clear_sheet', function() {
    check_ajax_referer('gi_sheets_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }
    
    $initializer = SheetsInitializer::getInstance();
    $result = $initializer->clear_sheet();
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
});

// インスタンスを初期化
function gi_init_sheets_initializer() {
    return SheetsInitializer::getInstance();
}
add_action('init', 'gi_init_sheets_initializer');

// ==================== ADMIN UI ====================

class SheetsAdminUI {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        try {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('admin_init', array($this, 'register_settings'));
        } catch (Exception $e) {
            error_log('SheetsAdminUI constructor error: ' . $e->getMessage());
        }
    }
    
    /**
     * 管理画面メニューに追加
     */
    public function add_admin_menu() {
        // デバッグ用: 必ず設定メニューの下に追加
        add_options_page(
            'Google Sheets連携',
            'Sheets連携',
            'edit_posts', // 権限を緩和
            'grant-sheets-sync',
            array($this, 'admin_page')
        );
        
        // 助成金投稿タイプが存在する場合は、そちらにも追加
        if (post_type_exists('grant')) {
            add_submenu_page(
                'edit.php?post_type=grant',
                'Google Sheets連携',
                'Sheets連携',
                'edit_posts', // 権限を緩和
                'grant-sheets-sync-grant',
                array($this, 'admin_page')
            );
        }
    }
    
    /**
     * 管理画面用スクリプトとスタイル
     */
    public function enqueue_admin_scripts($hook) {
        
        if (strpos($hook, 'grant-sheets-sync') === false) {
            return;
        }
        
        // JavaScriptファイルのパスを確認
        $js_path = get_template_directory_uri() . '/assets/js/sheets-admin.js';
        $js_file_path = get_template_directory() . '/assets/js/sheets-admin.js';
        
        
        wp_enqueue_script(
            'gi-sheets-admin',
            $js_path,
            array('jquery'),
            GI_THEME_VERSION . '-' . time(), // Cache busting
            true
        );
        
        $localize_data = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gi_sheets_nonce'),
            'debug' => true, // デバッグモード追加
            'strings' => array(
                'testing' => '接続をテスト中...',
                'syncing' => '同期中...',
                'success' => '成功',
                'error' => 'エラー',
                'confirm_sync' => '同期を実行しますか？この操作により既存のデータが上書きされる可能性があります。'
            )
        );
        
        
        wp_localize_script('gi-sheets-admin', 'giSheetsAdmin', $localize_data);
        
        // CSSファイルも確認
        $css_path = get_template_directory_uri() . '/assets/css/sheets-admin.css';
        $css_file_path = get_template_directory() . '/assets/css/sheets-admin.css';
        
        
        wp_enqueue_style(
            'gi-sheets-admin-style',
            $css_path,
            array(),
            GI_THEME_VERSION
        );
    }
    
    /**
     * 設定の登録（自動同期機能は削除済み）
     */
    public function register_settings() {
        // 自動同期設定は削除されました - 手動同期のみ利用可能
    }
    
    /**
     * 管理画面のメインページ
     */
    public function admin_page() {
        // エラーハンドリングでページ全体を保護
        try {
        ?>
        <div class="wrap">
            <h1>Google Sheets連携設定</h1>
            
            <!-- 接続状態カード -->
            <div class="gi-sheets-card">
                <h2>接続状態</h2>
                <div id="connection-status" class="gi-status-unknown">
                    <span class="gi-status-indicator"></span>
                    <span class="gi-status-text">未確認</span>
                </div>
                <p>
                    <button type="button" id="test-connection" class="button">接続をテスト</button>
                </p>
                
                <div class="gi-connection-info">
                    <h4>スプレッドシート情報</h4>
                    <p><strong>スプレッドシートID:</strong> 1kGc1Eb4AYvURkSfdzMwipNjfe8xC6iGCM2q1sUgIfWg</p>
                    <p><strong>シート名:</strong> grant_import</p>
                    <p><strong>サービスアカウント:</strong> grant-sheets-service@grant-sheets-integration.iam.gserviceaccount.com</p>
                    <p><a href="https://docs.google.com/spreadsheets/d/1kGc1Eb4AYvURkSfdzMwipNjfe8xC6iGCM2q1sUgIfWg/edit#gid=706632810" target="_blank" class="button button-secondary">スプレッドシートを開く</a></p>
                </div>
            </div>
            
            <!-- 手動同期カード -->
            <div class="gi-sheets-card">
                <h2>手動同期</h2>
                <div class="gi-sync-controls">
                    <div class="gi-sync-option">
                        <button type="button" class="button button-primary gi-sync-btn" data-direction="both">
                            完全同期（双方向）
                        </button>
                        <p class="description">WordPressとスプレッドシートの両方向で同期します。</p>
                    </div>
                    
                    <div class="gi-sync-option">
                        <button type="button" class="button gi-sync-btn" data-direction="wp_to_sheets">
                            WordPress → Sheets
                        </button>
                        <p class="description">WordPressの投稿をスプレッドシートに反映します。</p>
                    </div>
                    
                    <div class="gi-sync-option">
                        <button type="button" class="button gi-sync-btn" data-direction="sheets_to_wp">
                            Sheets → WordPress
                        </button>
                        <p class="description">スプレッドシートの変更をWordPressに反映します。</p>
                    </div>
                    
                    <div class="gi-sync-option" style="border-top: 1px solid #ddd; margin-top: 15px; padding-top: 15px;">
                        <button type="button" class="button button-secondary" id="test-specific-fields">
                            🔍 フィールド同期テスト
                        </button>
                        <p class="description">都道府県・カテゴリ・対象市町村フィールドの同期状態をテストします。</p>
                    </div>
                </div>
                
                <div id="sync-result" style="display: none;">
                    <div class="notice">
                        <p id="sync-message"></p>
                    </div>
                </div>
                
                <div id="field-test-result" style="display: none;">
                    <div class="notice">
                        <div id="field-test-content"></div>
                    </div>
                </div>
            </div>
            
            <!-- スプレッドシート初期化カード -->
            <div class="gi-sheets-card">
                <h2>スプレッドシート初期化</h2>
                <div class="gi-init-controls">
                    <p class="description">
                        スプレッドシートにヘッダー行を設定し、既存の投稿データをエクスポートします。
                    </p>
                    <div class="gi-init-actions">
                        <button type="button" id="initialize-sheet" class="button button-primary">
                            スプレッドシートを初期化
                        </button>
                        <button type="button" id="export-all-posts" class="button button-secondary">
                            全投稿をエクスポート
                        </button>
                        <button type="button" id="clear-sheet" class="button button-secondary gi-danger">
                            スプレッドシートをクリア
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- フィールドバリデーション設定カード -->
            <div class="gi-sheets-card">
                <h2>📋 フィールドマッピング & バリデーション設定（31列完全対応）</h2>
                <div class="gi-validation-info">
                    <p class="description">
                        <strong> 31列完全対応</strong>：スプレッドシートの選択肢フィールドにプルダウンメニューを設定して、入力ミスを防ぎます。<br>
                        <span class="description">A列-AE列まで31列すべてのフィールドマッピングが完了し、タクソノミー連携（都道府県・市町村・カテゴリ・タグ）と新規拡張フィールドに対応しています。</span>
                    </p>
                    
                    <div class="gi-field-mapping">
                        <h4> フィールドマッピング一覧</h4>
                        <table class="widefat" style="margin: 10px 0;">
                            <thead>
                                <tr>
                                    <th>列</th>
                                    <th>フィールド名（日本語）</th>
                                    <th>英語キー</th>
                                    <th>選択肢・説明</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>E列</strong></td>
                                    <td>ステータス</td>
                                    <td><code>post_status</code></td>
                                    <td>draft（下書き）/ publish（公開）/ private（非公開）/ deleted（削除）</td>
                                </tr>
                                <tr style="background: #f9f9f9;">
                                    <td><strong>M列</strong></td>
                                    <td>組織タイプ</td>
                                    <td><code>organization_type</code></td>
                                    <td>national（国・省庁）/ prefecture（都道府県）/ city（市区町村）/ public_org（公的機関）/ private_org（民間団体）/ foundation（財団法人）/ other（その他）</td>
                                </tr>
                                <tr>
                                    <td><strong>O列</strong></td>
                                    <td>申請方法</td>
                                    <td><code>application_method</code></td>
                                    <td>online（オンライン申請）/ mail（郵送申請）/ visit（窓口申請）/ mixed（オンライン・郵送併用）</td>
                                </tr>
                                <tr style="background: #f9f9f9;">
                                    <td><strong>R列</strong></td>
                                    <td>地域制限</td>
                                    <td><code>regional_limitation</code></td>
                                    <td>nationwide（全国対象）/ prefecture_only（都道府県内限定）/ municipality_only（市町村限定）/ region_group（地域グループ限定）/ specific_area（特定地域限定）</td>
                                </tr>
                                <tr style="background: #f9f9f9;">
                                    <td><strong>S列</strong></td>
                                    <td>申請ステータス</td>
                                    <td><code>application_status</code></td>
                                    <td>open（募集中）/ upcoming（募集予定）/ closed（募集終了）/ suspended（一時停止）</td>
                                </tr>
                                <tr style="background: #e8f5e8;">
                                    <td><strong>T列 完全連携</strong></td>
                                    <td>🏛️ 都道府県</td>
                                    <td><code>grant_prefecture</code></td>
                                    <td>北海道、東京都、大阪府等（タクソノミー、カンマ区切り可能）</td>
                                </tr>
                                <tr style="background: #e8f5e8;">
                                    <td><strong>U列 完全連携</strong></td>
                                    <td>🏘️ 市町村</td>
                                    <td><code>grant_municipality</code></td>
                                    <td>新宿区、渋谷区、札幌市等（タクソノミー、カンマ区切り可能）</td>
                                </tr>
                                <tr style="background: #e8f5e8;">
                                    <td><strong>V列 完全連携</strong></td>
                                    <td>📂 カテゴリ</td>
                                    <td><code>grant_category</code></td>
                                    <td>創業支援、研究開発、地域活性化等（タクソノミー、カンマ区切り可能）</td>
                                </tr>
                                <tr style="background: #e8f5e8;">
                                    <td><strong>W列 完全連携</strong></td>
                                    <td>🏷️ タグ</td>
                                    <td><code>grant_tag</code></td>
                                    <td>スタートアップ、AI、環境等（WordPressタグ、カンマ区切り可能）</td>
                                </tr>
                                <tr style="background: #fff8dc;">
                                    <td><strong>X列 新規</strong></td>
                                    <td>🔗 外部リンク</td>
                                    <td><code>external_link</code></td>
                                    <td>参考URL、関連サイトのリンク（URL形式）</td>
                                </tr>
                                <tr style="background: #fff8dc;">
                                    <td><strong>Y列 新規</strong></td>
                                    <td>📍 地域に関する備考</td>
                                    <td><code>area_notes</code></td>
                                    <td>地域制限の詳細説明・特記事項</td>
                                </tr>
                                <tr style="background: #fff8dc;">
                                    <td><strong>Z列 新規</strong></td>
                                    <td>📋 必要書類（詳細）</td>
                                    <td><code>required_documents_detailed</code></td>
                                    <td>申請に必要な書類の詳細リスト</td>
                                </tr>
                                <tr style="background: #fff8dc;">
                                    <td><strong>AA列 新規</strong></td>
                                    <td> 採択率（%）</td>
                                    <td><code>adoption_rate</code></td>
                                    <td>0-100の数値（過去実績に基づく採択率）</td>
                                </tr>
                                <tr style="background: #fff8dc;">
                                    <td><strong>AB列 新規</strong></td>
                                    <td>⚡ 申請難易度</td>
                                    <td><code>difficulty_level</code></td>
                                    <td>初級 / 中級 / 上級 / 非常に高い</td>
                                </tr>
                                <tr style="background: #fff8dc;">
                                    <td><strong>AC列 新規</strong></td>
                                    <td> 対象経費（詳細）</td>
                                    <td><code>eligible_expenses_detailed</code></td>
                                    <td>補助対象となる経費の詳細説明</td>
                                </tr>
                                <tr style="background: #fff8dc;">
                                    <td><strong>AD列 新規</strong></td>
                                    <td>💸 補助率（詳細）</td>
                                    <td><code>subsidy_rate_detailed</code></td>
                                    <td>補助率の詳細（例：1/2以内、上限100万円）</td>
                                </tr>
                                <tr style="background: #f0f0f0;">
                                    <td><strong>AE列</strong></td>
                                    <td>⏰ シート更新日</td>
                                    <td><code>sheet_updated</code></td>
                                    <td>システム自動入力（編集不可）</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="gi-validation-setup">
                        <h4>🔧 プルダウン設定手順</h4>
                        <ol>
                            <li><strong>Step 1:</strong> 下のボタンでバリデーション情報を準備</li>
                            <li><strong>Step 2:</strong> Googleスプレッドシートを開く</li>
                            <li><strong>Step 3:</strong> メニューから「🏛️ 助成金管理システム」→「WordPress連携」→「🔧 フィールドバリデーション設定」を選択</li>
                            <li><strong>Step 4:</strong> 選択肢フィールドの背景が薄い青色になり、プルダウンメニューが使用可能になります</li>
                        </ol>
                        
                        <div style="margin: 15px 0;">
                            <button type="button" id="setup-field-validation" class="button button-primary">
                                🔧 フィールドバリデーション設定を準備
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="validation-result" style="display: none;">
                    <div class="notice">
                        <p id="validation-message"></p>
                    </div>
                </div>
            </div>
            
            <!-- Webhook設定カード -->
            <div class="gi-sheets-card">
                <h2>リアルタイム同期（Webhook）</h2>
                <div class="gi-webhook-info">
                    <p>Google Apps Scriptを設定することで、スプレッドシートの変更をリアルタイムでWordPressに反映できます。</p>
                    
                    <?php
                    // Webhookハンドラーが利用可能かチェック
                    $webhook_url = home_url('/?gi_sheets_webhook=true');
                    $rest_webhook_url = rest_url('gi/v1/sheets-webhook');
                    $secret = wp_generate_password(32, false);
                    
                    try {
                        if (class_exists('SheetsWebhookHandler')) {
                            $webhook_handler = SheetsWebhookHandler::getInstance();
                            if (method_exists($webhook_handler, 'get_webhook_url')) {
                                $webhook_url = $webhook_handler->get_webhook_url();
                                $rest_webhook_url = $webhook_handler->get_rest_webhook_url();
                                $secret = $webhook_handler->get_webhook_secret();
                            }
                        }
                    } catch (Exception $e) {

                    }
                    ?>
                    
                    <div class="gi-webhook-config">
                        <h4>Google Apps Script設定値</h4>
                        <table class="form-table">
                            <tr>
                                <th>Webhook URL</th>
                                <td>
                                    <input type="text" value="<?php echo esc_attr($webhook_url); ?>" readonly style="width: 100%;">
                                    <button type="button" class="button button-small gi-copy-btn" data-copy="<?php echo esc_attr($webhook_url); ?>">コピー</button>
                                </td>
                            </tr>
                            <tr>
                                <th>REST API URL (推奨)</th>
                                <td>
                                    <input type="text" value="<?php echo esc_attr($rest_webhook_url); ?>" readonly style="width: 100%;">
                                    <button type="button" class="button button-small gi-copy-btn" data-copy="<?php echo esc_attr($rest_webhook_url); ?>">コピー</button>
                                </td>
                            </tr>
                            <tr>
                                <th>Secret Key</th>
                                <td>
                                    <input type="password" value="<?php echo esc_attr($secret); ?>" readonly style="width: 100%;">
                                    <button type="button" class="button button-small gi-copy-btn" data-copy="<?php echo esc_attr($secret); ?>">コピー</button>
                                    <button type="button" class="button button-small" onclick="this.previousElementSibling.previousElementSibling.type='text'">表示</button>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="gi-gas-setup">
                            <h4>セットアップ手順</h4>
                            <ol>
                                <li><a href="https://script.google.com" target="_blank">Google Apps Script</a> で新しいプロジェクトを作成</li>
                                <li>提供されたコード（SheetSync.gs）をコピー＆ペースト</li>
                                <li>上記の設定値をコードの CONFIG オブジェクトに設定</li>
                                <li>setupTriggers() 関数を実行してトリガーを設定</li>
                                <li>testConnection() 関数で接続をテスト</li>
                            </ol>
                            
                            <p>
                                <a href="<?php echo esc_url(get_template_directory_uri() . '/google-apps-script/SheetSync.gs'); ?>" class="button button-secondary" download>Google Apps Scriptコードをダウンロード</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 同期設定カード -->
            <div class="gi-sheets-card">
                <h2>自動同期設定</h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('gi_sheets_settings');
                    do_settings_sections('gi_sheets_settings');
                    submit_button('設定を保存');
                    ?>
                </form>
            </div>
            
            <!-- 同期ログカード -->
            <div class="gi-sheets-card">
                <h2>同期ログ</h2>
                <div id="sync-log">
                    <?php $this->display_sync_log(); ?>
                </div>
                <p>
                    <button type="button" id="refresh-log" class="button button-secondary">ログを更新</button>
                    <button type="button" id="clear-log" class="button button-secondary">ログをクリア</button>
                </p>
            </div>
            
            <!-- フィールドマッピングカード -->
            <div class="gi-sheets-card">
                <h2>フィールドマッピング</h2>
                <div class="gi-mapping-info">
                    <h4>スプレッドシートの列構成</h4>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>列</th>
                                <th>フィールド</th>
                                <th>説明</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>A</td><td>ID</td><td>WordPress投稿ID</td></tr>
                            <tr><td>B</td><td>タイトル</td><td>投稿のタイトル</td></tr>
                            <tr><td>C</td><td>内容</td><td>投稿の本文</td></tr>
                            <tr><td>D</td><td>抜粋</td><td>投稿の抜粋</td></tr>
                            <tr><td>E</td><td>ステータス</td><td>publish / draft / private / deleted</td></tr>
                            <tr><td>F</td><td>作成日</td><td>投稿作成日時</td></tr>
                            <tr><td>G</td><td>更新日</td><td>投稿更新日時</td></tr>
                            <tr><td>H</td><td>助成金額（表示用）</td><td>ACF: max_amount</td></tr>
                            <tr><td>I</td><td>助成金額（数値）</td><td>ACF: max_amount_numeric</td></tr>
                            <tr><td>J</td><td>申請期限（表示用）</td><td>ACF: deadline</td></tr>
                            <tr><td>K</td><td>申請期限（日付）</td><td>ACF: deadline_date</td></tr>
                            <tr><td>L</td><td>実施組織</td><td>ACF: organization</td></tr>
                            <tr><td>M</td><td>組織タイプ</td><td>ACF: organization_type</td></tr>
                            <tr><td>N</td><td>対象者・対象事業</td><td>ACF: grant_target</td></tr>
                            <tr><td>O</td><td>申請方法</td><td>ACF: application_method</td></tr>
                            <tr><td>P</td><td>問い合わせ先</td><td>ACF: contact_info</td></tr>
                            <tr><td>Q</td><td>公式URL</td><td>ACF: official_url</td></tr>
                            <tr><td>R</td><td>都道府県コード</td><td>ACF: target_prefecture</td></tr>
                            <tr><td>S</td><td>都道府県名</td><td>ACF: prefecture_name</td></tr>
                            <tr><td>T</td><td>対象市町村</td><td>ACF: target_municipality</td></tr>
                            <tr><td>U</td><td>地域制限</td><td>ACF: regional_limitation</td></tr>
                            <tr><td>V</td><td>申請ステータス</td><td>ACF: application_status</td></tr>
                            <tr><td>W</td><td>カテゴリ</td><td>カンマ区切りのカテゴリ名</td></tr>
                            <tr><td>X</td><td>タグ</td><td>カンマ区切りのタグ名</td></tr>
                            <tr><td>Y</td><td>シート更新日</td><td>スプレッドシート最終更新日時</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- 使用方法カード -->
            <div class="gi-sheets-card">
                <h2>スプレッドシートでの投稿管理方法</h2>
                <div class="gi-usage-guide">
                    <h4>新規投稿の作成</h4>
                    <ol>
                        <li>スプレッドシートの最下行に新しい行を追加</li>
                        <li>A列（ID）は空欄のままにする（自動的に割り当てられます）</li>
                        <li>B列以降に投稿データを入力</li>
                        <li>E列のステータスを「publish」「draft」「private」のいずれかに設定</li>
                        <li>手動同期でWordPressに反映</li>
                    </ol>
                    
                    <h4>既存投稿の編集</h4>
                    <ol>
                        <li>該当する投稿のIDを確認</li>
                        <li>その行の内容を編集</li>
                        <li>手動同期でWordPressに反映</li>
                    </ol>
                    
                    <h4>投稿の削除</h4>
                    <ol>
                        <li>該当する投稿のE列（ステータス）を「deleted」に変更</li>
                        <li>手動同期でWordPressから削除</li>
                    </ol>
                    
                    <div class="notice notice-info">
                        <p><strong>注意:</strong> スプレッドシートから行を削除してもWordPressからは削除されません。ステータスを「deleted」に変更してください。</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        } catch (Exception $e) {
            // エラーが発生した場合の表示
            echo '<div class="wrap">';
            echo '<h1>Google Sheets連携設定</h1>';
            echo '<div class="notice notice-error">';
            echo '<p><strong>エラーが発生しました:</strong> ' . esc_html($e->getMessage()) . '</p>';
            echo '<p>管理者にお問い合わせください。</p>';
            echo '</div>';
            echo '</div>';
            

        }
    }
    
    /**
     * 設定セクションのコールバック
     */
    public function settings_section_callback() {
        echo '<p>自動同期の設定を行います。</p>';
    }
    
    // 自動同期設定メソッドは削除されました - 手動同期のみ
    
    /**
     * 同期ログを表示
     */
    private function display_sync_log() {
        // Repair any existing log data issues
        self::repair_log_data();
        
        $logs = get_option('gi_sheets_sync_log', array());
        
        if (empty($logs)) {
            echo '<p>まだログがありません。</p>';
            return;
        }
        
        // 最新10件のログを表示
        $logs = array_slice($logs, -10);
        $logs = array_reverse($logs);
        
        echo '<div class="gi-log-container">';
        foreach ($logs as $log) {
            $class = 'gi-log-' . esc_attr($log['level']);
            
            // Safely handle timestamp conversion
            $timestamp = $log['timestamp'];
            if (is_string($timestamp)) {
                // If it's already a formatted date string, use it directly
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $timestamp)) {
                    $time = $timestamp;
                } else {
                    // Try to convert string timestamp to int
                    $timestamp = (int) $timestamp;
                    $time = $timestamp > 0 ? date('Y-m-d H:i:s', $timestamp) : 'Invalid Date';
                }
            } else {
                // Handle integer timestamp
                $time = is_numeric($timestamp) && $timestamp > 0 ? date('Y-m-d H:i:s', (int)$timestamp) : 'Invalid Date';
            }
            
            echo '<div class="gi-log-entry ' . $class . '">';
            echo '<span class="gi-log-time">' . esc_html($time) . '</span>';
            echo '<span class="gi-log-message">' . esc_html($log['message']) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * ログエントリを追加
     */
    public static function add_log_entry($message, $level = 'info') {
        $logs = get_option('gi_sheets_sync_log', array());
        
        // Clean up any existing log entries with invalid timestamps
        $logs = array_filter($logs, function($log) {
            return isset($log['timestamp']) && isset($log['level']) && isset($log['message']);
        });
        
        // Ensure all existing timestamps are integers
        foreach ($logs as &$log) {
            if (is_string($log['timestamp']) && is_numeric($log['timestamp'])) {
                $log['timestamp'] = (int) $log['timestamp'];
            } elseif (!is_int($log['timestamp'])) {
                $log['timestamp'] = time(); // Fallback to current time
            }
        }
        unset($log); // Break reference
        
        $logs[] = array(
            'timestamp' => time(),
            'level' => $level,
            'message' => $message
        );
        
        // 最大100件のログを保持
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('gi_sheets_sync_log', $logs);
    }
    
    /**
     * ログをクリア
     */
    public function clear_log() {
        delete_option('gi_sheets_sync_log');
    }
    
    /**
     * ログデータを修復（既存の不正なタイムスタンプを修正）
     */
    public static function repair_log_data() {
        $logs = get_option('gi_sheets_sync_log', array());
        $repaired = false;
        
        foreach ($logs as &$log) {
            if (isset($log['timestamp']) && is_string($log['timestamp'])) {
                if (is_numeric($log['timestamp'])) {
                    $log['timestamp'] = (int) $log['timestamp'];
                    $repaired = true;
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $log['timestamp'])) {
                    // Convert date string to timestamp
                    $log['timestamp'] = strtotime($log['timestamp']);
                    $repaired = true;
                } else {
                    // Invalid timestamp, use current time
                    $log['timestamp'] = time();
                    $repaired = true;
                }
            }
        }
        unset($log); // Break reference
        
        if ($repaired) {
            update_option('gi_sheets_sync_log', $logs);
        }
        
        return $repaired;
    }
}

// AJAX ハンドラーを追加
add_action('wp_ajax_gi_clear_sheets_log', function() {
    check_ajax_referer('gi_sheets_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }
    
    delete_option('gi_sheets_sync_log');
    wp_send_json_success('ログをクリアしました。');
});

// インスタンスを初期化
function gi_init_sheets_admin_ui() {
    return SheetsAdminUI::getInstance();
}

// デバッグ用: メニュー追加の確認通知
add_action('admin_notices', function() {
    if (current_user_can('edit_posts') && !isset($_GET['page'])) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>Google Sheets連携:</strong> ';
        echo '設定は「<a href="' . admin_url('options-general.php?page=grant-sheets-sync') . '">設定 → Sheets連携</a>」から利用できます。';
        if (post_type_exists('grant')) {
            echo ' または「<a href="' . admin_url('edit.php?post_type=grant&page=grant-sheets-sync-grant') . '">助成金 → Sheets連携</a>」からもアクセスできます。';
        }
        echo '</p></div>';
    }
});

// 管理画面でのみ初期化 - より安全な方法
if (is_admin()) {
    // WordPressが完全に初期化された後に実行
    add_action('admin_init', function() {
        try {
            if (function_exists('gi_init_sheets_admin_ui') && class_exists('SheetsAdminUI')) {
                gi_init_sheets_admin_ui();
            }
        } catch (Exception $e) {

        }
    }, 10);

    // フォールバック用のエラー処理付き初期化
    add_action('wp_loaded', function() {
        try {
            if (!class_exists('SheetsAdminUI')) {

                return;
            }
            
            // 確実に初期化させるためのセカンダリートリガー
            SheetsAdminUI::getInstance();
            
        } catch (Exception $e) {

        }
    }, 20);
}