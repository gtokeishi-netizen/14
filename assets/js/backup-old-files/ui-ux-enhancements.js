/**
 * UI/UX Enhancements JavaScript
 * スクリーンショット分析に基づくインタラクション改善
 * 
 * @version 1.0.0
 * @date 2025-10-04
 */

(function($) {
    'use strict';

    // ============================================================
    // 1. COMPARISON WIDGET MANAGEMENT
    //    比較ウィジェット管理
    // ============================================================
    
    const ComparisonManager = {
        init: function() {
            this.items = [];
            this.maxItems = 3;
            this.setupEventListeners();
            this.loadFromStorage();
        },
        
        setupEventListeners: function() {
            // 比較チェックボックスのイベント
            $(document).on('change', '.grant-compare-checkbox', function() {
                const grantId = $(this).data('grant-id');
                const grantTitle = $(this).data('grant-title');
                
                if ($(this).is(':checked')) {
                    ComparisonManager.addItem(grantId, grantTitle);
                } else {
                    ComparisonManager.removeItem(grantId);
                }
            });
            
            // 比較実行ボタン
            $(document).on('click', '.execute-comparison', function(e) {
                e.preventDefault();
                ComparisonManager.executeComparison();
            });
            
            // 比較クリアボタン
            $(document).on('click', '.clear-comparison', function(e) {
                e.preventDefault();
                ComparisonManager.clearAll();
            });
        },
        
        addItem: function(id, title) {
            if (this.items.length >= this.maxItems) {
                this.showNotification('比較は最大' + this.maxItems + '件までです', 'warning');
                return false;
            }
            
            if (this.items.find(item => item.id === id)) {
                return false; // 既に追加済み
            }
            
            this.items.push({ id: id, title: title });
            this.updateWidget();
            this.saveToStorage();
            this.showNotification('比較リストに追加しました', 'success');
            
            return true;
        },
        
        removeItem: function(id) {
            this.items = this.items.filter(item => item.id !== id);
            this.updateWidget();
            this.saveToStorage();
            
            // チェックボックスの状態を更新
            $('.grant-compare-checkbox[data-grant-id="' + id + '"]').prop('checked', false);
        },
        
        clearAll: function() {
            this.items = [];
            this.updateWidget();
            this.saveToStorage();
            $('.grant-compare-checkbox').prop('checked', false);
            this.showNotification('比較リストをクリアしました', 'info');
        },
        
        updateWidget: function() {
            if (this.items.length === 0) {
                $('.comparison-bar').removeClass('active');
                $('body').removeClass('has-comparison-bar');
                return;
            }
            
            $('body').addClass('has-comparison-bar');
            
            let html = '<div class="comparison-bar active">';
            html += '<div class="comparison-bar-inner">';
            html += '<div class="comparison-items">';
            
            this.items.forEach(item => {
                html += '<div class="comparison-item" data-id="' + item.id + '">';
                html += '<span class="item-title">' + item.title + '</span>';
                html += '<button class="remove-item" data-id="' + item.id + '">×</button>';
                html += '</div>';
            });
            
            html += '</div>';
            html += '<div class="comparison-actions">';
            html += '<button class="execute-comparison">比較する (' + this.items.length + '件)</button>';
            html += '<button class="clear-comparison">クリア</button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            $('.comparison-bar').replaceWith(html);
        },
        
        executeComparison: function() {
            if (this.items.length < 2) {
                this.showNotification('比較するには2件以上選択してください', 'warning');
                return;
            }
            
            // 比較ページへリダイレクト
            const ids = this.items.map(item => item.id).join(',');
            window.location.href = '/compare?grants=' + ids;
        },
        
        saveToStorage: function() {
            try {
                localStorage.setItem('grant_comparison', JSON.stringify(this.items));
            } catch (e) {
                console.error('Failed to save comparison data:', e);
            }
        },
        
        loadFromStorage: function() {
            try {
                const saved = localStorage.getItem('grant_comparison');
                if (saved) {
                    this.items = JSON.parse(saved);
                    this.updateWidget();
                    
                    // チェックボックスの状態を復元
                    this.items.forEach(item => {
                        $('.grant-compare-checkbox[data-grant-id="' + item.id + '"]').prop('checked', true);
                    });
                }
            } catch (e) {
                console.error('Failed to load comparison data:', e);
            }
        },
        
        showNotification: function(message, type) {
            const notification = $('<div class="ui-notification ' + type + '">' + message + '</div>');
            $('body').append(notification);
            
            setTimeout(() => {
                notification.addClass('show');
            }, 10);
            
            setTimeout(() => {
                notification.removeClass('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    };

    // ============================================================
    // 2. FILTER MANAGEMENT
    //    フィルター管理
    // ============================================================
    
    const FilterManager = {
        init: function() {
            this.activeFilters = {
                categories: [],
                prefectures: [],
                keywords: []
            };
            this.setupEventListeners();
        },
        
        setupEventListeners: function() {
            // カテゴリフィルター
            $(document).on('click', '.filter-chip[data-type="category"]', function() {
                const value = $(this).data('value');
                FilterManager.toggleFilter('categories', value);
                $(this).toggleClass('selected');
                FilterManager.applyFilters();
            });
            
            // 都道府県フィルター
            $(document).on('click', '.prefecture-item', function(e) {
                e.preventDefault();
                const value = $(this).data('prefecture');
                FilterManager.toggleFilter('prefectures', value);
                $(this).toggleClass('selected');
                FilterManager.applyFilters();
            });
            
            // ゼロ件非表示トグル
            $(document).on('change', '#hide-zero-count', function() {
                FilterManager.toggleZeroCount($(this).is(':checked'));
            });
            
            // フィルタークリア
            $(document).on('click', '.clear-filters', function() {
                FilterManager.clearAllFilters();
            });
        },
        
        toggleFilter: function(type, value) {
            const index = this.activeFilters[type].indexOf(value);
            if (index > -1) {
                this.activeFilters[type].splice(index, 1);
            } else {
                this.activeFilters[type].push(value);
            }
        },
        
        applyFilters: function() {
            // URLパラメータを更新
            const params = new URLSearchParams();
            
            if (this.activeFilters.categories.length > 0) {
                params.set('categories', this.activeFilters.categories.join(','));
            }
            if (this.activeFilters.prefectures.length > 0) {
                params.set('prefectures', this.activeFilters.prefectures.join(','));
            }
            
            // ページをリロードせずにURLを更新
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.pushState({}, '', newUrl);
            
            // AJAXでコンテンツを更新
            this.loadFilteredResults();
        },
        
        loadFilteredResults: function() {
            $('.grants-grid').addClass('loading');
            
            $.ajax({
                url: window.gi_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_grants',
                    nonce: window.gi_ajax.nonce,
                    filters: this.activeFilters
                },
                success: function(response) {
                    if (response.success) {
                        $('.grants-grid').html(response.data.html);
                        $('.results-count').text(response.data.count + '件の助成金');
                    }
                },
                error: function() {
                    FilterManager.showError('フィルター処理中にエラーが発生しました');
                },
                complete: function() {
                    $('.grants-grid').removeClass('loading');
                }
            });
        },
        
        toggleZeroCount: function(hide) {
            if (hide) {
                $('.filter-chip[data-count="0"]').addClass('hidden');
                $('.prefecture-item[data-count="0"]').addClass('hidden');
            } else {
                $('.filter-chip, .prefecture-item').removeClass('hidden');
            }
        },
        
        clearAllFilters: function() {
            this.activeFilters = {
                categories: [],
                prefectures: [],
                keywords: []
            };
            
            $('.filter-chip, .prefecture-item').removeClass('selected');
            window.history.pushState({}, '', window.location.pathname);
            this.loadFilteredResults();
        },
        
        showError: function(message) {
            alert(message); // より良いエラー表示に置き換え可能
        }
    };

    // ============================================================
    // 3. SMOOTH SCROLL & ANIMATIONS
    //    スムーズスクロールとアニメーション
    // ============================================================
    
    const ScrollManager = {
        init: function() {
            this.setupSmoothScroll();
            this.setupScrollAnimations();
            this.setupBackToTop();
        },
        
        setupSmoothScroll: function() {
            $('a[href^="#"]').on('click', function(e) {
                const target = $(this.getAttribute('href'));
                
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 600, 'swing');
                }
            });
        },
        
        setupScrollAnimations: function() {
            // Intersection Observer for fade-in animations
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animated');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                $('.category-card, .prefecture-item, .grant-card').each(function() {
                    observer.observe(this);
                });
            }
        },
        
        setupBackToTop: function() {
            // トップへ戻るボタンの表示/非表示
            $(window).on('scroll', function() {
                if ($(this).scrollTop() > 300) {
                    $('.back-to-top').addClass('visible');
                } else {
                    $('.back-to-top').removeClass('visible');
                }
            });
            
            // トップへ戻るボタンのクリック
            $(document).on('click', '.back-to-top', function(e) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: 0 }, 600);
            });
        }
    };

    // ============================================================
    // 4. MOBILE MENU & INTERACTIONS
    //    モバイルメニューとインタラクション
    // ============================================================
    
    const MobileManager = {
        init: function() {
            this.setupMobileMenu();
            this.setupTouchOptimizations();
        },
        
        setupMobileMenu: function() {
            // モバイルメニュートグル
            $(document).on('click', '.mobile-menu-toggle', function() {
                $('body').toggleClass('mobile-menu-open');
                $(this).toggleClass('active');
            });
            
            // メニュー外クリックで閉じる
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.mobile-menu, .mobile-menu-toggle').length) {
                    $('body').removeClass('mobile-menu-open');
                    $('.mobile-menu-toggle').removeClass('active');
                }
            });
        },
        
        setupTouchOptimizations: function() {
            // タッチデバイスでのhover代替
            if ('ontouchstart' in window) {
                $('.category-card, .prefecture-item').on('touchstart', function() {
                    $(this).addClass('touch-active');
                }).on('touchend', function() {
                    $(this).removeClass('touch-active');
                });
            }
        }
    };

    // ============================================================
    // 5. FORM ENHANCEMENTS
    //    フォーム拡張
    // ============================================================
    
    const FormManager = {
        init: function() {
            this.setupSearchEnhancements();
            this.setupFormValidation();
        },
        
        setupSearchEnhancements: function() {
            let searchTimeout;
            
            // リアルタイム検索サジェスト
            $('.search-input').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        FormManager.loadSuggestions(query);
                    }, 300);
                } else {
                    $('.search-suggestions').empty().hide();
                }
            });
            
            // サジェストクリック
            $(document).on('click', '.suggestion-item', function() {
                const value = $(this).data('value');
                $('.search-input').val(value);
                $('.search-suggestions').empty().hide();
                $('.search-form').submit();
            });
        },
        
        loadSuggestions: function(query) {
            $.ajax({
                url: window.gi_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'search_suggestions',
                    nonce: window.gi_ajax.nonce,
                    query: query
                },
                success: function(response) {
                    if (response.success && response.data.suggestions.length > 0) {
                        let html = '<div class="search-suggestions">';
                        response.data.suggestions.forEach(suggestion => {
                            html += '<div class="suggestion-item" data-value="' + suggestion.value + '">';
                            html += '<i class="fas fa-search"></i>';
                            html += '<span>' + suggestion.label + '</span>';
                            html += '</div>';
                        });
                        html += '</div>';
                        
                        $('.search-container').append(html);
                    }
                }
            });
        },
        
        setupFormValidation: function() {
            // フォームバリデーション
            $('form').on('submit', function(e) {
                const $form = $(this);
                let isValid = true;
                
                $form.find('[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('error').focus();
                        return false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    FormManager.showValidationError('必須項目を入力してください');
                }
            });
            
            // エラー状態のクリア
            $('input, textarea, select').on('input change', function() {
                $(this).removeClass('error');
            });
        },
        
        showValidationError: function(message) {
            const $error = $('<div class="validation-error">' + message + '</div>');
            $('body').append($error);
            
            setTimeout(() => $error.addClass('show'), 10);
            setTimeout(() => {
                $error.removeClass('show');
                setTimeout(() => $error.remove(), 300);
            }, 3000);
        }
    };

    // ============================================================
    // INITIALIZATION
    //    初期化
    // ============================================================
    
    $(document).ready(function() {
        ComparisonManager.init();
        FilterManager.init();
        ScrollManager.init();
        MobileManager.init();
        FormManager.init();
        
        console.log('✅ UI/UX Enhancements initialized');
    });

})(jQuery);
