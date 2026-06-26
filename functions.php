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

	if (origin_is_turnstile_enabled()) {
		wp_enqueue_script(
			'origin-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js',
			array(),
			null,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
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
				'left-logo'   => __('导航栏左对齐', 'origin'),
				'center-menu' => __('导航栏居中', 'origin'),
			),
			'label'   => __('导航布局', 'origin'),
			'section' => 'origin_layout',
			'type'    => 'select',
		)
	);

	$wp_customize->add_section(
		'origin_security',
		array(
			'title'    => __('安全', 'origin'),
			'priority' => 34,
		)
	);

	$wp_customize->add_setting(
		'origin_turnstile_site_key',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'origin_turnstile_site_key',
		array(
			'description' => __('配置后，登录、注册和评论表单会显示 Cloudflare Turnstile 质询。', 'origin'),
			'label'       => __('Turnstile 站点密钥', 'origin'),
			'section'     => 'origin_security',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'origin_turnstile_secret_key',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'origin_turnstile_secret_key',
		array(
			'description' => __('站点密钥和密钥都填写后才会启用验证。密钥只用于服务端校验，不会输出到前台。', 'origin'),
			'label'       => __('Turnstile 密钥', 'origin'),
			'section'     => 'origin_security',
			'type'        => 'password',
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
 * 读取登录请求中的跳转地址。
 *
 * @return string 安全的跳转地址，没有明确请求时返回空字符串。
 */
function origin_get_requested_login_redirect_url(): string {
	$redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';

	if ('' === $redirect_to) {
		return '';
	}

	$redirect_to = remove_query_arg(array('origin_auth', 'origin_auth_panel'), $redirect_to);

	return wp_validate_redirect($redirect_to, '');
}

/**
 * 读取异步认证面板的上下文地址。
 *
 * @return string 安全的上下文地址。
 */
function origin_get_auth_modal_context_url(): string {
	$context_url = isset($_GET['current_url']) ? esc_url_raw(wp_unslash($_GET['current_url'])) : origin_get_current_url();
	$context_url = remove_query_arg(array('origin_auth', 'origin_auth_panel'), $context_url);

	return wp_validate_redirect($context_url, home_url('/'));
}

/**
 * 读取主题用户后台固定地址别名。
 *
 * @return string 用户后台地址别名。
 */
function origin_get_user_dashboard_slug(): string {
	return 'account';
}

/**
 * 读取主题用户后台 URL。
 *
 * @return string 用户后台 URL。
 */
function origin_get_user_dashboard_url(): string {
	return home_url('/' . origin_get_user_dashboard_slug() . '/');
}

/**
 * 判断用户是否可以进入管理后台页面。
 *
 * 作者和编辑等具备发布文章能力的账户可以管理内容；订阅者和投稿者不进入后台。
 *
 * @param WP_User|int|null $user 用户对象、用户 ID，未提供时使用当前登录用户。
 * @return bool 可以进入时返回 true。
 */
function origin_can_user_access_wp_admin(WP_User|int|null $user = null): bool {
	if ($user instanceof WP_User) {
		$user_id = (int) $user->ID;
	} elseif (is_int($user)) {
		$user_id = $user;
	} else {
		$user_id = get_current_user_id();
	}

	if ($user_id <= 0) {
		return false;
	}

	return user_can($user_id, 'manage_options') || user_can($user_id, 'publish_posts');
}

/**
 * 判断当前用户是否可以进入管理后台页面。
 *
 * @return bool 可以进入时返回 true。
 */
function origin_can_current_user_access_wp_admin(): bool {
	return origin_can_user_access_wp_admin();
}

/**
 * 注册主题用户后台重写规则。
 */
function origin_add_user_dashboard_rewrite_rule(): void {
	add_rewrite_rule('^' . origin_get_user_dashboard_slug() . '/?$', 'index.php?origin_user_dashboard=1', 'top');
}
add_action('init', 'origin_add_user_dashboard_rewrite_rule');

/**
 * 增加主题用户后台查询变量。
 *
 * @param array<int, string> $vars 已注册的公开查询变量。
 * @return array<int, string> 调整后的查询变量。
 */
function origin_add_user_dashboard_query_var(array $vars): array {
	$vars[] = 'origin_user_dashboard';

	return $vars;
}
add_filter('query_vars', 'origin_add_user_dashboard_query_var');

/**
 * 判断当前请求路径是否为主题用户后台。
 *
 * @return bool 是用户后台请求时返回 true。
 */
function origin_is_user_dashboard_path(): bool {
	$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
	$request_uri = str_replace(array("\r", "\n"), '', $request_uri);
	$request_path = wp_parse_url($request_uri, PHP_URL_PATH);

	if (! is_string($request_path)) {
		return false;
	}

	$home_path = wp_parse_url(home_url('/'), PHP_URL_PATH);
	$home_path = is_string($home_path) ? trim($home_path, '/') : '';
	$request_path = trim($request_path, '/');

	if ('' !== $home_path && ($request_path === $home_path || str_starts_with($request_path, $home_path . '/'))) {
		$request_path = trim(substr($request_path, strlen($home_path)), '/');
	}

	return origin_get_user_dashboard_slug() === $request_path;
}

/**
 * 在重写规则未刷新时仍识别用户后台请求。
 *
 * @param WP $wp 当前请求对象。
 */
function origin_detect_user_dashboard_request(WP $wp): void {
	if (origin_is_user_dashboard_path()) {
		$wp->query_vars['origin_user_dashboard'] = '1';
	}
}
add_action('parse_request', 'origin_detect_user_dashboard_request');

/**
 * 判断当前请求是否为主题用户后台。
 *
 * @return bool 是用户后台请求时返回 true。
 */
function origin_is_user_dashboard_request(): bool {
	return '1' === (string) get_query_var('origin_user_dashboard') || origin_is_user_dashboard_path();
}

/**
 * 准备主题用户后台请求并保护访问权限。
 */
function origin_prepare_user_dashboard_request(): void {
	if (! origin_is_user_dashboard_request()) {
		return;
	}

	global $wp_query;

	if ($wp_query instanceof WP_Query) {
		$wp_query->is_404  = false;
		$wp_query->is_home = false;
		$wp_query->is_page = true;
	}

	status_header(200);
	nocache_headers();

	if (! is_user_logged_in()) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'origin_auth'       => 'login_required',
					'origin_auth_panel' => 'login',
				),
				home_url('/')
			)
		);
		exit;
	}
}
add_action('template_redirect', 'origin_prepare_user_dashboard_request', 0);

