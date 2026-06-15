<?php
/**
 * The search results template.
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
	<header class="archive-header gh-canvas">
		<h1>
			<?php
			printf(
				/* translators: %s: Search query. */
				esc_html__('搜索：%s', 'origin'),
				esc_html(get_search_query())
			);
			?>
		</h1>
	</header>

	<?php if (have_posts()) : ?>
		<div class="post-feed gh-feed gh-canvas">
			<?php
			while (have_posts()) :
				the_post();
				get_template_part('template-parts/content', 'feed');
			endwhile;
			?>
		</div>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<section class="empty-state gh-canvas">
			<h2><?php esc_html_e('没有找到相关内容', 'origin'); ?></h2>
			<?php get_search_form(); ?>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
