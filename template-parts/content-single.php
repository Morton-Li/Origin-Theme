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
$primary_category_link = '';
$thumbnail_id = (int) get_post_thumbnail_id();
$thumbnail_caption = $thumbnail_id ? wp_get_attachment_caption($thumbnail_id) : '';

if ($primary_category && ! is_page()) {
	$categories = get_the_category();
	$category_link = $categories ? get_category_link($categories[0]) : '';
	$primary_category_link = is_wp_error($category_link) ? '' : $category_link;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('single-entry'); ?>>
	<header class="single-header gh-canvas">
		<?php if (! is_page()) : ?>
			<div class="single-meta">
				<time class="single-meta-item single-meta-date" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
				<span class="single-meta-item single-meta-length"><?php echo esc_html(origin_get_reading_time()); ?></span>
				<?php if ($primary_category) : ?>
					<span class="single-meta-item single-meta-tag">
						<?php if ($primary_category_link) : ?>
							<a class="post-tag" href="<?php echo esc_url($primary_category_link); ?>"><?php echo esc_html($primary_category); ?></a>
						<?php else : ?>
							<?php echo esc_html($primary_category); ?>
						<?php endif; ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php the_title('<h1 class="single-title">', '</h1>'); ?>

		<?php if (has_excerpt()) : ?>
			<div class="single-excerpt"><?php the_excerpt(); ?></div>
		<?php endif; ?>

		<?php if (! is_page()) : ?>
			<div class="gh-meta-share">
				<button class="gh-button-share" type="button" data-origin-share data-share-title="<?php echo esc_attr(get_the_title()); ?>" data-share-url="<?php echo esc_url(get_permalink()); ?>">
					<span><?php esc_html_e('分享', 'origin'); ?></span>
					<?php origin_icon('share'); ?>
				</button>
			</div>
		<?php endif; ?>

		<?php if (has_post_thumbnail()) : ?>
			<figure class="single-media">
				<div class="u-placeholder horizontal">
					<?php the_post_thumbnail('large', array('class' => 'u-object-fit')); ?>
				</div>
				<?php if ($thumbnail_caption) : ?>
					<figcaption><?php echo esc_html($thumbnail_caption); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>
	</header>

	<div class="entry-content gh-canvas">
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
		<div class="single-tags gh-canvas">
			<?php the_tags('', ' '); ?>
		</div>
	<?php endif; ?>

	<?php if (! is_page()) : ?>
		<footer class="single-footer gh-canvas">
			<div class="single-footer-left">
				<?php
				$previous_post = get_previous_post();
				if ($previous_post) :
					?>
					<a class="navigation-link navigation-previous" href="<?php echo esc_url(get_permalink($previous_post)); ?>" aria-label="<?php echo esc_attr__('上一篇文章', 'origin'); ?>">
						<?php origin_icon('arrow-left'); ?>
					</a>
				<?php endif; ?>
			</div>

			<div class="single-footer-middle">
				<div class="single-footer-top">
					<h3 class="single-footer-title"><?php esc_html_e('发布者', 'origin'); ?></h3>
					<a class="author-image-placeholder u-placeholder square" href="<?php echo esc_url(get_author_posts_url((int) get_the_author_meta('ID'))); ?>" title="<?php echo esc_attr(get_the_author()); ?>">
						<?php echo get_avatar(get_the_author_meta('ID'), 96, '', get_the_author(), array('class' => 'author-image u-object-fit')); ?>
					</a>
				</div>
			</div>

			<div class="single-footer-right">
				<?php
				$next_post = get_next_post();
				if ($next_post) :
					?>
					<a class="navigation-link navigation-next" href="<?php echo esc_url(get_permalink($next_post)); ?>" aria-label="<?php echo esc_attr__('下一篇文章', 'origin'); ?>">
						<?php origin_icon('arrow-right'); ?>
					</a>
				<?php endif; ?>
			</div>
		</footer>
	<?php endif; ?>
</article>
