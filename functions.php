<?php
/**
 * Origin theme functions.
 *
 * Copyright (C) 2026 Morton Li.
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, version 3 of the License.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('ORIGIN_VERSION', wp_get_theme()->get('Version'));

if (! function_exists('origin_setup')) {
	/**
	 * 注册主题能力、菜单和编辑器支持。
	 */
	function origin_setup(): void {
		load_theme_textdomain('origin', get_template_directory() . '/languages');

		add_theme_support('automatic-feed-links');
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support('responsive-embeds');
		add_theme_support('editor-styles');
		add_theme_support(
			'html5',
			array(
				'caption',
				'comment-form',
				'comment-list',
				'gallery',
				'navigation-widgets',
				'search-form',
				'style',
				'script',
			)
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 96,
				'width'       => 320,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		register_nav_menus(
			array(
				'primary' => __('主导航', 'origin'),
				'footer'  => __('页脚导航', 'origin'),
			)
		);
	}
}
add_action('after_setup_theme', 'origin_setup');

/**
 * 设置默认内容宽度，供嵌入内容和媒体计算最大宽度。
 */
function origin_content_width(): void {
	$GLOBALS['content_width'] = 760;
}
add_action('after_setup_theme', 'origin_content_width', 0);

/**
 * 加载前台样式与脚本。
 */
function origin_enqueue_assets(): void {
	wp_enqueue_style('origin-style', get_stylesheet_uri(), array(), ORIGIN_VERSION);
	wp_enqueue_style('origin-screen', get_theme_file_uri('/assets/css/screen.css'), array('origin-style'), ORIGIN_VERSION);
	wp_enqueue_script(
		'origin-main',
		get_theme_file_uri('/assets/js/main.js'),
		array(),
		ORIGIN_VERSION,
		array(
			'in_footer' => true,
		)
	);
}
add_action('wp_enqueue_scripts', 'origin_enqueue_assets');

/**
 * 估算文章阅读时间。
 *
 * @param int|null $post_id 文章 ID。为空时读取当前循环内文章。
 * @return string 本地化后的阅读时间文本。
 */
function origin_get_reading_time(?int $post_id = null): string {
	$post_id = $post_id ?: get_the_ID();

	if (! $post_id) {
		return __('1 分钟阅读', 'origin');
	}

	// $plain_text 是去除短代码和 HTML 后的正文，用于稳定估算阅读量。
	$plain_text = wp_strip_all_tags(strip_shortcodes((string) get_post_field('post_content', $post_id)));
	preg_match_all('/[\p{Han}]|[A-Za-z0-9]+/u', $plain_text, $matches);

	// $token_count 将中文单字和西文单词都视为阅读单位，兼顾中英文混排文章。
	$token_count = max(1, count($matches[0]));
	$minutes     = max(1, (int) ceil($token_count / 250));

	return sprintf(
		/* translators: %s: Estimated reading minutes. */
		__('%s 分钟阅读', 'origin'),
		number_format_i18n($minutes)
	);
}

/**
 * 判断文章是否应显示精选标识。
 *
 * @param int|null $post_id 文章 ID。为空时读取当前循环内文章。
 * @return bool 是精选文章时返回 true。
 */
function origin_is_featured_post(?int $post_id = null): bool {
	$post_id = $post_id ?: get_the_ID();

	if (! $post_id) {
		return false;
	}

	return is_sticky($post_id) || has_tag('featured', $post_id);
}

/**
 * 读取文章主分类名称。
 *
 * @param int|null $post_id 文章 ID。为空时读取当前循环内文章。
 * @return string 主分类名称；无分类时返回空字符串。
 */
function origin_get_primary_category_name(?int $post_id = null): string {
	$post_id = $post_id ?: get_the_ID();

	if (! $post_id) {
		return '';
	}

	// $categories 保持 WordPress 默认排序，第一项作为轻量主分类展示。
	$categories = get_the_category($post_id);

	return $categories ? $categories[0]->name : '';
}

/**
 * 输出首页文章流的下一页链接。
 */
function origin_the_load_more_pagination(): void {
	$next_link = get_next_posts_link(__('加载更多', 'origin'));

	if (! $next_link) {
		return;
	}
	?>
	<nav class="load-more" aria-label="<?php esc_attr_e('文章分页', 'origin'); ?>">
		<?php echo str_replace('<a ', '<a class="button button-secondary" ', $next_link); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</nav>
	<?php
}

/**
 * 输出可访问的 SVG 图标。
 *
 * @param string $name 图标名称。
 */
function origin_icon(string $name): void {
	$icons = array(
		'search'        => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>',
		'star'          => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 16.9l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"></path></svg>',
		'chevron-right' => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="m9 18 6-6-6-6"></path></svg>',
		'arrow-left'    => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="m15 18-6-6 6-6"></path></svg>',
		'arrow-right'   => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="m9 18 6-6-6-6"></path></svg>',
	);

	if (! isset($icons[$name])) {
		return;
	}

	echo $icons[$name]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
