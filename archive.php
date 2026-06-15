<?php
/**
 * The archive template.
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
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description('<div class="archive-description">', '</div>'); ?>
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
	<?php endif; ?>
</main>

<?php
get_footer();