/**
 * 加载主题用户后台模板。
 *
 * @param string $template 当前模板路径。
 * @return string 用户后台模板路径。
 */
function origin_load_user_dashboard_template(string $template): string {
	if (! origin_is_user_dashboard_request()) {
		return $template;
	}

	$account_template = locate_template('user-dashboard.php');

	return $account_template ?: $template;
}
add_filter('template_include', 'origin_load_user_dashboard_template');

/**
 * 切换主题后刷新用户后台重写规则。
 */
function origin_flush_user_dashboard_rewrite_rules(): void {
	origin_add_user_dashboard_rewrite_rule();
	flush_rewrite_rules(false);
	update_option('origin_rewrite_version', ORIGIN_VERSION);
}
add_action('after_switch_theme', 'origin_flush_user_dashboard_rewrite_rules');

/**
 * 版本变化后为管理员刷新重写规则，避免新路由短暂 404。
 */
function origin_maybe_flush_user_dashboard_rewrite_rules(): void {
	if (! current_user_can('manage_options')) {
		return;
	}

	if (ORIGIN_VERSION === get_option('origin_rewrite_version')) {
		return;
	}

	origin_flush_user_dashboard_rewrite_rules();
}
add_action('admin_init', 'origin_maybe_flush_user_dashboard_rewrite_rules', 0);

