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
<body <?php body_class('is-head-' . origin_get_navigation_layout()); ?>>
<?php wp_body_open(); ?>
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
			</nav>

			<div class="gh-head-actions">
				<button class="gh-search gh-icon-btn" type="button" aria-label="<?php esc_attr_e('搜索本站', 'origin'); ?>" aria-controls="origin-search-modal" aria-expanded="false" data-origin-search-open>
					<?php origin_icon('search'); ?>
				</button>
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

	<div class="site-content">
