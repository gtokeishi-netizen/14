<?php
/**
 * Template for displaying single posts
 * Tailwind CSS Play CDN対応版
 */

get_header(); ?>


<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#f0f9ff',
                        100: '#e0f2fe',
                        500: '#0ea5e9',
                        600: '#0284c7',
                        700: '#0369a1',
                        900: '#0c4a6e'
                    },
                    accent: {
                        50: '#fffbeb',
                        500: '#f59e0b',
                        600: '#d97706'
                    },
                    success: {
                        50: '#f0fdf4',
                        500: '#22c55e',
                        600: '#16a34a'
                    }
                },
                animation: {
                    'fade-in': 'fadeIn 0.6s ease-out',
                    'slide-up': 'slideUp 0.8s ease-out',
                    'bounce-gentle': 'bounceGentle 1s ease-out',
                    'scale-in': 'scaleIn 0.5s ease-out'
                },
                keyframes: {
                    fadeIn: {
                        '0%': { opacity: '0' },
                        '100%': { opacity: '1' }
                    },
                    slideUp: {
                        '0%': { opacity: '0', transform: 'translateY(30px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' }
                    },
                    bounceGentle: {
                        '0%': { transform: 'scale(0.95)' },
                        '50%': { transform: 'scale(1.02)' },
                        '100%': { transform: 'scale(1)' }
                    },
                    scaleIn: {
                        '0%': { opacity: '0', transform: 'scale(0.9)' },
                        '100%': { opacity: '1', transform: 'scale(1)' }
                    }
                }
            }
        }
    }