/**
 * 判断当前后台请求是否是前台表单或异步入口。
 *
 * @return bool 是非后台页面入口时返回 true。
 */
function origin_is_wp_admin_endpoint_request(): bool {
	global $pagenow;

	return wp_doing_ajax() || in_array($pagenow, array('admin-ajax.php', 'admin-post.php'), true);
}

/**
 * 判断目标地址是否指向管理后台。
 *
 * @param string $url 待检查的地址。
 * @return bool 指向管理后台时返回 true。
 */
function origin_is_wp_admin_url(string $url): bool {
	$target_path = wp_parse_url($url, PHP_URL_PATH);
	$admin_path  = wp_parse_url(admin_url(), PHP_URL_PATH);

	if (! is_string($target_path) || ! is_string($admin_path)) {
		return false;
	}

	$target_path = trailingslashit('/' . ltrim($target_path, '/'));
	$admin_path  = trailingslashit('/' . trim($admin_path, '/'));

	return str_starts_with($target_path, $admin_path);
}

/**
 * 低权限账户不能进入管理后台。
 */
function origin_redirect_regular_users_from_wp_admin(): void {
	if (! is_user_logged_in() || origin_can_current_user_access_wp_admin() || origin_is_wp_admin_endpoint_request()) {
		return;
	}

	wp_safe_redirect(home_url('/'));
	exit;
}
add_action('admin_init', 'origin_redirect_regular_users_from_wp_admin', 1);

/**
 * 在限制管理后台时隐藏低权限账户的前台工具栏。
 *
 * @param bool $show_admin_bar 是否显示工具栏。
 * @return bool 调整后的工具栏显示状态。
 */
function origin_filter_admin_bar_visibility(bool $show_admin_bar): bool {
	if (is_user_logged_in() && ! origin_can_current_user_access_wp_admin()) {
		return false;
	}

	return $show_admin_bar;
}
add_filter('show_admin_bar', 'origin_filter_admin_bar_visibility');

/**
 * 在核心登录流程中按请求地址和权限决定跳转位置。
 *
 * @param string           $redirect_to           当前跳转地址。
 * @param string           $requested_redirect_to 用户请求的跳转地址。
 * @param WP_User|WP_Error $user                  登录用户或错误对象。
 * @return string 调整后的跳转地址。
 */
function origin_filter_login_redirect(string $redirect_to, string $requested_redirect_to, WP_User|WP_Error $user): string {
	if (! $user instanceof WP_User || ! $user->exists()) {
		return $redirect_to;
	}

	$can_access_admin = origin_can_user_access_wp_admin($user);
	$target_url       = '' !== $requested_redirect_to ? $requested_redirect_to : $redirect_to;

	if ('' === $requested_redirect_to && ! $can_access_admin) {
		$target_url = home_url('/');
	}

	$target_url = remove_query_arg(array('origin_auth', 'origin_auth_panel'), $target_url);
	$target_url = wp_validate_redirect($target_url, home_url('/'));

	if (! $can_access_admin && origin_is_wp_admin_url($target_url)) {
		return home_url('/');
	}

	return $target_url;
}
add_filter('login_redirect', 'origin_filter_login_redirect', 10, 3);

/**
 * 在限制管理后台时改写用户后台 URL。
 *
 * @param string $url     原始后台 URL。
 * @param int    $user_id 用户 ID。
 * @return string 调整后的后台 URL。
 */
function origin_filter_user_dashboard_url(string $url, int $user_id): string {
	if ($user_id > 0 && ! origin_can_user_access_wp_admin($user_id)) {
		return origin_get_user_dashboard_url();
	}

	return $url;
}
add_filter('user_dashboard_url', 'origin_filter_user_dashboard_url', 10, 2);

