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

		if (comments_open() || get_comments_number()) {
			comments_template();
		}
	endwhile;
	?>
</main>

<button class="origin-back-to-top" type="button" aria-label="<?php esc_attr_e('返回顶部', 'origin'); ?>" data-origin-back-to-top>
	<span class="origin-back-to-top-icon"><?php origin_icon('arrow-up'); ?></span>
</button>

<?php
get_footer();
