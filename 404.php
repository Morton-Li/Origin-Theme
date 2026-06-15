<?php
/**
 * The 404 template.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<section class="empty-state gh-canvas">
		<p class="single-meta"><?php esc_html_e('404', 'origin'); ?></p>
		<h1><?php esc_html_e('页面不存在', 'origin'); ?></h1>
		<p><?php esc_html_e('可以回到首页继续阅读，或使用搜索查找内容。', 'origin'); ?></p>
		<?php get_search_form(); ?>
	</section>
</main>

<?php
get_footer();