/**
 * 在限制管理后台时改写用户资料 URL。
 *
 * @param string $url     原始资料 URL。
 * @param int    $user_id 用户 ID。
 * @return string 调整后的资料 URL。
 */
function origin_filter_edit_profile_url(string $url, int $user_id): string {
	if ($user_id > 0 && ! origin_can_user_access_wp_admin($user_id)) {
		return origin_get_user_dashboard_url();
	}

	return $url;
}
add_filter('edit_profile_url', 'origin_filter_edit_profile_url', 10, 2);

/**
 * 为主题用户后台追加页面类名。
 *
 * @param array<int, string> $classes 当前页面类名。
 * @return array<int, string> 调整后的页面类名。
 */
function origin_user_dashboard_body_class(array $classes): array {
	if (origin_is_user_dashboard_request()) {
		$classes[] = 'origin-account-page';
	}

	return $classes;
}
add_filter('body_class', 'origin_user_dashboard_body_class');

/**
 * 读取认证跳转地址。
 *
 * @param WP_User|null $user 已登录用户。提供后会按后台访问策略过滤管理后台地址。
 * @return string 安全的跳转地址。
 */
function origin_get_auth_redirect_url(?WP_User $user = null): string {
	$redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : (wp_get_referer() ?: home_url('/'));
	$redirect_to = remove_query_arg(array('origin_auth', 'origin_auth_panel'), $redirect_to);
	$redirect_to = wp_validate_redirect($redirect_to, home_url('/'));

	if (origin_is_wp_admin_url($redirect_to) && (! $user instanceof WP_User || ! $user->exists() || ! origin_can_user_access_wp_admin($user))) {
		return home_url('/');
	}

	return $redirect_to;
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
		'login_required'       => __('请先登录后再进入个人中心。', 'origin'),
		'login_missing'        => __('请输入账号和密码。', 'origin'),
		'login_failed'         => __('账号或密码不正确。', 'origin'),
		'turnstile_failed'     => __('请先完成安全验证。', 'origin'),
		'register_closed'      => __('当前站点暂未开放注册。', 'origin'),
		'register_missing'     => __('请填写注册所需信息。', 'origin'),
		'register_bad_user'    => __('用户名只能包含字母、数字、空格、下划线、连字符、句点和 @。', 'origin'),
		'register_user_exists' => __('这个用户名已被使用。', 'origin'),
		'register_bad_email'   => __('请输入有效的邮箱地址。', 'origin'),
		'register_email_used'  => __('这个邮箱地址已被使用。', 'origin'),
		'register_mail_sent'   => __('注册邮件已发送，请前往邮箱设置密码后登录。', 'origin'),
		'register_failed'      => __('注册暂时失败，请稍后再试。', 'origin'),
	);

	return $messages[$status] ?? '';
}

/**
 * 读取用户后台提示信息。
 *
 * @return array{message:string,type:string} 提示内容和类型。
 */
