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
 * 读取导航布局设置。
 *
 * @return string 导航布局标识。
 */
function origin_get_navigation_layout(): string {
	$layout = (string) get_theme_mod('origin_navigation_layout', 'left-logo');

	if (! in_array($layout, array('left-logo', 'center-menu'), true)) {
		return 'left-logo';
	}

	return $layout;
}

/**
 * 注册主题自定义设置。
 *
 * @param WP_Customize_Manager $wp_customize 自定义器实例。
 */
function origin_customize_register(WP_Customize_Manager $wp_customize): void {
	$wp_customize->add_section(
		'origin_layout',
		array(
			'title'    => __('布局', 'origin'),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'origin_navigation_layout',
		array(
			'default'           => 'left-logo',
			'sanitize_callback' => 'origin_sanitize_navigation_layout',
		)
	);

	$wp_customize->add_control(
		'origin_navigation_layout',
		array(
			'choices' => array(
				'left-logo'   => __('Logo 与导航左对齐', 'origin'),
				'center-menu' => __('导航居中', 'origin'),
			),
			'label'   => __('导航布局', 'origin'),
			'section' => 'origin_layout',
			'type'    => 'select',
		)
	);
}
add_action('customize_register', 'origin_customize_register');

/**
 * 清理导航布局设置。
 *
 * @param string $value 待清理的设置值。
 * @return string 安全的设置值。
 */
function origin_sanitize_navigation_layout(string $value): string {
	return in_array($value, array('left-logo', 'center-menu'), true) ? $value : 'left-logo';
}

/**
 * 读取当前页面 URL，用于认证完成后回到原页面。
 *
 * @return string 当前页面 URL。
 */
function origin_get_current_url(): string {
	$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/';
	$request_uri = str_replace(array("\r", "\n"), '', $request_uri);

	if ('' === $request_uri || '/' !== $request_uri[0]) {
		$request_uri = '/';
	}

	return home_url($request_uri);
}

/**
 * 读取认证跳转地址。
 *
 * @return string 安全的跳转地址。
 */
function origin_get_auth_redirect_url(): string {
	$redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : (wp_get_referer() ?: home_url('/'));
	$redirect_to = remove_query_arg(array('origin_auth', 'origin_auth_panel'), $redirect_to);

	return wp_validate_redirect($redirect_to, home_url('/'));
}

/**
 * 跳转回前台页面并携带认证状态。
 *
 * @param string $status 认证状态。
 * @param string $panel  应打开的认证面板。
 */
function origin_redirect_with_auth_status(string $status, string $panel = 'login'): void {
	wp_safe_redirect(
		add_query_arg(
			array(
				'origin_auth'       => sanitize_key($status),
				'origin_auth_panel' => sanitize_key($panel),
			),
			origin_get_auth_redirect_url()
		)
	);
	exit;
}

/**
 * 读取认证提示文本。
 *
 * @return string 认证提示文本。
 */
function origin_get_auth_notice(): string {
	$status = isset($_GET['origin_auth']) ? sanitize_key(wp_unslash($_GET['origin_auth'])) : '';

	$messages = array(
		'login_missing'        => __('请输入账号和密码。', 'origin'),
		'login_failed'         => __('账号或密码不正确。', 'origin'),
		'register_closed'      => __('当前站点暂未开放注册。', 'origin'),
		'register_missing'     => __('请填写注册所需信息。', 'origin'),
		'register_bad_user'    => __('用户名只能包含字母、数字、空格、下划线、连字符、句点和 @。', 'origin'),
		'register_user_exists' => __('这个用户名已被使用。', 'origin'),
		'register_bad_email'   => __('请输入有效的邮箱地址。', 'origin'),
		'register_email_used'  => __('这个邮箱地址已被使用。', 'origin'),
		'register_bad_pass'    => __('密码至少需要 8 个字符。', 'origin'),
		'register_failed'      => __('注册暂时失败，请稍后再试。', 'origin'),
	);

	return $messages[$status] ?? '';
}

/**
 * 读取认证弹层默认面板。
 *
 * @return string 面板名称。
 */
function origin_get_auth_panel(): string {
	$panel = isset($_GET['origin_auth_panel']) ? sanitize_key(wp_unslash($_GET['origin_auth_panel'])) : 'login';

	return in_array($panel, array('login', 'register'), true) ? $panel : 'login';
}

/**
 * 处理主题级登录提交。
 */
function origin_handle_login(): void {
	check_admin_referer('origin_login', 'origin_login_nonce');

	$login    = isset($_POST['origin_login']) ? sanitize_text_field(wp_unslash($_POST['origin_login'])) : '';
	$password = isset($_POST['origin_password']) ? (string) wp_unslash($_POST['origin_password']) : '';

	if ('' === $login || '' === $password) {
		origin_redirect_with_auth_status('login_missing', 'login');
	}

	if (is_email($login)) {
		$user = get_user_by('email', $login);
		$login = $user ? $user->user_login : $login;
	}

	$user = wp_signon(
		array(
			'user_login'    => $login,
			'user_password' => $password,
			'remember'      => ! empty($_POST['origin_remember']),
		),
		is_ssl()
	);

	if (is_wp_error($user)) {
		origin_redirect_with_auth_status('login_failed', 'login');
	}

	wp_safe_redirect(origin_get_auth_redirect_url());
	exit;
}
add_action('admin_post_nopriv_origin_login', 'origin_handle_login');

/**
 * 处理主题级注册提交。
 */
function origin_handle_register(): void {
	check_admin_referer('origin_register', 'origin_register_nonce');

	if (! get_option('users_can_register')) {
		origin_redirect_with_auth_status('register_closed', 'register');
	}

	$username = isset($_POST['origin_username']) ? sanitize_user(wp_unslash($_POST['origin_username']), true) : '';
	$email    = isset($_POST['origin_email']) ? sanitize_email(wp_unslash($_POST['origin_email'])) : '';
	$password = isset($_POST['origin_password']) ? (string) wp_unslash($_POST['origin_password']) : '';

	if ('' === $username || '' === $email || '' === $password) {
		origin_redirect_with_auth_status('register_missing', 'register');
	}

	if (! validate_username($username)) {
		origin_redirect_with_auth_status('register_bad_user', 'register');
	}

	if (username_exists($username)) {
		origin_redirect_with_auth_status('register_user_exists', 'register');
	}

	if (! is_email($email)) {
		origin_redirect_with_auth_status('register_bad_email', 'register');
	}

	if (email_exists($email)) {
		origin_redirect_with_auth_status('register_email_used', 'register');
	}

	if (strlen($password) < 8) {
		origin_redirect_with_auth_status('register_bad_pass', 'register');
	}

	$user_id = wp_create_user($username, $password, $email);

	if (is_wp_error($user_id)) {
		origin_redirect_with_auth_status('register_failed', 'register');
	}

	wp_new_user_notification($user_id, null, 'admin');
	wp_signon(
		array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => true,
		),
		is_ssl()
	);

	wp_safe_redirect(origin_get_auth_redirect_url());
	exit;
}
add_action('admin_post_nopriv_origin_register', 'origin_handle_register');

