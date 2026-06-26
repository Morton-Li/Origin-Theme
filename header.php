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

$origin_is_account_page = function_exists('origin_is_user_dashboard_request') && origin_is_user_dashboard_request();
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
	<?php if (! $origin_is_account_page) : ?>
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

		<div id="origin-auth-modal" class="auth-modal" hidden data-origin-auth-modal data-origin-auth-current-panel="<?php echo esc_attr(origin_get_auth_panel()); ?>" data-origin-auth-has-notice="<?php echo origin_get_auth_notice() ? 'true' : 'false'; ?>" data-origin-auth-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
			<div class="auth-modal-backdrop" data-origin-auth-close></div>
			<div class="auth-modal-panel" role="dialog" aria-modal="true" aria-labelledby="origin-auth-title">
				<button class="auth-modal-close gh-icon-btn" type="button" aria-label="<?php esc_attr_e('关闭账户面板', 'origin'); ?>" data-origin-auth-close>
					<?php origin_icon('close'); ?>
				</button>

				<h2 id="origin-auth-title" class="screen-reader-text"><?php esc_html_e('账户', 'origin'); ?></h2>
				<p class="auth-muted auth-loading" data-origin-auth-loading hidden><?php esc_html_e('正在加载账户面板...', 'origin'); ?></p>
				<p class="auth-muted auth-error" data-origin-auth-error hidden><?php esc_html_e('账户面板暂时无法加载，请稍后再试。', 'origin'); ?></p>
				<div class="auth-modal-content" data-origin-auth-content></div>
			</div>
		</div>
	<?php endif; ?>

	<div class="site-content">
