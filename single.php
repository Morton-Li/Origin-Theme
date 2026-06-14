<?php
/**
 * The single post template.
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
	<?php
	while (have_posts()) :
		the_post();
		get_template_part('template-parts/content', 'single');

		the_post_navigation(
			array(
				'prev_text' => '<span class="nav-subtitle">' . esc_html__('上一篇', 'origin') . '</span><span class="nav-title">%title</span>',
				'next_text' => '<span class="nav-subtitle">' . esc_html__('下一篇', 'origin') . '</span><span class="nav-title">%title</span>',
			)
		);

		if (comments_open() || get_comments_number()) {
			comments_template();
		}
	endwhile;
	?>
</main>

<?php
get_footer();
