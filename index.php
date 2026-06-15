<?php
/**
 * The main template file.
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
			<h1><?php esc_html_e('还没有文章', 'origin'); ?></h1>
			<p><?php esc_html_e('第一篇文章发布后，会在这里形成清晰的阅读列表。', 'origin'); ?></p>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
