/**
 * Google Sheets Admin JavaScript
 * 
 * 管理画面でのGoogle Sheets連携機能
 * - 接続テスト
 * - 手動同期
 * - ログ管理
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initSheetsAdmin();
    });
    
    function initSheetsAdmin() {
        // 接続テストボタン
        $('#test-connection').on('click', testConnection);
        
        // 手動同期ボタン
        $('.gi-sync-btn').on('click', handleManualSync);
        
        // ログ操作ボタン
        $('#refresh-log').on('click', refreshLog);
        $('#clear-log').on('click', clearLog);
        
        // 初期化ボタン
        $('#initialize-sheet').on('click', initializeSheet);
        $('#export-all-posts').on('click', exportAllPosts);
        $('#clear-sheet').on('click', clearSheet);
        
        // フィールドバリデーション設定ボタン
        $('#setup-field-validation').on('click', setupFieldValidation);
        
        // フィールド同期テストボタン
        $('#test-specific-fields').on('click', testSpecificFields);
        
        // コピーボタン
        $('.gi-copy-btn').on('click', handleCopyButton);
        
        // 初回接続テスト（ページ読み込み時）
        setTimeout(testConnection, 1000);
    }
    
    /**
     * 接続テスト
     */
    function testConnection() {
        const $btn = $('#test-connection');
        const $status = $('#connection-status');
        
        // ボタンを無効化
        $btn.prop('disabled', true).text(giSheetsAdmin.strings.testing);
        
        // ステータスを更新中に設定
        updateConnectionStatus('testing', 'テスト中...');
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'gi_test_sheets_connection',
                nonce: giSheetsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateConnectionStatus('connected', response.data);
                    showNotice('success', response.data);
                } else {
                    updateConnectionStatus('error', response.data || 'エラーが発生しました');
                    showNotice('error', response.data || 'エラーが発生しました');
                }
            },
            error: function(xhr, status, error) {
                updateConnectionStatus('error', 'ネットワークエラー: ' + error);
                showNotice('error', 'ネットワークエラー: ' + error);
            },
            complete: function() {
                $btn.prop('disabled', false).text('接続をテスト');
            }
        });
    }
    
    /**
     * 接続ステータスを更新
     */
    function updateConnectionStatus(status, message) {
        const $status = $('#connection-status');
        const $text = $status.find('.gi-status-text');
        
        // クラスをリセット
        $status.removeClass('gi-status-unknown gi-status-testing gi-status-connected gi-status-error');
        
        // 新しいクラスを追加
        $status.addClass('gi-status-' + status);
        
        // テキストを更新
        $text.text(message);
    }
    
    /**
     * 手動同期処理
     */
    function handleManualSync() {
        const $btn = $(this);
        const direction = $btn.data('direction');
        const originalText = $btn.text();
        
        // 確認ダイアログ
        if (!confirm(giSheetsAdmin.strings.confirm_sync)) {
            return;
        }
        
        // ボタンを無効化
        $btn.prop('disabled', true).text(giSheetsAdmin.strings.syncing);
        $('.gi-sync-btn').not($btn).prop('disabled', true);
        
        // 結果エリアを初期化
        $('#sync-result').hide();
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'gi_manual_sheets_sync',
                nonce: giSheetsAdmin.nonce,
                direction: direction
            },
            success: function(response) {
                if (response.success) {
                    showSyncResult('success', response.data);
                    showNotice('success', response.data);
                } else {
                    showSyncResult('error', response.data || '同期に失敗しました');
                    showNotice('error', response.data || '同期に失敗しました');
                }
            },
            error: function(xhr, status, error) {
                const message = 'ネットワークエラー: ' + error;
                showSyncResult('error', message);
                showNotice('error', message);
            },
            complete: function() {
                // ボタンを復元
                $('.gi-sync-btn').prop('disabled', false);
                $btn.text(originalText);
                
                // ログを自動更新
                setTimeout(refreshLog, 2000);
            }
        });
    }
    
    /**
     * 同期結果を表示
     */
    function showSyncResult(type, message) {
        const $result = $('#sync-result');
        const $notice = $result.find('.notice');
        const $message = $('#sync-message');
        
        // クラスをリセット
        $notice.removeClass('notice-success notice-error');
        
        // 新しいクラスを追加
        if (type === 'success') {
            $notice.addClass('notice-success');
        } else {
            $notice.addClass('notice-error');
        }
        
        // メッセージを設定
        $message.text(message);
        
        // 表示
        $result.show();
        
        // 5秒後に自動で隠す
        setTimeout(function() {
            $result.fadeOut();
        }, 5000);
    }
    
    /**
     * ログを更新
     */
    function refreshLog() {
        const $btn = $('#refresh-log');
        const originalText = $btn.text();
        
        $btn.prop('disabled', true).text('更新中...');
        
        // ページをリロード（簡易実装）
        window.location.reload();
    }
    
    /**
     * ログをクリア
     */
    function clearLog() {
        if (!confirm('ログをクリアしますか？この操作は取り消せません。')) {
            return;
        }
        
        const $btn = $('#clear-log');
        const originalText = $btn.text();
        
        $btn.prop('disabled', true).text('クリア中...');
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'gi_clear_sheets_log',
                nonce: giSheetsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data);
                    // ログエリアをクリア
                    $('#sync-log').html('<p>まだログがありません。</p>');
                } else {
                    showNotice('error', response.data || 'ログのクリアに失敗しました');
                }
            },
            error: function(xhr, status, error) {
                showNotice('error', 'ネットワークエラー: ' + error);
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    }
    
    /**
     * 通知を表示
     */
    function showNotice(type, message) {
        // 既存の通知を削除
        $('.gi-admin-notice').remove();
        
        // 新しい通知を作成
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const $notice = $('<div class="notice ' + noticeClass + ' is-dismissible gi-admin-notice"><p>' + message + '</p></div>');
        
        // 通知を挿入
        $('.wrap h1').after($notice);
        
        // 自動で消す
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // 閉じるボタンの処理
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        });
    }
    
    /**
     * スプレッドシート初期化
     */
    function initializeSheet() {
        if (!confirm('スプレッドシートを初期化しますか？ヘッダー行と既存投稿がエクスポートされます。')) {
            return;
        }
        
        const $btn = $('#initialize-sheet');
        const originalText = $btn.text();
        
        $btn.prop('disabled', true).text('初期化中...');
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'gi_initialize_sheet',
                nonce: giSheetsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data);
                } else {
                    showNotice('error', response.data || '初期化に失敗しました');
                }
            },
            error: function(xhr, status, error) {
                showNotice('error', 'ネットワークエラー: ' + error);
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    }
    
    /**
     * 全投稿エクスポート
     */
    function exportAllPosts() {
        if (!confirm('全投稿をスプレッドシートにエクスポートしますか？')) {
            return;
        }
        
        const $btn = $('#export-all-posts');
        const originalText = $btn.text();
        
        $btn.prop('disabled', true).text('エクスポート中...');
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'gi_export_all_posts',
                nonce: giSheetsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data);
                } else {
                    showNotice('error', response.data || 'エクスポートに失敗しました');
                }
            },
            error: function(xhr, status, error) {
                showNotice('error', 'ネットワークエラー: ' + error);
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    }
    
    /**
     * スプレッドシートクリア
     */
    function clearSheet() {
        if (!confirm('⚠️ 注意：スプレッドシートの全データが削除されます。\nこの操作は取り消せません。本当に実行しますか？')) {
            return;
        }
        
        const $btn = $('#clear-sheet');
        const originalText = $btn.text();
        
        $btn.prop('disabled', true).text('クリア中...');
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'gi_clear_sheet',
                nonce: giSheetsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data);
                } else {
                    showNotice('error', response.data || 'クリアに失敗しました');
                }
            },
            error: function(xhr, status, error) {
                showNotice('error', 'ネットワークエラー: ' + error);
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    }
    
    /**
     * コピーボタンの処理
     */
    function handleCopyButton() {
        const $btn = $(this);
        const textToCopy = $btn.data('copy');
        const originalText = $btn.text();
        
        try {
            navigator.clipboard.writeText(textToCopy).then(function() {
                $btn.text('コピー済み').addClass('gi-copied');
                setTimeout(function() {
                    $btn.text(originalText).removeClass('gi-copied');
                }, 2000);
            });
        } catch (err) {
            // フォールバック: テキストエリアを使用
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            $btn.text('コピー済み').addClass('gi-copied');
            setTimeout(function() {
                $btn.text(originalText).removeClass('gi-copied');
            }, 2000);
        }
    }
    
    /**
     * フィールドバリデーション設定
     */
    function setupFieldValidation() {
        const $btn = $('#setup-field-validation');
        const $result = $('#validation-result');
        const $message = $('#validation-message');
        
        // ボタンを無効化
        $btn.prop('disabled', true).html('🔧 設定準備中...');
        
        // 結果エリアを隠す
        $result.hide();
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'gi_setup_field_validation',
                nonce: giSheetsAdmin.nonce
            },
            timeout: 60000, // 60秒タイムアウト
            success: function(response) {
                if (response.success) {
                    $message.html(`
                        <strong>✅ フィールドバリデーション情報の準備が完了しました</strong><br>
                        ${response.data.message}<br><br>
                        <strong>📋 次の手順でスプレッドシートにプルダウンを設定してください：</strong><br>
                        ${Object.values(response.data.next_steps).map((step, index) => `${index + 1}. ${step}`).join('<br>')}
                        <br><br>
                        <em>設定後は、選択肢フィールド（E、M、O、R、U、V列）の背景が薄い青色になり、プルダウンメニューから正しい値を選択できるようになります。</em>
                    `);
                    $result.removeClass('notice-error notice-warning').addClass('notice-success').show();
                } else {
                    $message.html('❌ フィールドバリデーション設定の準備に失敗しました: ' + (response.data || '不明なエラー'));
                    $result.removeClass('notice-success notice-warning').addClass('notice-error').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Field validation setup error:', xhr, status, error);
                $message.html('❌ フィールドバリデーション設定中にエラーが発生しました: ' + error);
                $result.removeClass('notice-success notice-warning').addClass('notice-error').show();
            },
            complete: function() {
                // ボタンを復元
                $btn.prop('disabled', false).html('🔧 フィールドバリデーション設定を準備');
            }
        });
    }
    
    /**
     * 特定フィールドの同期テスト
     */
    function testSpecificFields() {
        const $btn = $('#test-specific-fields');
        const $result = $('#field-test-result');
        const $content = $('#field-test-content');
        
        // ボタンを無効化
        $btn.prop('disabled', true).text('🔍 テスト実行中...');
        
        // 結果エリアを隠す
        $result.hide();
        
        $.ajax({
            url: giSheetsAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'gi_test_specific_fields',
                nonce: giSheetsAdmin.nonce
            },
            timeout: 30000, // 30秒タイムアウト
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    let html = `
                        <strong>🔍 フィールド同期テスト結果</strong><br>
                        <strong>テスト対象行:</strong> ${data.total_rows}行（最初の5行をテスト）<br><br>
                    `;
                    
                    if (data.test_results.length === 0) {
                        html += '<div style="background:#fff3cd;padding:10px;border-radius:3px;margin:5px 0;">⚠️ テスト可能な投稿が見つかりませんでした。スプレッドシートにWordPress投稿IDが設定された行があることを確認してください。</div>';
                    } else {
                        data.test_results.forEach(function(test) {
                            html += `
                                <div style="border:1px solid #ddd;padding:10px;margin:10px 0;border-radius:5px;">
                                    <strong>📝 投稿: ${test.post_title} (ID: ${test.post_id}, 行: ${test.sheet_row})</strong><br><br>
                                    <table style="width:100%;border-collapse:collapse;font-size:12px;">
                                        <tr style="background:#f2f2f2;">
                                            <th style="border:1px solid #ddd;padding:5px;">フィールド</th>
                                            <th style="border:1px solid #ddd;padding:5px;">列</th>
                                            <th style="border:1px solid #ddd;padding:5px;">スプレッドシート値</th>
                                            <th style="border:1px solid #ddd;padding:5px;">WordPress値</th>
                                            <th style="border:1px solid #ddd;padding:5px;">同期状況</th>
                                        </tr>
                            `;
                            
                            Object.keys(test.fields).forEach(function(fieldKey) {
                                const field = test.fields[fieldKey];
                                const statusColor = field.matches ? '#d4edda' : '#f8d7da';
                                const statusText = field.matches ? '✅ 一致' : '❌ 不一致';
                                
                                html += `
                                    <tr style="background:${statusColor};">
                                        <td style="border:1px solid #ddd;padding:5px;">${fieldKey}</td>
                                        <td style="border:1px solid #ddd;padding:5px;">${field.column}</td>
                                        <td style="border:1px solid #ddd;padding:5px;">${field.sheet_value || '(空)'}</td>
                                        <td style="border:1px solid #ddd;padding:5px;">${field.wp_value || '(空)'}</td>
                                        <td style="border:1px solid #ddd;padding:5px;">${statusText}</td>
                                    </tr>
                                `;
                            });
                            
                            html += '</table></div>';
                        });
                        
                        // 不一致があるかチェック
                        let hasMismatches = false;
                        data.test_results.forEach(function(test) {
                            Object.keys(test.fields).forEach(function(fieldKey) {
                                if (!test.fields[fieldKey].matches) {
                                    hasMismatches = true;
                                }
                            });
                        });
                        
                        if (hasMismatches) {
                            html += `
                                <div style="background:#f8d7da;color:#721c24;padding:10px;border-radius:3px;margin:10px 0;">
                                    <strong>⚠️ 同期の不一致が検出されました</strong><br>
                                    上記の表で「❌ 不一致」となっているフィールドは、スプレッドシートとWordPressで値が異なります。<br>
                                    「Sheets → WordPress」同期を実行して修正することをお勧めします。
                                </div>
                            `;
                        } else {
                            html += `
                                <div style="background:#d4edda;color:#155724;padding:10px;border-radius:3px;margin:10px 0;">
                                    <strong>✅ すべてのフィールドが正常に同期されています</strong><br>
                                    都道府県、カテゴリ、対象市町村のフィールドは正しく同期されています。
                                </div>
                            `;
                        }
                    }
                    
                    $content.html(html);
                    $result.removeClass('notice-error').addClass('notice-success').show();
                } else {
                    $content.html('❌ フィールドテストに失敗しました: ' + (response.data || '不明なエラー'));
                    $result.removeClass('notice-success').addClass('notice-error').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Field test error:', xhr, status, error);
                $content.html('❌ フィールドテスト中にエラーが発生しました: ' + error);
                $result.removeClass('notice-success').addClass('notice-error').show();
            },
            complete: function() {
                // ボタンを復元
                $btn.prop('disabled', false).text('🔍 フィールド同期テスト');
            }
        });
    }
    
    /**
     * 設定フォームの送信処理
     */
    $('form').on('submit', function() {
        const $submitBtn = $(this).find('input[type="submit"]');
        $submitBtn.prop('disabled', true).val('保存中...');
        
        // フォーム送信後にボタンを復元
        setTimeout(function() {
            $submitBtn.prop('disabled', false).val('設定を保存');
        }, 3000);
    });
    
})(jQuery);