<?php
/**
 * Template part for homepage featured posts.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$featured_posts = new WP_Query(
	array(
		'ignore_sticky_posts' => false,
		'no_found_rows'       => true,
		'posts_per_page'      => 6,
		'post_status'         => 'publish',
		'tag'                 => 'featured',
	)
);

if (! $featured_posts->have_posts()) {
	$sticky_posts = get_option('sticky_posts');

	if ($sticky_posts) {
		$featured_posts = new WP_Query(
			array(
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'post__in'            => array_slice(array_map('intval', $sticky_posts), 0, 6),
				'orderby'             => 'post__in',
				'post_status'         => 'publish',
				'posts_per_page'      => 6,
			)
		);
	}
}

if (! $featured_posts->have_posts()) {
	wp_reset_postdata();
	return;
}
?>

<section class="featured-wrapper gh-outer" aria-labelledby="featured-title">
	<div class="featured-inner gh-inner">
		<h2 id="featured-title" class="featured-title"><?php esc_html_e('精选文章', 'origin'); ?></h2>

		<div class="featured-carousel">
			<button class="featured-nav featured-nav-prev" type="button" aria-label="<?php esc_attr_e('上一组精选文章', 'origin'); ?>" data-origin-featured-prev>
				<?php origin_icon('arrow-left'); ?>
			</button>
			<button class="featured-nav featured-nav-next" type="button" aria-label="<?php esc_attr_e('下一组精选文章', 'origin'); ?>" data-origin-featured-next>
				<?php origin_icon('arrow-right'); ?>
			</button>

			<div class="featured-feed" data-origin-featured-carousel>
			<?php
			while ($featured_posts->have_posts()) :
				$featured_posts->the_post();
				?>
				<article id="featured-post-<?php the_ID(); ?>" <?php post_class('featured-card'); ?>>
					<a class="featured-card-link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
						<div class="featured-card-media u-placeholder horizontal">
							<?php if (has_post_thumbnail()) : ?>
								<?php the_post_thumbnail('medium_large', array('class' => 'u-object-fit')); ?>
							<?php else : ?>
								<span class="featured-card-placeholder u-object-fit" aria-hidden="true"></span>
							<?php endif; ?>
						</div>
						<h3 class="featured-card-title"><?php the_title(); ?></h3>
					</a>
				</article>
				<?php
			endwhile;
			?>
			</div>
		</div>
	</div>
</section>

<?php
wp_reset_postdata();
