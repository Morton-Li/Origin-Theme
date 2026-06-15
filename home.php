<?php
/**
 * The posts homepage template.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main home-main">
	<?php
	get_template_part('template-parts/home', 'cover');
	get_template_part('template-parts/home', 'featured');
	?>

	<section class="home-feed gh-canvas" aria-labelledby="home-feed-title">
		<h2 id="home-feed-title" class="screen-reader-text"><?php esc_html_e('最新文章', 'origin'); ?></h2>

		<?php if (have_posts()) : ?>
			<div class="post-feed gh-feed">
				<?php
				while (have_posts()) :
					the_post();
					get_template_part('template-parts/content', 'feed');
				endwhile;
				?>
			</div>

			<?php origin_the_load_more_pagination(); ?>
		<?php else : ?>
			<section class="empty-state">
				<h3><?php esc_html_e('静候第一篇文章', 'origin'); ?></h3>
				<p><?php esc_html_e('当内容准备好时，首页会以清晰的文章流呈现每一次更新。', 'origin'); ?></p>
			</section>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