function origin_get_account_notice(): array {
	$status = isset($_GET['origin_account']) ? sanitize_key(wp_unslash($_GET['origin_account'])) : '';

	$messages = array(
		'profile_saved'       => array(__('账户资料已更新。', 'origin'), 'success'),
		'profile_missing'     => array(__('请填写显示名称和邮箱。', 'origin'), 'error'),
		'profile_bad_email'   => array(__('请输入有效的邮箱地址。', 'origin'), 'error'),
		'profile_email_used'  => array(__('这个邮箱地址已被其他账户使用。', 'origin'), 'error'),
		'profile_save_failed' => array(__('账户资料暂时无法保存，请稍后再试。', 'origin'), 'error'),
	);

	if (! isset($messages[$status])) {
		return array(
			'message' => '',
			'type'    => '',
		);
	}

	return array(
		'message' => $messages[$status][0],
		'type'    => $messages[$status][1],
	);
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
 * 读取认证弹层请求面板。
 *
 * @return string 面板名称。
 */
function origin_get_requested_auth_panel(): string {
	$panel = isset($_GET['panel']) ? sanitize_key(wp_unslash($_GET['panel'])) : origin_get_auth_panel();

	return in_array($panel, array('login', 'register'), true) ? $panel : 'login';
}

/**
 * 输出认证弹层的动态内容。
 *
 * @param string $context_url 表单完成后的回跳上下文地址。
 */
function origin_the_auth_modal_content(string $context_url): void {
	$auth_notice = origin_get_auth_notice();
	?>
	<div class="auth-tabs" role="tablist" aria-label="<?php esc_attr_e('账户操作', 'origin'); ?>">
		<button id="origin-auth-login-tab" class="auth-tab" type="button" role="tab" aria-controls="origin-auth-login" data-origin-auth-tab="login"><?php esc_html_e('登录', 'origin'); ?></button>
		<button id="origin-auth-register-tab" class="auth-tab" type="button" role="tab" aria-controls="origin-auth-register" data-origin-auth-tab="register"><?php esc_html_e('注册', 'origin'); ?></button>
	</div>

	<?php if ($auth_notice) : ?>
		<div class="auth-notice" role="alert"><?php echo esc_html($auth_notice); ?></div>
	<?php endif; ?>

	<div class="auth-panel-stage" data-origin-auth-stage>
		<div id="origin-auth-login" class="auth-panel" role="tabpanel" aria-labelledby="origin-auth-login-tab" data-origin-auth-panel="login">
			<form class="auth-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
				<input type="hidden" name="action" value="origin_login">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url(origin_get_requested_login_redirect_url()); ?>">
				<?php wp_nonce_field('origin_login', 'origin_login_nonce'); ?>
				<label for="origin-login-field"><?php esc_html_e('账号或邮箱', 'origin'); ?></label>
				<input id="origin-login-field" name="origin_login" type="text" autocomplete="username" required>
				<label for="origin-login-password"><?php esc_html_e('密码', 'origin'); ?></label>
				<input id="origin-login-password" name="origin_password" type="password" autocomplete="current-password" required>
				<label class="auth-checkbox" for="origin-login-remember">
					<input id="origin-login-remember" name="origin_remember" type="checkbox" value="1">
					<span><?php esc_html_e('保持登录', 'origin'); ?></span>
				</label>
				<?php origin_the_turnstile_widget('login'); ?>
				<button class="gh-btn gh-primary-btn auth-submit" type="submit"><?php esc_html_e('登录', 'origin'); ?></button>
			</form>
		</div>

		<div id="origin-auth-register" class="auth-panel" role="tabpanel" aria-labelledby="origin-auth-register-tab" data-origin-auth-panel="register">
			<?php if (get_option('users_can_register')) : ?>
				<form class="auth-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
					<input type="hidden" name="action" value="origin_register">
					<input type="hidden" name="redirect_to" value="<?php echo esc_url($context_url); ?>">
					<?php wp_nonce_field('origin_register', 'origin_register_nonce'); ?>
					<label for="origin-register-username"><?php esc_html_e('用户名', 'origin'); ?></label>
					<input id="origin-register-username" name="origin_username" type="text" autocomplete="username" required>
					<p class="auth-help"><?php esc_html_e('仅支持字母、数字、空格、下划线、连字符、句点和 @。', 'origin'); ?></p>
					<label for="origin-register-email"><?php esc_html_e('邮箱', 'origin'); ?></label>
					<input id="origin-register-email" name="origin_email" type="email" autocomplete="email" required>
					<p class="auth-help"><?php esc_html_e('注册后将向该邮箱发送设置密码链接。', 'origin'); ?></p>
					<?php origin_the_turnstile_widget('register'); ?>
					<button class="gh-btn gh-primary-btn auth-submit" type="submit"><?php esc_html_e('注册', 'origin'); ?></button>
				</form>
			<?php else : ?>
				<p class="auth-muted"><?php esc_html_e('当前站点暂未开放注册。', 'origin'); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * 异步加载认证弹层内容。
 */
function origin_ajax_load_auth_modal(): void {
	$panel       = origin_get_requested_auth_panel();
	$notice      = origin_get_auth_notice();
	$context_url = origin_get_auth_modal_context_url();

	ob_start();
	origin_the_auth_modal_content($context_url);
	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'      => is_string($html) ? $html : '',
			'panel'     => $panel,
			'hasNotice' => '' !== $notice,
		)
	);
}
add_action('wp_ajax_origin_load_auth_modal', 'origin_ajax_load_auth_modal');
add_action('wp_ajax_nopriv_origin_load_auth_modal', 'origin_ajax_load_auth_modal');

