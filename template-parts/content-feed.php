<?php
/**
 * Template part for feed items.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$is_featured = origin_is_featured_post();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class($is_featured ? 'feed origin-feed--featured' : 'feed public'); ?>>
	<div class="feed-calendar" aria-hidden="true">
		<div class="feed-calendar-day"><?php echo esc_html(get_the_date('d')); ?></div>
		<div class="feed-calendar-month"><?php echo esc_html(get_the_date('M')); ?></div>
	</div>

	<h2 class="feed-title"><?php the_title(); ?></h2>

	<div class="feed-right">
		<?php if ($is_featured) : ?>
			<span class="icon-star" aria-label="<?php esc_attr_e('精选文章', 'origin'); ?>">
				<?php origin_icon('star'); ?>
			</span>
		<?php endif; ?>
		<div class="feed-length"><?php echo esc_html(origin_get_reading_time()); ?></div>
	</div>

	<span class="feed-icon"><?php origin_icon('chevron-right'); ?></span>

	<a class="u-permalink" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>"></a>
</article>
