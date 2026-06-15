<?php
/**
 * The page template.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main page-template">
	<?php
	while (have_posts()) :
		the_post();
		get_template_part('template-parts/content', 'single');
	endwhile;
	?>
</main>

<?php
get_footer();
