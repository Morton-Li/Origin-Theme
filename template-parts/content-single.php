<?php
/**
 * Template part for single entries.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$primary_category = origin_get_primary_category_name();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('single-entry gh-canvas'); ?>>
	<header class="single-header">
		<?php if (! is_page()) : ?>
			<div class="single-meta">
				<?php if ($primary_category) : ?>
					<span class="single-meta-item single-meta-tag"><?php echo esc_html($primary_category); ?></span>
				<?php endif; ?>
				<time class="single-meta-item" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
				<span class="single-meta-item"><?php echo esc_html(origin_get_reading_time()); ?></span>
			</div>
		<?php endif; ?>

		<?php the_title('<h1 class="single-title">', '</h1>'); ?>

		<?php if (has_excerpt()) : ?>
			<div class="single-excerpt"><?php the_excerpt(); ?></div>
		<?php endif; ?>

		<?php if (has_post_thumbnail()) : ?>
			<figure class="single-media">
				<?php the_post_thumbnail('large'); ?>
			</figure>
		<?php endif; ?>
	</header>

	<div class="entry-content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<nav class="page-links" aria-label="' . esc_attr__('分页', 'origin') . '">',
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<?php if (has_tag()) : ?>
		<footer class="single-footer">
			<div class="single-tags"><?php the_tags('', ' '); ?></div>
		</footer>
	<?php endif; ?>
</article>