/**
 * 处理主题级退出登录。
 */
function origin_handle_logout(): void {
	check_admin_referer('origin_logout', 'origin_logout_nonce');

	wp_logout();
	wp_safe_redirect(origin_get_auth_redirect_url());
	exit;
}
add_action('admin_post_origin_logout', 'origin_handle_logout');

/**
 * 输出头部账户操作。
 *
 * @param string $extra_class 额外 CSS 类名。
 */
function origin_the_header_auth_controls(string $extra_class = ''): void {
	$classes = trim('gh-head-members ' . $extra_class);
	?>
	<div class="<?php echo esc_attr($classes); ?>">
		<?php if (is_user_logged_in()) : ?>
			<span class="gh-head-user"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
			<form class="gh-head-logout" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
				<input type="hidden" name="action" value="origin_logout">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url(origin_get_current_url()); ?>">
				<?php wp_nonce_field('origin_logout', 'origin_logout_nonce'); ?>
				<button class="gh-head-btn gh-btn gh-primary-btn" type="submit"><?php esc_html_e('退出', 'origin'); ?></button>
			</form>
		<?php else : ?>
			<button class="gh-head-link" type="button" data-origin-auth-open="login"><?php esc_html_e('登录', 'origin'); ?></button>
			<?php if (get_option('users_can_register')) : ?>
				<button class="gh-head-btn gh-btn gh-primary-btn" type="button" data-origin-auth-open="register"><?php esc_html_e('注册', 'origin'); ?></button>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

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
		'close'         => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>',
		'share'         => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><path d="m8.6 10.5 6.8-4"></path><path d="m8.6 13.5 6.8 4"></path></svg>',
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