/**
 * 读取 Turnstile 站点密钥。
 *
 * @return string 站点密钥。
 */
function origin_get_turnstile_site_key(): string {
	return trim((string) get_theme_mod('origin_turnstile_site_key', ''));
}

/**
 * 读取 Turnstile 服务端密钥。
 *
 * @return string 服务端密钥。
 */
function origin_get_turnstile_secret_key(): string {
	return trim((string) get_theme_mod('origin_turnstile_secret_key', ''));
}

/**
 * 判断 Turnstile 是否已完整配置。
 *
 * @return bool 是否启用 Turnstile。
 */
function origin_is_turnstile_enabled(): bool {
	return '' !== origin_get_turnstile_site_key() && '' !== origin_get_turnstile_secret_key();
}

/**
 * 读取当前请求 IP，用于 Turnstile 可选校验参数。
 *
 * @return string 请求 IP；无法安全识别时返回空字符串。
 */
function origin_get_request_ip(): string {
	$server_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');

	foreach ($server_keys as $server_key) {
		if (empty($_SERVER[$server_key])) {
			continue;
		}

		$value = sanitize_text_field(wp_unslash((string) $_SERVER[$server_key]));

		if ('HTTP_X_FORWARDED_FOR' === $server_key) {
			$forwarded_ips = explode(',', $value);
			$value         = trim($forwarded_ips[0]);
		}

		if (filter_var($value, FILTER_VALIDATE_IP)) {
			return $value;
		}
	}

	return '';
}

/**
 * 校验 Turnstile 质询结果。
 *
 * @return bool 未启用时始终通过；启用后返回 Cloudflare 校验结果。
 */
function origin_verify_turnstile(): bool {
	if (! origin_is_turnstile_enabled()) {
		return true;
	}

	$token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';

	if ('' === $token) {
		return false;
	}

	$body = array(
		'secret'   => origin_get_turnstile_secret_key(),
		'response' => $token,
	);

	$remote_ip = origin_get_request_ip();

	if ('' !== $remote_ip) {
		$body['remoteip'] = $remote_ip;
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'body'    => $body,
			'timeout' => 8,
		)
	);

	if (is_wp_error($response)) {
		return false;
	}

	$result = json_decode(wp_remote_retrieve_body($response), true);

	return is_array($result) && ! empty($result['success']);
}

/**
 * 输出 Turnstile 前台组件。
 *
 * @param string $action 当前组件用途。
 */
