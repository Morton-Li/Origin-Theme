<?php
/**
 * The header for Origin.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.classList.add('origin-js', 'origin-page-loading');</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class('is-head-' . origin_get_navigation_layout()); ?>>
<?php wp_body_open(); ?>
<div class="origin-page-loader" aria-hidden="true" data-origin-page-loader>
	<div class="origin-page-loader-mark"></div>
</div>
<div class="site">
	<header id="gh-head" class="gh-head gh-outer">
		<div class="gh-head-inner gh-inner">
			<div class="gh-head-brand">
				<div class="gh-head-brand-wrapper">
					<?php if (has_custom_logo()) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<a class="gh-head-logo" href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
					<?php endif; ?>
				</div>
				<button class="gh-search gh-icon-btn" type="button" aria-label="<?php esc_attr_e('搜索本站', 'origin'); ?>" aria-controls="origin-search-modal" aria-expanded="false" data-origin-search-open>
					<?php origin_icon('search'); ?>
				</button>
				<button class="gh-burger" type="button" aria-label="<?php esc_attr_e('切换导航菜单', 'origin'); ?>" aria-controls="primary-menu" aria-expanded="false">
					<span></span>
				</button>
			</div>

			<nav id="primary-menu" class="gh-head-menu" aria-label="<?php esc_attr_e('主导航', 'origin'); ?>">
				<?php
				wp_nav_menu(
					array(
						'container'      => false,
						'theme_location' => 'primary',
						'menu_class'     => 'menu',
						'fallback_cb'    => 'wp_page_menu',
						'depth'          => 1,
					)
				);
				?>
				<?php origin_the_header_auth_controls('gh-head-members-mobile'); ?>
			</nav>

			<div class="gh-head-actions">
				<button class="gh-search gh-icon-btn" type="button" aria-label="<?php esc_attr_e('搜索本站', 'origin'); ?>" aria-controls="origin-search-modal" aria-expanded="false" data-origin-search-open>
					<?php origin_icon('search'); ?>
				</button>
				<?php origin_the_header_auth_controls(); ?>
			</div>
		</div>
	</header>

	<div id="origin-search-modal" class="search-modal" hidden data-origin-search-modal>
		<div class="search-modal-backdrop" data-origin-search-close></div>
		<div class="search-modal-panel" role="dialog" aria-modal="true" aria-labelledby="origin-search-title">
			<button class="search-modal-close gh-icon-btn" type="button" aria-label="<?php esc_attr_e('关闭搜索', 'origin'); ?>" data-origin-search-close>
				<?php origin_icon('close'); ?>
			</button>
			<h2 id="origin-search-title" class="search-modal-title"><?php esc_html_e('搜索文章', 'origin'); ?></h2>
			<?php get_search_form(); ?>
		</div>
	</div>

	<div id="origin-auth-modal" class="auth-modal" hidden data-origin-auth-modal data-origin-auth-current-panel="<?php echo esc_attr(origin_get_auth_panel()); ?>" data-origin-auth-has-notice="<?php echo origin_get_auth_notice() ? 'true' : 'false'; ?>">
		<div class="auth-modal-backdrop" data-origin-auth-close></div>
		<div class="auth-modal-panel" role="dialog" aria-modal="true" aria-labelledby="origin-auth-title">
			<button class="auth-modal-close gh-icon-btn" type="button" aria-label="<?php esc_attr_e('关闭账户面板', 'origin'); ?>" data-origin-auth-close>
				<?php origin_icon('close'); ?>
			</button>

			<div class="auth-tabs" role="tablist" aria-label="<?php esc_attr_e('账户操作', 'origin'); ?>">
				<button id="origin-auth-login-tab" class="auth-tab" type="button" role="tab" aria-controls="origin-auth-login" data-origin-auth-tab="login"><?php esc_html_e('登录', 'origin'); ?></button>
				<button id="origin-auth-register-tab" class="auth-tab" type="button" role="tab" aria-controls="origin-auth-register" data-origin-auth-tab="register"><?php esc_html_e('注册', 'origin'); ?></button>
			</div>

			<h2 id="origin-auth-title" class="screen-reader-text"><?php esc_html_e('账户', 'origin'); ?></h2>

			<?php if (origin_get_auth_notice()) : ?>
				<div class="auth-notice" role="alert"><?php echo esc_html(origin_get_auth_notice()); ?></div>
			<?php endif; ?>

			<div id="origin-auth-login" class="auth-panel" role="tabpanel" aria-labelledby="origin-auth-login-tab" data-origin-auth-panel="login">
				<form class="auth-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
					<input type="hidden" name="action" value="origin_login">
					<input type="hidden" name="redirect_to" value="<?php echo esc_url(origin_get_current_url()); ?>">
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
						<input type="hidden" name="redirect_to" value="<?php echo esc_url(origin_get_current_url()); ?>">
						<?php wp_nonce_field('origin_register', 'origin_register_nonce'); ?>
						<label for="origin-register-username"><?php esc_html_e('用户名', 'origin'); ?></label>
						<input id="origin-register-username" name="origin_username" type="text" autocomplete="username" required>
						<label for="origin-register-email"><?php esc_html_e('邮箱', 'origin'); ?></label>
						<input id="origin-register-email" name="origin_email" type="email" autocomplete="email" required>
						<label for="origin-register-password"><?php esc_html_e('密码', 'origin'); ?></label>
						<input id="origin-register-password" name="origin_password" type="password" autocomplete="new-password" minlength="8" required>
						<?php origin_the_turnstile_widget('register'); ?>
						<button class="gh-btn gh-primary-btn auth-submit" type="submit"><?php esc_html_e('注册', 'origin'); ?></button>
					</form>
				<?php else : ?>
					<p class="auth-muted"><?php esc_html_e('当前站点暂未开放注册。', 'origin'); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="site-content">
