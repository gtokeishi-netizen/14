/**
 * 助成金投稿メタボックス用 JavaScript (WordPress標準タクソノミー版)
 * 
 * WordPress標準タクソノミー + ACFフィールドのハイブリッド管理
 * - カテゴリー・都道府県・市町村: WordPress標準タクソノミー
 * - その他のフィールド: ACF維持
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initGrantTaxonomyMetaboxes();
    });
    
    function initGrantTaxonomyMetaboxes() {
        // ==========
        // 1. タクソノミー関連機能
        // ==========
        
        // 都道府県：全国対象チェックボックス
        $('#select_all_prefectures').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.prefecture-checkbox').prop('checked', isChecked);
        });
        
        // 都道府県：個別チェックボックス変更時
        $('.prefecture-checkbox').on('change', function() {
            const totalPrefectures = $('.prefecture-checkbox').length;
            const checkedPrefectures = $('.prefecture-checkbox:checked').length;
            $('#select_all_prefectures').prop('checked', totalPrefectures === checkedPrefectures);
        });
        
        // 市町村：検索機能
        $('#municipality_search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.municipality-option').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(searchTerm));
            });
        });
        
        // カテゴリー追加
        $('#add_grant_category').on('click', function() {
            const categoryName = $('#new_grant_category').val().trim();
            if (categoryName) {
                addNewTaxonomyTerm('grant_category', categoryName, 'category');
            }
        });
        
        // 市町村追加
        $('#add_municipality').on('click', function() {
            const municipalityName = $('#new_municipality').val().trim();
            if (municipalityName) {
                addNewTaxonomyTerm('grant_municipality', municipalityName, 'municipality');
            }
        });
        
        // ACFフィールドは標準のACF機能に任せる
        
        // フィールドの変更を検知してGoogle Sheets同期を提案
        trackFieldChanges();
        
        // 初期状態設定
        checkInitialSelections();
    }
    
    /**
     * 新しいタクソノミータームを追加
     */
    function addNewTaxonomyTerm(taxonomy, termName, type) {
        $.ajax({
            url: grantMetaboxes.ajaxurl,
            type: 'POST',
            data: {
                action: 'gi_add_taxonomy_term',
                taxonomy: taxonomy,
                term_name: termName,
                nonce: grantMetaboxes.nonce
            },
            success: function(response) {
                if (response.success) {
                    // 新しいタームをリストに追加
                    const termId = response.data.term_id;
                    const termName = response.data.name;
                    
                    let targetContainer = '';
                    let inputName = '';
                    
                    if (type === 'category') {
                        targetContainer = '#grant-category-selection';
                        inputName = 'grant_categories[]';
                        $('#new_grant_category').val('');
                    } else if (type === 'municipality') {
                        targetContainer = '#grant-municipality-selection';
                        inputName = 'grant_municipalities[]';
                        $('#new_municipality').val('');
                    }
                    
                    const newOption = `
                        <label style="display: block; margin-bottom: 6px;" class="${type === 'municipality' ? 'municipality-option' : ''}">
                            <input type="checkbox" 
                                   name="${inputName}" 
                                   value="${termId}"
                                   checked>
                            ${termName}
                            <span style="color: #666;">（0件）</span>
                        </label>
                    `;
                    
                    // 追加ボタンの直前に挿入
                    $(targetContainer + ' > div:last-child').before(newOption);
                    
                    showNotice('success', `「${termName}」を追加しました。`);
                } else {
                    showNotice('error', `追加に失敗しました: ${response.data}`);
                }
            },
            error: function() {
                showNotice('error', '通信エラーが発生しました。');
            }
        });
    }
    
    /**
     * 初期選択状態をチェック
     */
    function checkInitialSelections() {
        // 都道府県の全選択状態をチェック
        const totalPrefectures = $('.prefecture-checkbox').length;
        const checkedPrefectures = $('.prefecture-checkbox:checked').length;
        $('#select_all_prefectures').prop('checked', totalPrefectures === checkedPrefectures && totalPrefectures > 0);
    }
    
    /**
     * 通知メッセージを表示
     */
    function showNotice(type, message) {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = $(`
            <div class="notice ${noticeClass} is-dismissible" style="margin: 10px 0;">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">この通知を閉じる</span>
                </button>
            </div>
        `);
        
        $('#post').prepend(notice);
        
        // 自動で5秒後に消す
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
        
        // 閉じるボタン
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut();
        });
    }
    
    // 削除済み：ACF関連の自動生成機能
    // ACFの標準機能で十分なため、カスタム機能は不要
    
    /**
     * タクソノミー変更の追跡（シンプル版）
     */
    function trackFieldChanges() {
        // タクソノミーの変更を検知
        $(document).on('change', 'input[name="grant_categories[]"], input[name="grant_prefectures[]"], input[name="grant_municipalities[]"]', function() {
            // 何かが変更されたことを視覚的に表示
            $(this).closest('.grant-metabox-content').css('border-left', '3px solid #00a0d2');
            setTimeout(() => {
                $(this).closest('.grant-metabox-content').css('border-left', '');
            }, 2000);
        });
    }
    
    // 削除済み：複雑な同期リマインダー機能
    // Google Sheetsの同期は自動で動作するため不要
    
    // 削除済み：複雑なバリデーション機能
    // WordPressとACFの標準バリデーション機能で十分
    
})(jQuery);