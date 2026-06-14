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
	<?php wp_head(); ?>
</head>
<body <?php body_class('is-head-left-logo'); ?>>
<?php wp_body_open(); ?>
<div class="site">
	<header id="gh-head" class="gh-head gh-outer">
		<div class="gh-head-inner gh-inner">
			<div class="gh-head-brand">
				<div class="gh-head-brand-wrapper">
					<a class="gh-head-logo" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
						<?php
						if (has_custom_logo()) {
							the_custom_logo();
						} else {
							bloginfo('name');
						}
						?>
					</a>
				</div>
				<a class="gh-search gh-icon-btn" href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="<?php esc_attr_e('搜索本站', 'origin'); ?>">
					<?php origin_icon('search'); ?>
				</a>
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
			</nav>

			<div class="gh-head-actions">
				<a class="gh-search gh-icon-btn" href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="<?php esc_attr_e('搜索本站', 'origin'); ?>">
					<?php origin_icon('search'); ?>
				</a>
			</div>
		</div>
	</header>

	<div class="site-content">