</script>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('animate-fade-in'); ?>>
                
                <!-- ヒーローセクション -->
                <header class="relative bg-gradient-to-r from-primary-600 to-accent-600 text-white overflow-hidden">
                    <div class="absolute inset-0 bg-black/20"></div>
                    
                    <!-- 背景パターン -->
                    <div class="absolute inset-0 opacity-10">
                        <svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" fill-rule="evenodd">
                                <g fill="currentColor" fill-opacity="0.1">
                                    <rect x="11" y="11" width="38" height="38" rx="4"/>
                                    <circle cx="30" cy="30" r="6"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="absolute inset-0">
                            <?php 
                            $thumbnail_id = get_post_thumbnail_id();
                            $thumbnail_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: get_the_title();
                            ?>
                            <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>" 
                                 alt="<?php echo esc_attr($thumbnail_alt); ?>"
                                 class="w-full h-full object-cover opacity-30"
                                 loading="eager">
                        </div>
                    <?php endif; ?>

                    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
                        <div class="animate-slide-up">
                            <!-- カテゴリとタグ -->
                            <div class="flex flex-wrap gap-3 mb-6">
                                <?php
                                $categories = get_the_category();
                                if ($categories) :
                                    foreach ($categories as $category) :
                                ?>
                                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>"
                                       class="inline-flex items-center px-4 py-2 rounded-full 
                                              bg-white/20 backdrop-blur-sm text-white hover:bg-white/30
                                              transition-all duration-200 border border-white/30">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        </svg>
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                <?php 
                                    endforeach;
                                endif;

                                $tags = get_the_tags();
                                if ($tags) :
                                    foreach (array_slice($tags, 0, 3) as $tag) :
                                ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                       class="inline-flex items-center px-3 py-1 rounded-full 
                                              bg-accent-500/20 backdrop-blur-sm text-white hover:bg-accent-500/30
                                              transition-all duration-200 text-sm">
                                        #<?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </div>

                            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                                <?php the_title(); ?>
                            </h1>

                            <?php if (has_excerpt()) : ?>
                                <p class="text-xl md:text-2xl text-white/90 leading-relaxed mb-8">
                                    <?php the_excerpt(); ?>
                                </p>
                            <?php endif; ?>

                            <!-- 記事メタ情報 -->
                            <div class="flex flex-wrap items-center gap-6 text-white/80">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium"><?php echo get_the_author(); ?></span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                    <span><?php echo get_the_date('Y年n月j日'); ?></span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    <span>読了時間: <?php echo ceil(str_word_count(strip_tags(get_the_content())) / 200); ?>分</span>
                                </div>

                                <!-- ソーシャルシェア -->
                                <div class="flex items-center gap-2 ml-auto">
                                    <span class="text-sm">シェア:</span>
                                    <button onclick="shareArticle('twitter')" 
                                            class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                    </button>
                                    <button onclick="shareArticle('facebook')" 
                                            class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    </button>
                                    <button onclick="shareArticle('line')" 
                                            class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.070 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 装飾的な要素 -->
                    <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
                </header>

                <!-- メインコンテンツ -->
                <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    
                    <!-- 目次（長い記事の場合） -->
                    <?php
                    $content = get_the_content();
                    $headings = array();
                    preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/', $content, $matches);
                    if (count($matches[0]) > 3) :
                    ?>
                        <div class="bg-primary-50 rounded-2xl border border-primary-200 p-8 mb-12 animate-scale-in">
                            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <svg class="w-6 h-6 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                </svg>
                                目次
                            </h2>
                            <ul class="space-y-2">
                                <?php 
                                foreach ($matches[1] as $index => $heading) :
                                    $heading_text = strip_tags($heading);
                                    $heading_id = 'heading-' . ($index + 1);
                                ?>
                                    <li>
                                        <a href="#<?php echo $heading_id; ?>" 
                                           class="flex items-center gap-2 text-primary-700 hover:text-primary-800 
                                                  hover:bg-primary-100 rounded-lg px-3 py-2 transition-all duration-200">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            <?php echo esc_html($heading_text); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- AI要約セクション - User Request: AIアシスタント機能追加 -->
                    <?php
                    $ai_summary = get_post_meta(get_the_ID(), '_ai_summary', true);
                    if ($ai_summary) :
                    ?>
                        <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl p-8 md:p-10 mb-12 animate-scale-in border-2 border-gray-800">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <svg class="w-7 h-7 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-3">
                                        AI記事要約
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-white text-xs font-medium border border-white/20">
                                            <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                            </svg>
                                            AI生成
                                        </span>
                                    </h3>
                                    <p class="text-sm text-gray-400">この記事の内容をAIが自動要約しました</p>
                                </div>
                            </div>
                            <div class="prose prose-invert prose-lg max-w-none">
                                <div class="text-gray-200 leading-relaxed space-y-3 text-base">
                                    <?php echo wp_kses_post(wpautop($ai_summary)); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- AI関連補助金提案 - User Request: AIアシスタント機能追加 -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-12 animate-scale-in">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-12 h-12 bg-black rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">この記事に関連する補助金</h3>
                                <p class="text-sm text-gray-600">記事の内容から関連する可能性のある補助金・助成金を自動提案します</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4" id="ai-related-grants">
                            <?php
                            // 記事のカテゴリーとタグから関連する補助金を取得
                            $post_categories = get_the_category();
                            $post_tags = get_the_tags();
                            
                            $category_ids = array();
                            if ($post_categories) {
                                foreach ($post_categories as $cat) {
                                    $category_ids[] = $cat->term_id;
                                }
                            }
                            
                            // 関連する補助金を検索
                            $related_grants_args = array(
                                'post_type' => 'grant',
                                'posts_per_page' => 3,
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC'
                            );
                            
                            if (!empty($category_ids)) {
                                $related_grants_args['tax_query'] = array(
                                    array(
                                        'taxonomy' => 'grant_category',
                                        'field' => 'term_id',
                                        'terms' => $category_ids,
                                        'operator' => 'IN'
                                    )
                                );
                            }
                            
                            $related_grants = new WP_Query($related_grants_args);
                            
                            if ($related_grants->have_posts()) :
                                while ($related_grants->have_posts()) : $related_grants->the_post();
                                    $grant_amount = get_post_meta(get_the_ID(), '_grant_amount', true);
                                    $grant_deadline = get_post_meta(get_the_ID(), '_grant_deadline', true);
                                    $grant_prefecture = get_post_meta(get_the_ID(), '_grant_prefecture', true);
                            ?>
                                <div class="flex items-start gap-4 p-5 rounded-xl border-2 border-gray-100 hover:border-black hover:shadow-md transition-all duration-200 group">
                                    <div class="w-10 h-10 bg-gray-100 group-hover:bg-black rounded-lg flex items-center justify-center flex-shrink-0 transition-colors duration-200">
                                        <svg class="w-5 h-5 text-gray-600 group-hover:text-white transition-colors duration-200" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-900 mb-2 group-hover:text-black transition-colors duration-200">
                                            <a href="<?php the_permalink(); ?>" class="hover:underline">
                                                <?php the_title(); ?>
                                            </a>
                                        </h4>
                                        <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                            <?php if ($grant_amount) : ?>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <?php echo esc_html($grant_amount); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($grant_deadline) : ?>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <?php echo esc_html($grant_deadline); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($grant_prefecture) : ?>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <?php echo esc_html($grant_prefecture); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="<?php the_permalink(); ?>" class="flex-shrink-0 w-8 h-8 bg-gray-100 group-hover:bg-black rounded-lg flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-4 h-4 text-gray-600 group-hover:text-white transition-colors duration-200" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                            <?php
                                endwhile;
                                wp_reset_postdata();
                            else :
                            ?>
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm">現在、関連する補助金情報はありません</p>
                                    <a href="/" class="inline-flex items-center gap-2 mt-4 text-sm font-medium text-black hover:underline">
                                        補助金を検索する
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a href="/" class="inline-flex items-center gap-2 text-sm font-medium text-black hover:underline">
                                すべての補助金を見る
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- 記事本文 -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 md:p-12 mb-12 animate-slide-up">
                        <div class="prose prose-lg max-w-none 
                                  prose-headings:text-gray-900 prose-headings:font-bold
                                  prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-6
                                  prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-4
                                  prose-p:text-gray-700 prose-p:leading-relaxed prose-p:mb-6
                                  prose-a:text-primary-600 prose-a:no-underline hover:prose-a:underline
                                  prose-strong:text-gray-900 prose-strong:font-semibold
                                  prose-ul:text-gray-700 prose-ol:text-gray-700
                                  prose-li:mb-2 prose-li:leading-relaxed
                                  prose-blockquote:border-l-4 prose-blockquote:border-primary-500 
                                  prose-blockquote:bg-primary-50 prose-blockquote:rounded-r-lg 
                                  prose-blockquote:p-6 prose-blockquote:not-italic
                                  prose-code:bg-gray-100 prose-code:rounded prose-code:px-2 prose-code:py-1
                                  prose-pre:bg-gray-900 prose-pre:rounded-xl prose-pre:p-6
                                  prose-img:rounded-xl prose-img:shadow-lg
                                  prose-table:w-full prose-th:bg-gray-50 prose-th:font-semibold
                                  prose-td:border-gray-200 prose-th:border-gray-200">
                            <?php
                            // 目次用のIDを見出しに追加
                            $content = get_the_content();
                            if (count($matches[0]) > 3) {
                                foreach ($matches[0] as $index => $full_heading) {
                                    $heading_id = 'heading-' . ($index + 1);
                                    $new_heading = str_replace('>', ' id="' . $heading_id . '">', $full_heading);
                                    $content = str_replace($full_heading, $new_heading, $content);
                                }
                            }
                            echo apply_filters('the_content', $content);

                            wp_link_pages(array(
                                'before' => '<div class="flex flex-wrap gap-2 mt-12 pt-8 border-t border-gray-200">',
                                'after'  => '</div>',
                                'link_before' => '<span class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-100 text-primary-700 hover:bg-primary-200 transition-colors duration-200 font-medium">',
                                'link_after' => '</span>',
                            ));
                            ?>
                        </div>
                    </div>

                    <!-- タグクラウド -->
                    <?php if ($tags) : ?>
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-12 animate-scale-in">
                            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <svg class="w-6 h-6 text-accent-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                                関連タグ
                            </h3>
                            <div class="flex flex-wrap gap-3">
                                <?php foreach ($tags as $tag) : ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                       class="inline-flex items-center px-4 py-2 bg-accent-100 text-accent-800 
                                              rounded-full hover:bg-accent-200 transition-colors duration-200 
                                              text-sm font-medium">
                                        #<?php echo esc_html($tag->name); ?>
                                        <span class="ml-2 text-xs bg-accent-200 text-accent-700 rounded-full px-2 py-0.5">
                                            <?php echo $tag->count; ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 著者情報 -->
                    <?php 
                    $author_bio = get_the_author_meta('description');
                    if ($author_bio) :
                    ?>
                        <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-2xl border border-gray-200 p-8 mb-12 animate-slide-up">
                            <div class="flex items-start gap-6">
                                <div class="flex-shrink-0">
                                    <?php echo get_avatar(get_the_author_meta('ID'), 80, '', '', array('class' => 'w-20 h-20 rounded-full border-4 border-white shadow-lg')); ?>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 mb-3 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                        </svg>
                                        <?php echo get_the_author(); ?>
                                    </h3>
                                    <p class="text-gray-700 leading-relaxed mb-4"><?php echo esc_html($author_bio); ?></p>
                                    <div class="flex gap-4">
                                        <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"
                                           class="inline-flex items-center px-4 py-2 bg-primary-600 text-white 
                                                  rounded-lg hover:bg-primary-700 transition-colors duration-200 text-sm font-medium">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            他の記事を見る
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 関連記事 -->
                    <?php
                    $related_posts = get_posts(array(
                        'posts_per_page' => 3,
                        'exclude' => array(get_the_ID()),
                        'category__in' => wp_get_post_categories(get_the_ID()),
                        'orderby' => 'rand'
                    ));
                    
                    if ($related_posts) :
                    ?>
                        <div class="mt-16 pt-12 border-t border-gray-200">
                            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center flex items-center justify-center gap-3">
                                <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                                </svg>
                                関連記事
                            </h2>
                            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                                <?php foreach ($related_posts as $post) : setup_postdata($post); ?>
                                    <a href="<?php the_permalink(); ?>" 
                                       class="group block bg-white border border-gray-200 rounded-xl overflow-hidden 
                                              hover:shadow-lg hover:border-primary-300 transform hover:-translate-y-2 
                                              transition-all duration-300">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="aspect-video overflow-hidden bg-gradient-to-br from-primary-100 to-accent-100">
                                                <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium')); ?>" 
                                                     alt="<?php echo esc_attr(get_the_title()); ?>"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                     loading="lazy">
                                            </div>
                                        <?php else : ?>
                                            <div class="aspect-video bg-gradient-to-br from-primary-100 to-accent-100 flex items-center justify-center">
                                                <svg class="w-16 h-16 text-primary-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="p-6">
                                            <?php 
                                            $post_categories = get_the_category();
                                            if ($post_categories) :
                                            ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-100 text-primary-800 text-xs font-medium mb-3">
                                                    <?php echo esc_html($post_categories[0]->name); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <h3 class="font-bold text-gray-900 group-hover:text-primary-600 
                                                     transition-colors duration-200 mb-3 line-clamp-2 leading-tight">
                                                <?php the_title(); ?>
                                            </h3>
                                            
                                            <?php if (has_excerpt()) : ?>
                                                <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                                                    <?php the_excerpt(); ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center text-gray-500 text-xs gap-4">
                                                    <span><?php echo get_the_date('n/j'); ?></span>
                                                    <span><?php echo ceil(str_word_count(strip_tags(get_the_content())) / 200); ?>分</span>
                                                </div>
                                                
                                                <div class="flex items-center text-primary-600 text-sm font-medium">
                                                    <span>続きを読む</span>
                                                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-200" 
                                                         fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; wp_reset_postdata(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- コメントセクション -->
                    <?php if (comments_open() || get_comments_number()) : ?>
                        <div class="mt-16 pt-12 border-t border-gray-200">
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 md:p-12">
                                <?php comments_template(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- CTA セクション -->
                    <div class="mt-16 text-center">
                        <div class="bg-gradient-to-r from-primary-600 to-accent-600 rounded-2xl p-12 text-white">
                            <h2 class="text-3xl font-bold mb-4">他の記事もチェック</h2>
                            <p class="text-xl mb-8 opacity-90">役立つ情報をお届けしています</p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="/blog" 
                                   class="inline-flex items-center px-8 py-4 bg-white text-primary-600 
                                          font-bold rounded-lg hover:bg-gray-100 transform hover:scale-105 
                                          transition-all duration-200 shadow-lg">
                                    記事一覧を見る
                                    <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a href="/newsletter" 
                                   class="inline-flex items-center px-8 py-4 bg-transparent border-2 border-white 
                                          text-white font-bold rounded-lg hover:bg-white hover:text-primary-600 
                                          transform hover:scale-105 transition-all duration-200">
                                    ニュースレター登録
                                    <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ソーシャルシェア機能
    window.shareArticle = function(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        const text = encodeURIComponent(document.querySelector('meta[property="og:description"]')?.content || '');
        
        let shareUrl = '';
        
        switch(platform) {
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'line':
                shareUrl = `https://social-plugins.line.me/lineit/share?url=${url}`;
                break;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    };

    // 目次のスムーズスクロール
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
            }
        });
    });

    // 読み上げ進行状況インジケータ
    window.addEventListener('scroll', function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        
        let progressBar = document.getElementById('reading-progress');
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.id = 'reading-progress';
            progressBar.className = 'fixed top-0 left-0 h-1 bg-gradient-to-r from-primary-600 to-accent-600 z-50 transition-all duration-150';
            document.body.appendChild(progressBar);
        }
        progressBar.style.width = scrolled + '%';
    });

    // 画像の遅延読み込みとフェードイン効果
    const images = document.querySelectorAll('img[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.style.opacity = '0';
                img.addEventListener('load', () => {
                    img.style.transition = 'opacity 0.5s ease-in-out';
                    img.style.opacity = '1';
                });
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // フローティング目次（長い記事用）
    const tocElement = document.querySelector('.bg-primary-50');
    if (tocElement && window.innerWidth > 1024) {
        let isSticky = false;
        const originalTop = tocElement.getBoundingClientRect().top + window.scrollY;
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.scrollY;
            
            if (scrollTop > originalTop + 100 && !isSticky) {
                isSticky = true;
                const floatingToc = tocElement.cloneNode(true);
                floatingToc.id = 'floating-toc';
                floatingToc.className = 'fixed top-20 right-8 w-80 bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-200 p-6 z-40 max-h-80 overflow-y-auto';
                document.body.appendChild(floatingToc);
            } else if (scrollTop <= originalTop && isSticky) {
                isSticky = false;
                const floatingToc = document.getElementById('floating-toc');
                if (floatingToc) {
                    floatingToc.remove();
                }
            }
        });
    }

    // テキストハイライト機能
    const selectableText = document.querySelectorAll('.prose p, .prose li');
    selectableText.forEach(element => {
        element.addEventListener('mouseup', function() {
            const selection = window.getSelection();
            if (selection.toString().length > 0) {
                // 選択されたテキストの処理（将来的にメモ機能等に活用可能）
            }
        });
    });
});
</script>

<?php get_footer(); ?>