function origin_the_turnstile_widget(string $action = ''): void {
	if (! origin_is_turnstile_enabled()) {
		return;
	}

	$action = sanitize_key($action);
	?>
	<div class="origin-turnstile cf-turnstile" data-sitekey="<?php echo esc_attr(origin_get_turnstile_site_key()); ?>" data-theme="auto" data-size="flexible"<?php echo '' !== $action ? ' data-action="' . esc_attr($action) . '"' : ''; ?>></div>
	<?php
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

	if (! origin_verify_turnstile()) {
		origin_redirect_with_auth_status('turnstile_failed', 'login');
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

	wp_safe_redirect(origin_get_auth_redirect_url($user));
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

	if (! origin_verify_turnstile()) {
		origin_redirect_with_auth_status('turnstile_failed', 'register');
	}

	$raw_username = isset($_POST['origin_username']) ? trim((string) wp_unslash($_POST['origin_username'])) : '';
	$raw_email    = isset($_POST['origin_email']) ? trim((string) wp_unslash($_POST['origin_email'])) : '';

	if ('' === $raw_username || '' === $raw_email) {
		origin_redirect_with_auth_status('register_missing', 'register');
	}

	if (! validate_username($raw_username)) {
		origin_redirect_with_auth_status('register_bad_user', 'register');
	}

	$username = sanitize_user($raw_username, true);
	$email    = sanitize_email($raw_email);

	if (username_exists($username)) {
		origin_redirect_with_auth_status('register_user_exists', 'register');
	}

	if (! is_email($email)) {
		origin_redirect_with_auth_status('register_bad_email', 'register');
	}

	if (email_exists($email)) {
		origin_redirect_with_auth_status('register_email_used', 'register');
	}

	$user_id = register_new_user($username, $email);

	if (is_wp_error($user_id)) {
		origin_redirect_with_auth_status('register_failed', 'register');
	}

	origin_redirect_with_auth_status('register_mail_sent', 'login');
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
 * 跳转回主题用户后台并携带状态。
 *
 * @param string $status 用户后台状态。
 */
function origin_redirect_with_account_status(string $status): void {
	wp_safe_redirect(
		add_query_arg(
			'origin_account',
			sanitize_key($status),
			origin_get_user_dashboard_url()
		)
	);
	exit;
}

/**
 * 处理主题用户后台资料保存。
 */
function origin_handle_profile_update(): void {
	if (! is_user_logged_in()) {
		origin_redirect_with_auth_status('login_required', 'login');
	}

	check_admin_referer('origin_update_profile', 'origin_update_profile_nonce');

	$user_id      = get_current_user_id();
	$display_name = isset($_POST['origin_display_name']) ? sanitize_text_field(wp_unslash($_POST['origin_display_name'])) : '';
	$email        = isset($_POST['origin_email']) ? sanitize_email(wp_unslash($_POST['origin_email'])) : '';

	if ('' === $display_name || '' === $email) {
		origin_redirect_with_account_status('profile_missing');
	}

	if (! is_email($email)) {
		origin_redirect_with_account_status('profile_bad_email');
	}

	$email_owner = email_exists($email);

	if ($email_owner && (int) $email_owner !== $user_id) {
		origin_redirect_with_account_status('profile_email_used');
	}

	$result = wp_update_user(
		array(
			'ID'           => $user_id,
			'display_name' => $display_name,
			'nickname'     => $display_name,
			'user_email'   => $email,
		)
	);

	if (is_wp_error($result)) {
		origin_redirect_with_account_status('profile_save_failed');
	}

	origin_redirect_with_account_status('profile_saved');
}
add_action('admin_post_origin_update_profile', 'origin_handle_profile_update');

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
			<a class="gh-head-user" href="<?php echo esc_url(origin_get_user_dashboard_url()); ?>"><?php echo esc_html(wp_get_current_user()->display_name); ?></a>
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
 * 调整评论表单默认字段。
 *
 * @param array<string, string> $fields 评论表单字段。
 * @return array<string, string> 调整后的评论表单字段。
 */
function origin_comment_form_default_fields(array $fields): array {
	$commenter = wp_get_current_commenter();
	$required  = (bool) get_option('require_name_email');

	$required_indicator = $required ? ' <span class="required">*</span>' : '';
	$required_attribute = $required ? ' required' : '';

	$fields['author'] = sprintf(
		'<p class="comment-form-author"><label for="author">%1$s%2$s</label><input id="author" name="author" type="text" value="%3$s" maxlength="245" autocomplete="name"%4$s></p>',
		esc_html__('显示名称', 'origin'),
		$required_indicator,
		esc_attr($commenter['comment_author']),
		$required_attribute
	);

	$fields['email'] = sprintf(
		'<p class="comment-form-email"><label for="email">%1$s%2$s</label><input id="email" name="email" type="email" value="%3$s" maxlength="100" autocomplete="email"%4$s></p>',
		esc_html__('邮箱', 'origin'),
		$required_indicator,
		esc_attr($commenter['comment_author_email']),
		$required_attribute
	);

	unset($fields['url']);

	if (isset($fields['cookies'])) {
		$checked = empty($commenter['comment_author_email']) ? '' : ' checked';

		$fields['cookies'] = sprintf(
			'<p class="comment-form-cookies-consent"><label for="wp-comment-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"%1$s><span>%2$s</span></label></p>',
			$checked,
			esc_html__('保存我的信息以便下次评论时使用。', 'origin')
		);
	}

	return $fields;
}
add_filter('comment_form_default_fields', 'origin_comment_form_default_fields');

/**
 * 调整评论表单整体文案与评论输入框。
 *
 * @param array<string, mixed> $defaults 评论表单默认参数。
 * @return array<string, mixed> 调整后的默认参数。
 */
function origin_comment_form_defaults(array $defaults): array {
	$defaults['comment_notes_before'] = '';
	$defaults['comment_notes_after']  = '';
	$defaults['comment_field']        = sprintf(
		'<p class="comment-form-comment"><label for="comment">%1$s <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="5" maxlength="65525" required></textarea></p>',
		esc_html__('评论', 'origin')
	);

	return $defaults;
}
add_filter('comment_form_defaults', 'origin_comment_form_defaults');

/**
 * 在评论提交按钮前输出 Turnstile 组件。
 *
 * @param string               $submit_field 提交区域 HTML。
 * @param array<string, mixed> $args         评论表单参数。
 * @return string 调整后的提交区域 HTML。
 */
function origin_comment_form_submit_field(string $submit_field, array $args): string {
	if (! origin_is_turnstile_enabled()) {
		return $submit_field;
	}

	ob_start();
	origin_the_turnstile_widget('comment');
	$turnstile = ob_get_clean();

	return $turnstile . $submit_field;
}
add_filter('comment_form_submit_field', 'origin_comment_form_submit_field', 10, 2);

/**
 * 评论提交前校验 Turnstile，并移除网站字段。
 *
 * @param array<string, mixed> $commentdata 评论数据。
 * @return array<string, mixed> 调整后的评论数据。
 */
function origin_preprocess_comment(array $commentdata): array {
	$commentdata['comment_author_url'] = '';

	$comment_type = isset($commentdata['comment_type']) ? (string) $commentdata['comment_type'] : '';

	if (is_admin() || ('' !== $comment_type && 'comment' !== $comment_type)) {
		return $commentdata;
	}

	if (! origin_verify_turnstile()) {
		wp_die(
			esc_html__('请先完成安全验证后再发表评论。', 'origin'),
			esc_html__('安全验证失败', 'origin'),
			array(
				'back_link' => true,
				'response'  => 403,
			)
		);
	}

	return $commentdata;
}
add_filter('preprocess_comment', 'origin_preprocess_comment');

/**
 * 前台评论作者始终显示为纯文本，避免输出作者网站链接。
 *
 * @param string $author_link 作者链接 HTML。
 * @param string $author      作者显示名称。
 * @return string 安全的作者纯文本。
 */
function origin_comment_author_link(string $author_link, string $author): string {
	unset($author_link);

	return esc_html($author);
}
add_filter('get_comment_author_link', 'origin_comment_author_link', 10, 2);

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
		'arrow-up'      => '<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="m18 15-6-6-6 6"></path></svg>',
	);

	if (! isset($icons[$name])) {
		return;
	}

	echo $icons[$name]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
