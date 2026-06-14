<?php
/**
 * Template part for the homepage cover.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$site_icon_url    = get_site_icon_url(96);
$site_description = get_bloginfo('description', 'display');
?>

<section class="cover gh-outer" aria-labelledby="home-cover-title">
	<div class="cover-content gh-inner">
		<h1 id="home-cover-title" class="screen-reader-text"><?php bloginfo('name'); ?></h1>

		<?php if ($site_icon_url) : ?>
			<div class="cover-icon" aria-hidden="true">
				<img class="cover-icon-image" src="<?php echo esc_url($site_icon_url); ?>" alt="">
			</div>
		<?php endif; ?>

		<?php if ($site_description) : ?>
			<div class="cover-description"><?php echo esc_html($site_description); ?></div>
		<?php endif; ?>
	</div>
</section>
