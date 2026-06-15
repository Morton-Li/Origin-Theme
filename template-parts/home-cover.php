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

		<?php if (! is_user_logged_in()) : ?>
			<div class="cover-cta">
				<?php if (get_option('users_can_register')) : ?>
					<button class="button" type="button" data-origin-auth-open="register"><?php esc_html_e('立即注册', 'origin'); ?></button>
				<?php endif; ?>
				<button class="button button-secondary" type="button" data-origin-auth-open="login"><?php esc_html_e('登录', 'origin'); ?></button>
			</div>
		<?php endif; ?>
	</div>
</section>
