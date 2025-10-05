/*!
 * Grant Insight Perfect - 統合フロントエンドJavaScript
 * 全JSを統合・最適化したメインスクリプト
 * 重複削除、パフォーマンス最適化済み
 * 
 * @version 1.0.0
 * @date 2025-10-05
 */

/**
 * =============================================================================
 * GRANT INSIGHT - メイン名前空間
 * グローバルスコープ汚染を防ぐ統一名前空間
 * =============================================================================
 */
const GrantInsight = {
    // バージョン情報
    version: '1.0.0',
    
    // 設定オブジェクト
    config: {
        debounceDelay: 300,
        toastDuration: 3000,
        scrollTrackingInterval: 250,
        apiEndpoint: '/wp-admin/admin-ajax.php',
        searchMinLength: 2,
        maxComparisonItems: 3
    },

    // 初期化フラグ
    initialized: false,
    
    // 状態管理
    state: {
        lastScrollY: 0,
        headerHeight: 0,
        isScrolling: false,
        activeFilters: new Map(),
        comparisonItems: [],
        touchStartY: 0,
        touchEndY: 0
    },

    // DOM要素キャッシュ
    elements: {},

    /**
     * ==========================================================================
     * 初期化システム
     * ==========================================================================
     */
    init() {
        if (this.initialized) return;
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupAll());
        } else {
            this.setupAll();
        }
    },

    /**
     * 全機能のセットアップ
     */
    setupAll() {
        try {
            this.cacheElements();
            this.setupUtils();
            this.setupSearch();
            this.setupFilters();
            this.setupComparison();
            this.setupMobile();
            this.setupAccessibility();
            this.setupPerformance();
            this.setupAnimations();
            this.setupForms();
            
            this.initialized = true;
            this.debug('Grant Insight initialized successfully');
        } catch (error) {
            console.error('Initialization error:', error);
        }
    },

    /**
     * DOM要素のキャッシュ
     */
    cacheElements() {
        this.elements = {
            // 検索関連
            searchInputs: document.querySelectorAll('.gi-search-input, #grant-search, .search-input'),
            searchContainer: document.querySelector('.gi-search-container, .search-container'),
            searchSuggestions: null, // 動的作成
            
            // フィルター関連
            filterButtons: document.querySelectorAll('.gi-filter-chip, .filter-button, .filter-chip'),
            filterTrigger: document.querySelector('.gi-filter-trigger, #filter-toggle'),
            
            // コンテンツ関連
            grantsGrid: document.querySelector('.gi-grants-grid, .grants-grid, #grants-container'),
            
            // UI要素
            header: document.querySelector('.gi-mobile-header, .site-header'),
            body: document.body,
            
            // 比較関連
            comparisonBar: null // 動的作成
        };
    },

    /**
     * ==========================================================================
     * ユーティリティ関数群
     * ==========================================================================
     */
    setupUtils() {
        // HTMLエスケープ関数
        this.escapeHtml = function(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        };

        // デバウンス関数
        this.debounce = function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };

        // スロットル関数
        this.throttle = function(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        };

        // トースト通知関数
        this.showToast = function(message, type = 'info') {
            // 既存のトーストを削除
            const existingToast = document.querySelector('.gi-toast, .ui-notification');
            if (existingToast) {
                existingToast.remove();
            }
            
            const toast = document.createElement('div');
            toast.className = `gi-toast gi-toast-${type}`;
            toast.innerHTML = `
                <div class="gi-toast-content">
                    <span class="gi-toast-message">${this.escapeHtml(message)}</span>
                    <button class="gi-toast-close" aria-label="閉じる">×</button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // アニメーション
            requestAnimationFrame(() => {
                toast.classList.add('gi-toast-show');
            });
            
            // 閉じるボタン
            toast.querySelector('.gi-toast-close').addEventListener('click', () => {
                this.hideToast(toast);
            });
            
            // 自動削除
            setTimeout(() => {
                this.hideToast(toast);
            }, this.config.toastDuration);
            
            return toast;
        };

        // トースト非表示
        this.hideToast = function(toast) {
            toast.classList.remove('gi-toast-show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        };

        // AJAX関数（統一API）
        this.ajax = function(action, data = {}, options = {}) {
            const url = options.url || this.config.apiEndpoint;
            
            const requestData = {
                action: action,
                nonce: window.gi_ajax?.nonce || options.nonce,
                ...data
            };

            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    ...options.headers
                },
                body: new URLSearchParams(requestData).toString(),
                ...options
            }).then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            });
        };

        // デバッグ関数
        this.debug = function(message, ...args) {
            if (window.location.hostname === 'localhost' || window.location.search.includes('debug=1')) {
                console.log(`[Grant Insight] ${message}`, ...args);
            }
        };
    },

    /**
     * ==========================================================================
     * 検索機能（統合版）
     * ==========================================================================
     */
    setupSearch() {
        if (!this.elements.searchInputs.length) return;

        this.elements.searchInputs.forEach(input => {
            // 検索入力のデバウンス処理
            const debouncedSearch = this.debounce((value) => {
                if (value.length >= this.config.searchMinLength) {
                    this.performSearch(value);
                    this.showSearchSuggestions(value);
                } else {
                    this.hideSearchSuggestions();
                }
            }, this.config.debounceDelay);

            // 入力イベント
            input.addEventListener('input', (e) => {
                debouncedSearch(e.target.value);
            });

            // エンターキーでの検索実行
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.executeSearch(e.target.value);
                }
                
                // キーボードナビゲーション
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    this.handleSuggestionNavigation(e);
                }

                if (e.key === 'Escape') {
                    this.hideSearchSuggestions();
                }
            });

            // フォーカス時の処理
            input.addEventListener('focus', () => {
                this.state.lastFocusedInput = input;
                if (input.value.length >= this.config.searchMinLength) {
                    this.showSearchSuggestions(input.value);
                }
            });

            // フォーカス外時の処理
            input.addEventListener('blur', () => {
                setTimeout(() => this.hideSearchSuggestions(), 150);
            });
        });
    },

    /**
     * 検索実行
     */
    performSearch(query) {
        this.ajax('gi_search_grants', { query })
            .then(response => {
                if (response.success) {
                    this.updateSearchResults(response.data);
                } else {
                    this.showToast(response.data || '検索中にエラーが発生しました', 'error');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                this.showToast('検索中にエラーが発生しました', 'error');
            });
    },

    /**
     * 検索候補表示
     */
    showSearchSuggestions(query) {
        this.ajax('gi_get_search_suggestions', { query })
            .then(response => {
                if (response.success) {
                    this.renderSearchSuggestions(response.data);
                }
            })
            .catch(error => {
                this.debug('Search suggestions error:', error);
            });
    },

    /**
     * 検索候補のレンダリング
     */
    renderSearchSuggestions(suggestions) {
        if (!suggestions || !suggestions.length) {
            this.hideSearchSuggestions();
            return;
        }

        let container = this.elements.searchSuggestions;
        if (!container) {
            container = document.createElement('div');
            container.className = 'gi-search-suggestions';
            this.elements.searchSuggestions = container;
            
            if (this.elements.searchContainer) {
                this.elements.searchContainer.appendChild(container);
            } else {
                // フォールバック：最初の検索入力の親に追加
                const firstInput = this.elements.searchInputs[0];
                if (firstInput && firstInput.parentNode) {
                    firstInput.parentNode.appendChild(container);
                }
            }
        }

        container.innerHTML = suggestions.map((item, index) => `
            <div class="gi-suggestion-item" 
                 data-value="${this.escapeHtml(item.value)}"
                 data-index="${index}">
                <i class="fas fa-search gi-suggestion-icon"></i>
                <span class="gi-suggestion-text">${this.escapeHtml(item.label)}</span>
            </div>
        `).join('');

        container.style.display = 'block';
        container.classList.add('gi-suggestions-active');

        // クリックイベントの設定
        container.querySelectorAll('.gi-suggestion-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const value = e.currentTarget.dataset.value;
                this.executeSearch(value);
                this.hideSearchSuggestions();
            });
        });
    },

    /**
     * 検索候補のキーボードナビゲーション
     */
    handleSuggestionNavigation(e) {
        const container = this.elements.searchSuggestions;
        if (!container || !container.classList.contains('gi-suggestions-active')) return;

        const items = container.querySelectorAll('.gi-suggestion-item');
        if (!items.length) return;

        const currentActive = container.querySelector('.gi-suggestion-active');
        let newIndex = 0;

        if (currentActive) {
            const currentIndex = parseInt(currentActive.dataset.index);
            if (e.key === 'ArrowDown') {
                newIndex = (currentIndex + 1) % items.length;
            } else if (e.key === 'ArrowUp') {
                newIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1;
            }
            currentActive.classList.remove('gi-suggestion-active');
        }

        e.preventDefault();
        items[newIndex].classList.add('gi-suggestion-active');
    },

    /**
     * 検索実行
     */
    executeSearch(query) {
        const input = this.elements.searchInputs[0];
        if (input) {
            input.value = query;
        }
        
        // 検索結果ページに移動またはAJAXで結果更新
        const currentPath = window.location.pathname;
        if (currentPath === '/' || currentPath.includes('grants')) {
            this.performSearch(query);
        } else {
            window.location.href = `/grants/?search=${encodeURIComponent(query)}`;
        }
        
        this.hideSearchSuggestions();
    },

    /**
     * 検索候補を隠す
     */
    hideSearchSuggestions() {
        const container = this.elements.searchSuggestions;
        if (container) {
            container.classList.remove('gi-suggestions-active');
            setTimeout(() => {
                container.style.display = 'none';
            }, 150);
        }
    },

    /**
     * ==========================================================================
     * フィルター機能（統合版）
     * ==========================================================================
     */
    setupFilters() {
        // フィルターボタンのイベント
        this.elements.filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.toggleFilter(button);
            });
        });

        // フィルター表示ボタン
        if (this.elements.filterTrigger) {
            this.elements.filterTrigger.addEventListener('click', () => {
                this.showFilterBottomSheet();
            });
        }

        // デリゲートイベント（動的要素用）
        document.addEventListener('click', (e) => {
            // 比較実行
            if (e.target.matches('.execute-comparison, .gi-btn-filter-apply')) {
                e.preventDefault();
                this.handleFilterApply(e.target);
            }

            // 比較クリア
            if (e.target.matches('.clear-comparison, .gi-btn-filter-clear')) {
                e.preventDefault();
                this.clearFilters();
            }

            // フィルターシート閉じる
            if (e.target.matches('.gi-filter-sheet-close')) {
                this.hideFilterBottomSheet();
            }
        });
    },

    /**
     * フィルター切り替え
     */
    toggleFilter(button) {
        const filterType = button.dataset.filter || button.dataset.type;
        const filterValue = button.dataset.value;
        
        if (!filterType || !filterValue) return;

        button.classList.toggle('active');
        button.classList.toggle('selected'); // 互換性のため
        
        const filterKey = `${filterType}-${filterValue}`;
        
        if (button.classList.contains('active')) {
            this.state.activeFilters.set(filterKey, {
                type: filterType,
                value: filterValue,
                label: button.textContent.trim()
            });
        } else {
            this.state.activeFilters.delete(filterKey);
        }

        // リアルタイムフィルタリング
        this.applyFilters();
    },

    /**
     * フィルター適用
     */
    applyFilters() {
        const filters = this.buildFilterObject();
        
        this.ajax('gi_filter_grants', { filters })
            .then(response => {
                if (response.success) {
                    this.updateSearchResults(response.data);
                    const count = response.data.total || response.data.count || 0;
                    this.showToast(`${count}件の助成金が見つかりました`, 'success');
                    this.updateURL(filters);
                } else {
                    this.showToast(response.data || 'フィルター処理中にエラーが発生しました', 'error');
                }
            })
            .catch(error => {
                console.error('Filter error:', error);
                this.showToast('フィルター処理中にエラーが発生しました', 'error');
            });

        this.hideFilterBottomSheet();
    },

    /**
     * フィルターオブジェクトの構築
     */
    buildFilterObject() {
        const filters = {};
        
        this.state.activeFilters.forEach(filter => {
            if (!filters[filter.type]) {
                filters[filter.type] = [];
            }
            filters[filter.type].push(filter.value);
        });

        return filters;
    },

    /**
     * URLの更新（履歴管理）
     */
    updateURL(filters) {
        const params = new URLSearchParams();
        
        Object.keys(filters).forEach(type => {
            if (filters[type].length > 0) {
                params.set(type, filters[type].join(','));
            }
        });
        
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newUrl);
    },

    /**
     * フィルタークリア
     */
    clearFilters() {
        this.state.activeFilters.clear();
        
        // UI状態のリセット
        document.querySelectorAll('.gi-filter-chip.active, .filter-button.active, .filter-chip.selected').forEach(button => {
            button.classList.remove('active', 'selected');
        });

        this.applyFilters();
    },

    /**
     * フィルター適用ハンドラー
     */
    handleFilterApply(target) {
        if (target.classList.contains('execute-comparison')) {
            this.executeComparison();
        } else {
            this.applyFilters();
        }
    },

    /**
     * ==========================================================================
     * 比較機能
     * ==========================================================================
     */
    setupComparison() {
        // 比較チェックボックスのイベント（デリゲート）
        document.addEventListener('change', (e) => {
            if (e.target.matches('.grant-compare-checkbox')) {
                const grantId = e.target.dataset.grantId;
                const grantTitle = e.target.dataset.grantTitle || e.target.closest('.grant-card')?.querySelector('.card-title, .grant-card-title')?.textContent?.trim();
                
                if (e.target.checked) {
                    this.addComparisonItem(grantId, grantTitle);
                } else {
                    this.removeComparisonItem(grantId);
                }
            }
        });

        // ローカルストレージから復元
        this.loadComparisonFromStorage();
    },

    /**
     * 比較アイテム追加
     */
    addComparisonItem(id, title) {
        if (this.state.comparisonItems.length >= this.config.maxComparisonItems) {
            this.showToast(`比較は最大${this.config.maxComparisonItems}件までです`, 'warning');
            
            // チェックボックスを解除
            const checkbox = document.querySelector(`[data-grant-id="${id}"]`);
            if (checkbox) checkbox.checked = false;
            return false;
        }
        
        if (this.state.comparisonItems.find(item => item.id === id)) {
            return false; // 既に追加済み
        }
        
        this.state.comparisonItems.push({ id, title: title || `助成金 ID: ${id}` });
        this.updateComparisonWidget();
        this.saveComparisonToStorage();
        this.showToast('比較リストに追加しました', 'success');
        
        return true;
    },

    /**
     * 比較アイテム削除
     */
    removeComparisonItem(id) {
        this.state.comparisonItems = this.state.comparisonItems.filter(item => item.id !== id);
        this.updateComparisonWidget();
        this.saveComparisonToStorage();
        
        // チェックボックスの状態を更新
        const checkbox = document.querySelector(`[data-grant-id="${id}"]`);
        if (checkbox) checkbox.checked = false;
    },

    /**
     * 比較ウィジェット更新
     */
    updateComparisonWidget() {
        if (this.state.comparisonItems.length === 0) {
            this.hideComparisonWidget();
            return;
        }
        
        this.elements.body.classList.add('has-comparison-bar');
        
        let container = this.elements.comparisonBar;
        if (!container) {
            container = document.createElement('div');
            container.className = 'gi-comparison-bar';
            this.elements.comparisonBar = container;
            this.elements.body.appendChild(container);
        }

        container.innerHTML = `
            <div class="gi-comparison-bar-inner">
                <div class="gi-comparison-items">
                    ${this.state.comparisonItems.map(item => `
                        <div class="gi-comparison-item" data-id="${item.id}">
                            <span class="gi-item-title">${this.escapeHtml(item.title)}</span>
                            <button class="gi-remove-item" data-id="${item.id}" aria-label="削除">×</button>
                        </div>
                    `).join('')}
                </div>
                <div class="gi-comparison-actions">
                    <button class="execute-comparison gi-btn gi-btn-primary">
                        比較する (${this.state.comparisonItems.length}件)
                    </button>
                    <button class="clear-comparison gi-btn gi-btn-secondary">クリア</button>
                </div>
            </div>
        `;
        
        container.classList.add('gi-comparison-active');

        // 削除ボタンのイベント
        container.querySelectorAll('.gi-remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                this.removeComparisonItem(id);
            });
        });
    },

    /**
     * 比較ウィジェット非表示
     */
    hideComparisonWidget() {
        if (this.elements.comparisonBar) {
            this.elements.comparisonBar.classList.remove('gi-comparison-active');
            this.elements.body.classList.remove('has-comparison-bar');
        }
    },

    /**
     * 比較実行
     */
    executeComparison() {
        if (this.state.comparisonItems.length < 2) {
            this.showToast('比較するには2件以上選択してください', 'warning');
            return;
        }
        
        const ids = this.state.comparisonItems.map(item => item.id).join(',');
        window.location.href = `/compare?grants=${ids}`;
    },

    /**
     * 比較データの保存
     */
    saveComparisonToStorage() {
        try {
            localStorage.setItem('grant_comparison', JSON.stringify(this.state.comparisonItems));
        } catch (e) {
            this.debug('Failed to save comparison data:', e);
        }
    },

    /**
     * 比較データの読み込み
     */
    loadComparisonFromStorage() {
        try {
            const saved = localStorage.getItem('grant_comparison');
            if (saved) {
                this.state.comparisonItems = JSON.parse(saved);
                this.updateComparisonWidget();
                
                // チェックボックスの状態を復元
                this.state.comparisonItems.forEach(item => {
                    const checkbox = document.querySelector(`[data-grant-id="${item.id}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
        } catch (e) {
            this.debug('Failed to load comparison data:', e);
        }
    },

    /**
     * ==========================================================================
     * モバイル最適化機能
     * ==========================================================================
     */
    setupMobile() {
        this.setupMobileHeader();
        this.setupTouchOptimizations();
        this.setupCardInteractions();
        this.setupMobileMenu();
    },

    /**
     * モバイルヘッダーのセットアップ
     */
    setupMobileHeader() {
        if (!this.elements.header && window.innerWidth <= 768) {
            this.elements.header = this.createMobileHeader();
        }
        
        if (this.elements.header) {
            this.state.headerHeight = this.elements.header.offsetHeight;
            
            // スマートヘッダー表示/非表示
            const scrollHandler = this.throttle(() => {
                const currentScrollY = window.scrollY;
                const scrollDelta = Math.abs(currentScrollY - this.state.lastScrollY);
                
                if (scrollDelta < 10) return;
                
                if (currentScrollY > this.state.lastScrollY && currentScrollY > this.state.headerHeight) {
                    this.elements.header.classList.add('gi-header-hidden');
                } else {
                    this.elements.header.classList.remove('gi-header-hidden');
                }
                
                this.state.lastScrollY = currentScrollY;
            }, 10);
            
            window.addEventListener('scroll', scrollHandler, { passive: true });
        }
    },

    /**
     * モバイルヘッダーの作成
     */
    createMobileHeader() {
        const header = document.createElement('div');
        header.className = 'gi-mobile-header';
        header.innerHTML = `
            <div class="gi-mobile-header-content">
                <a href="/" class="gi-logo-mobile">助成金検索</a>
                <div class="gi-search-container-mobile">
                    <input type="text" class="gi-search-input" placeholder="助成金を検索...">
                </div>
                <button class="gi-filter-trigger" aria-label="フィルター">
                    <i class="fas fa-sliders-h"></i>
                </button>
            </div>
        `;
        
        document.body.insertBefore(header, document.body.firstChild);
        
        // 新しい検索入力を要素キャッシュに追加
        const newSearchInput = header.querySelector('.gi-search-input');
        if (newSearchInput) {
            // 既存の検索設定を適用
            this.setupSearchForElement(newSearchInput);
        }
        
        return header;
    },

    /**
     * 単一要素への検索設定（モバイルヘッダー用）
     */
    setupSearchForElement(input) {
        const debouncedSearch = this.debounce((value) => {
            if (value.length >= this.config.searchMinLength) {
                this.showSearchSuggestions(value);
            }
        }, this.config.debounceDelay);

        input.addEventListener('input', (e) => debouncedSearch(e.target.value));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.executeSearch(e.target.value);
            }
        });
    },

    /**
     * モバイルメニュー
     */
    setupMobileMenu() {
        // モバイルメニュートグル
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mobile-menu-toggle, .gi-menu-toggle')) {
                this.elements.body.classList.toggle('gi-mobile-menu-open');
                e.target.classList.toggle('gi-menu-active');
            }

            // メニュー外クリックで閉じる
            if (!e.target.closest('.gi-mobile-menu, .mobile-menu, .mobile-menu-toggle, .gi-menu-toggle')) {
                this.elements.body.classList.remove('gi-mobile-menu-open');
                document.querySelectorAll('.mobile-menu-toggle, .gi-menu-toggle').forEach(toggle => {
                    toggle.classList.remove('gi-menu-active');
                });
            }
        });
    },

    /**
     * タッチ最適化
     */
    setupTouchOptimizations() {
        // タッチデバイス検出
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        if (isTouchDevice) {
            this.elements.body.classList.add('gi-touch-device');
            
            // タッチフィードバック
            this.setupTouchFeedback();
            
            // プルトゥリフレッシュ
            this.setupPullToRefresh();
        }
    },

    /**
     * タッチフィードバック
     */
    setupTouchFeedback() {
        const touchElements = document.querySelectorAll('button, .btn, .gi-filter-chip, .category-card, .grant-card');
        
        touchElements.forEach(element => {
            element.addEventListener('touchstart', () => {
                element.classList.add('gi-touch-active');
            });

            element.addEventListener('touchend', () => {
                setTimeout(() => {
                    element.classList.remove('gi-touch-active');
                }, 150);
            });
        });
    },

    /**
     * カードインタラクション
     */
    setupCardInteractions() {
        document.addEventListener('click', (e) => {
            const card = e.target.closest('.gi-grant-card-enhanced, .grant-card, .category-card');
            if (!card) return;

            // ボタンやリンク以外をクリックした場合、詳細ページに移動
            if (!e.target.matches('button, .btn, a, input, .gi-bookmark-btn')) {
                const link = card.querySelector('a[href]');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });
    },

    /**
     * プルトゥリフレッシュ
     */
    setupPullToRefresh() {
        let startY = 0;
        let currentY = 0;
        let isRefreshing = false;

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0 && !isRefreshing) {
                startY = e.touches[0].clientY;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (window.scrollY === 0 && startY > 0) {
                currentY = e.touches[0].clientY;
                const pullDistance = currentY - startY;
                
                if (pullDistance > 100 && !isRefreshing) {
                    this.showPullToRefreshIndicator();
                }
            }
        }, { passive: true });

        document.addEventListener('touchend', () => {
            if (currentY - startY > 100 && !isRefreshing) {
                this.triggerRefresh();
            }
            startY = 0;
            currentY = 0;
        });
    },

    /**
     * リフレッシュ実行
     */
    triggerRefresh() {
        this.showToast('更新中...', 'info');
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    },

    /**
     * プルトゥリフレッシュインジケーター表示
     */
    showPullToRefreshIndicator() {
        // 実装は簡略化（必要に応じて詳細実装）
        this.debug('Pull to refresh triggered');
    },

    /**
     * ==========================================================================
     * アニメーション・スクロール効果
     * ==========================================================================
     */
    setupAnimations() {
        this.setupScrollAnimations();
        this.setupSmoothScroll();
        this.setupBackToTop();
    },

    /**
     * スクロールアニメーション
     */
    setupScrollAnimations() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('gi-animated', 'gi-fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            
            const animateElements = document.querySelectorAll('.category-card, .grant-card, .prefecture-item');
            animateElements.forEach(el => {
                el.classList.add('gi-animate-on-scroll');
                observer.observe(el);
            });
        }
    },

    /**
     * スムーズスクロール
     */
    setupSmoothScroll() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="#"]');
            if (!link) return;

            const targetId = link.getAttribute('href');
            const target = document.querySelector(targetId);
            
            if (target) {
                e.preventDefault();
                const headerOffset = this.state.headerHeight || 80;
                const targetPosition = target.offsetTop - headerOffset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    },

    /**
     * トップへ戻るボタン
     */
    setupBackToTop() {
        let backToTopButton = document.querySelector('.gi-back-to-top, .back-to-top');
        
        if (!backToTopButton) {
            backToTopButton = document.createElement('button');
            backToTopButton.className = 'gi-back-to-top';
            backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
            backToTopButton.setAttribute('aria-label', 'ページトップへ戻る');
            document.body.appendChild(backToTopButton);
        }
        
        // スクロール監視
        const scrollHandler = this.throttle(() => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('gi-back-to-top-visible');
            } else {
                backToTopButton.classList.remove('gi-back-to-top-visible');
            }
        }, 100);
        
        window.addEventListener('scroll', scrollHandler, { passive: true });
        
        // クリックイベント
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    },

    /**
     * ==========================================================================
     * フォーム拡張
     * ==========================================================================
     */
    setupForms() {
        this.setupFormValidation();
        this.setupFormEnhancements();
    },

    /**
     * フォームバリデーション
     */
    setupFormValidation() {
        document.addEventListener('submit', (e) => {
            const form = e.target.closest('form');
            if (!form || form.classList.contains('gi-no-validation')) return;

            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('gi-field-error');
                    
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    field.classList.remove('gi-field-error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                this.showToast('必須項目を入力してください', 'error');
                
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
            }
        });

        // エラー状態のクリア
        document.addEventListener('input', (e) => {
            if (e.target.matches('input, textarea, select')) {
                e.target.classList.remove('gi-field-error');
            }
        });
    },

    /**
     * フォーム拡張機能
     */
    setupFormEnhancements() {
        // 自動保存（下書き機能）
        this.setupAutoSave();
        
        // ファイル選択の改善
        this.setupFileInputs();
    },

    /**
     * 自動保存機能
     */
    setupAutoSave() {
        const autoSaveFields = document.querySelectorAll('[data-autosave]');
        
        autoSaveFields.forEach(field => {
            const saveKey = field.dataset.autosave;
            
            // 保存されたデータを復元
            const savedValue = localStorage.getItem(`gi_autosave_${saveKey}`);
            if (savedValue && !field.value) {
                field.value = savedValue;
            }
            
            // 変更時に自動保存
            const saveHandler = this.debounce(() => {
                try {
                    localStorage.setItem(`gi_autosave_${saveKey}`, field.value);
                    this.debug(`Auto-saved: ${saveKey}`);
                } catch (e) {
                    this.debug('Auto-save error:', e);
                }
            }, 1000);
            
            field.addEventListener('input', saveHandler);
        });
    },

    /**
     * ファイル入力の改善
     */
    setupFileInputs() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', (e) => {
                const files = e.target.files;
                if (files.length > 0) {
                    const fileNames = Array.from(files).map(file => file.name).join(', ');
                    this.showToast(`選択されたファイル: ${fileNames}`, 'info');
                }
            });
        });
    },

    /**
     * ==========================================================================
     * アクセシビリティ・パフォーマンス
     * ==========================================================================
     */
    setupAccessibility() {
        this.setupKeyboardNavigation();
        this.setupFocusManagement();
        this.setupARIALabels();
    },

    /**
     * キーボードナビゲーション
     */
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Escapeキー
            if (e.key === 'Escape') {
                this.hideSearchSuggestions();
                this.hideFilterBottomSheet();
                this.closeModals();
            }
            
            // Ctrl+K で検索フォーカス
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                const searchInput = this.elements.searchInputs[0];
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    },

    /**
     * フォーカス管理
     */
    setupFocusManagement() {
        // タブトラップの実装
        this.setupTabTrap();
        
        // フォーカス可視化
        this.setupFocusVisibility();
    },

    /**
     * タブトラップ
     */
    setupTabTrap() {
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Tab') return;

            const modal = document.querySelector('.gi-modal-active, .gi-filter-bottom-sheet.active');
            if (!modal) return;

            const focusableElements = modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    },

    /**
     * フォーカス可視化
     */
    setupFocusVisibility() {
        // マウス使用時はフォーカスアウトラインを無効化
        document.addEventListener('mousedown', () => {
            this.elements.body.classList.add('gi-using-mouse');
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                this.elements.body.classList.remove('gi-using-mouse');
            }
        });
    },

    /**
     * ARIA ラベルの設定
     */
    setupARIALabels() {
        // 動的コンテンツのARIAラベル
        const updateARIALabels = () => {
            // 結果数の通知
            const resultsContainer = this.elements.grantsGrid;
            if (resultsContainer) {
                const count = resultsContainer.querySelectorAll('.grant-card').length;
                resultsContainer.setAttribute('aria-label', `${count}件の助成金が表示されています`);
            }
            
            // 比較アイテム数の通知
            if (this.elements.comparisonBar) {
                const count = this.state.comparisonItems.length;
                this.elements.comparisonBar.setAttribute('aria-label', `${count}件の助成金が比較リストに追加されています`);
            }
        };

        // 初期設定
        updateARIALabels();

        // 変更時に更新
        const observer = new MutationObserver(updateARIALabels);
        if (this.elements.grantsGrid) {
            observer.observe(this.elements.grantsGrid, { childList: true });
        }
    },

    /**
     * パフォーマンス最適化
     */
    setupPerformance() {
        this.setupLazyLoading();
        this.setupInfiniteScroll();
        this.setupImageOptimization();
    },

    /**
     * 遅延読み込み
     */
    setupLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        if (images.length === 0 || !('IntersectionObserver' in window)) return;

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('gi-image-loaded');
                    img.classList.remove('gi-image-loading');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });

        images.forEach(img => {
            img.classList.add('gi-image-loading');
            imageObserver.observe(img);
        });
    },

    /**
     * 無限スクロール
     */
    setupInfiniteScroll() {
        let page = 2;
        let isLoading = false;
        let hasMore = true;

        const loadMoreHandler = this.throttle(() => {
            if (isLoading || !hasMore) return;

            const scrollTop = window.pageYOffset;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            if (scrollTop + windowHeight >= documentHeight - 1000) {
                isLoading = true;
                
                this.ajax('gi_load_more_grants', { page })
                    .then(response => {
                        if (response.success && response.data.grants && response.data.grants.length > 0) {
                            const container = this.elements.grantsGrid;
                            if (container) {
                                const newCards = response.data.grants.map(grant => 
                                    this.renderGrantCard(grant)
                                ).join('');
                                container.insertAdjacentHTML('beforeend', newCards);
                                
                                // 新しいカードにイベントを設定
                                this.setupNewCardEvents(container);
                            }
                            page++;
                        } else {
                            hasMore = false;
                        }
                    })
                    .catch(error => {
                        console.error('Load more error:', error);
                        hasMore = false;
                    })
                    .finally(() => {
                        isLoading = false;
                    });
            }
        }, 200);

        window.addEventListener('scroll', loadMoreHandler, { passive: true });
    },

    /**
     * 新しいカードイベントの設定
     */
    setupNewCardEvents(container) {
        // 新しく追加された画像の遅延読み込み
        const newImages = container.querySelectorAll('img[data-src]:not(.gi-image-loading)');
        newImages.forEach(img => {
            img.classList.add('gi-image-loading');
            // 既存の画像オブザーバーがあれば再利用
        });

        // 新しいチェックボックスの状態復元
        this.state.comparisonItems.forEach(item => {
            const checkbox = container.querySelector(`[data-grant-id="${item.id}"]:not([data-restored])`);
            if (checkbox) {
                checkbox.checked = true;
                checkbox.dataset.restored = 'true';
            }
        });
    },

    /**
     * 画像最適化
     */
    setupImageOptimization() {
        // WebP対応チェック
        const supportsWebP = this.checkWebPSupport();
        
        if (supportsWebP) {
            this.elements.body.classList.add('gi-supports-webp');
        }
    },

    /**
     * WebP対応チェック
     */
    checkWebPSupport() {
        try {
            return document.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') === 0;
        } catch (e) {
            return false;
        }
    },

    /**
     * ==========================================================================
     * UI更新・レンダリング
     * ==========================================================================
     */

    /**
     * 検索結果の更新
     */
    updateSearchResults(data) {
        const container = this.elements.grantsGrid;
        if (!container) return;

        if (data.grants && data.grants.length > 0) {
            container.innerHTML = data.grants.map(grant => this.renderGrantCard(grant)).join('');
            this.setupNewCardEvents(container);
        } else {
            container.innerHTML = `
                <div class="gi-no-results">
                    <div class="gi-no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>該当する助成金が見つかりませんでした</h3>
                    <p>検索条件を変更して再度お試しください。</p>
                </div>
            `;
        }

        // 結果数の更新
        const countElement = document.querySelector('.gi-results-count, .results-count');
        if (countElement && data.total !== undefined) {
            countElement.textContent = `${data.total}件の助成金`;
        }
    },

    /**
     * 助成金カードのレンダリング
     */
    renderGrantCard(grant) {
        return `
            <div class="gi-grant-card-enhanced grant-card" data-grant-id="${grant.id}">
                <div class="gi-card-image-container">
                    <img src="${grant.image || '/assets/images/default-grant.jpg'}" 
                         alt="${this.escapeHtml(grant.title)}" 
                         class="gi-card-image"
                         loading="lazy">
                    <div class="gi-card-badges">
                        ${grant.is_new ? '<span class="gi-card-badge gi-badge-new">新着</span>' : ''}
                        ${grant.is_featured ? '<span class="gi-card-badge gi-badge-featured">注目</span>' : ''}
                    </div>
                    <div class="gi-card-compare">
                        <label class="gi-compare-checkbox-container">
                            <input type="checkbox" 
                                   class="grant-compare-checkbox"
                                   data-grant-id="${grant.id}"
                                   data-grant-title="${this.escapeHtml(grant.title)}">
                            <span class="gi-compare-checkbox-label">比較</span>
                        </label>
                    </div>
                </div>
                <div class="gi-card-content">
                    <h3 class="gi-card-title">${this.escapeHtml(grant.title)}</h3>
                    <div class="gi-card-meta">
                        <div class="gi-card-amount">${grant.amount ? `${grant.amount}円` : '金額未定'}</div>
                        <div class="gi-card-organization">${this.escapeHtml(grant.organization || '')}</div>
                        <div class="gi-card-deadline">${grant.deadline ? `締切: ${grant.deadline}` : ''}</div>
                    </div>
                    ${grant.excerpt ? `<p class="gi-card-excerpt">${this.escapeHtml(grant.excerpt)}</p>` : ''}
                    <div class="gi-card-actions">
                        <a href="${grant.url || '#'}" class="gi-btn gi-btn-primary gi-card-cta">詳細を見る</a>
                        <button class="gi-btn gi-btn-secondary gi-bookmark-btn" 
                                data-grant-id="${grant.id}"
                                aria-label="ブックマーク">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * ==========================================================================
     * フィルターUI（ボトムシート）
     * ==========================================================================
     */

    /**
     * フィルターボトムシート表示
     */
    showFilterBottomSheet() {
        let sheet = document.querySelector('.gi-filter-bottom-sheet');
        
        if (!sheet) {
            sheet = this.createFilterBottomSheet();
            document.body.appendChild(sheet);
        }
        
        // オーバーレイ
        const overlay = document.createElement('div');
        overlay.className = 'gi-filter-overlay';
        overlay.addEventListener('click', () => this.hideFilterBottomSheet());
        document.body.appendChild(overlay);
        
        // アニメーション
        requestAnimationFrame(() => {
            sheet.classList.add('gi-filter-sheet-active');
            overlay.classList.add('gi-overlay-active');
            this.elements.body.classList.add('gi-filter-sheet-open');
        });
    },

    /**
     * フィルターボトムシート非表示
     */
    hideFilterBottomSheet() {
        const sheet = document.querySelector('.gi-filter-bottom-sheet');
        const overlay = document.querySelector('.gi-filter-overlay');
        
        if (sheet) {
            sheet.classList.remove('gi-filter-sheet-active');
        }
        
        if (overlay) {
            overlay.classList.remove('gi-overlay-active');
        }
        
        this.elements.body.classList.remove('gi-filter-sheet-open');
        
        setTimeout(() => {
            if (sheet && sheet.parentNode) {
                sheet.parentNode.removeChild(sheet);
            }
            if (overlay && overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    },

    /**
     * フィルターボトムシートの作成
     */
    createFilterBottomSheet() {
        const sheet = document.createElement('div');
        sheet.className = 'gi-filter-bottom-sheet';
        sheet.innerHTML = `
            <div class="gi-filter-sheet-header">
                <h3 class="gi-filter-sheet-title">フィルター</h3>
                <button class="gi-filter-sheet-close gi-btn-icon" aria-label="閉じる">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="gi-filter-sheet-content">
                <div class="gi-filter-group">
                    <div class="gi-filter-group-title">カテゴリー</div>
                    <div class="gi-filter-options">
                        <button class="gi-filter-option" data-filter="category" data-value="business">
                            <span>事業助成</span>
                        </button>
                        <button class="gi-filter-option" data-filter="category" data-value="research">
                            <span>研究助成</span>
                        </button>
                        <button class="gi-filter-option" data-filter="category" data-value="education">
                            <span>教育助成</span>
                        </button>
                    </div>
                </div>
                <div class="gi-filter-group">
                    <div class="gi-filter-group-title">都道府県</div>
                    <div class="gi-filter-options">
                        <button class="gi-filter-option" data-filter="prefecture" data-value="tokyo">
                            <span>東京都</span>
                        </button>
                        <button class="gi-filter-option" data-filter="prefecture" data-value="osaka">
                            <span>大阪府</span>
                        </button>
                        <button class="gi-filter-option" data-filter="prefecture" data-value="kanagawa">
                            <span>神奈川県</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="gi-filter-sheet-footer">
                <button class="gi-btn gi-btn-secondary gi-btn-filter-clear">クリア</button>
                <button class="gi-btn gi-btn-primary gi-btn-filter-apply">適用</button>
            </div>
        `;

        // フィルターオプションのイベント
        sheet.querySelectorAll('.gi-filter-option').forEach(option => {
            option.addEventListener('click', () => {
                option.classList.toggle('gi-filter-option-selected');
            });
        });

        return sheet;
    },

    /**
     * ==========================================================================
     * ユーティリティ・ヘルパー
     * ==========================================================================
     */

    /**
     * モーダルを閉じる
     */
    closeModals() {
        // 各種モーダルやポップアップを閉じる
        this.hideSearchSuggestions();
        this.hideFilterBottomSheet();
        
        // 他のモーダルがあれば追加
        document.querySelectorAll('.gi-modal-active, .gi-popup-active').forEach(modal => {
            modal.classList.remove('gi-modal-active', 'gi-popup-active');
        });
    }
};

/**
 * =============================================================================
 * 自動初期化
 * =============================================================================
 */

// 初期化実行
GrantInsight.init();

// グローバルアクセス用（後方互換性とデバッグ用）
window.GrantInsight = GrantInsight;

/**
 * =============================================================================
 * CSS-in-JS スタイル（最小限）
 * =============================================================================
 */

// 動的に必要なスタイルを追加
document.addEventListener('DOMContentLoaded', () => {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = `
        /* Toast通知スタイル */
        .gi-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            background: var(--mb-white);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .gi-toast-show {
            transform: translateX(0);
        }
        
        .gi-toast-content {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .gi-toast-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: var(--mb-gray-500);
        }
        
        /* アニメーション */
        .gi-animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .gi-animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* タッチフィードバック */
        .gi-touch-active {
            transform: scale(0.98);
            opacity: 0.8;
        }
        
        /* フォーカス管理 */
        .gi-using-mouse *:focus {
            outline: none;
        }
        
        /* エラー状態 */
        .gi-field-error {
            border-color: var(--accent-red) !important;
            box-shadow: 0 0 0 2px rgba(230, 0, 18, 0.1);
        }
    `;
    document.head.appendChild(styleSheet);
});

/**
 * =============================================================================
 * エクスポート（モジュール対応）
 * =============================================================================
 */

// ES6モジュール対応
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GrantInsight;
}

// AMD対応
if (typeof define === 'function' && define.amd) {
    define(() => GrantInsight);
}