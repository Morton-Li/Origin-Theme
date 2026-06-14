<?php
/**
 * The comments template.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

if (post_password_required()) {
	return;
}
?>

<section id="comments" class="comments-area gh-canvas">
	<?php if (have_comments()) : ?>
		<h2 class="comments-title">
			<?php
			printf(
				/* translators: %s: Number of comments. */
				esc_html__('%s 条评论', 'origin'),
				esc_html(number_format_i18n(get_comments_number()))
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'avatar_size' => 48,
					'style'       => 'ol',
					'short_ping'  => true,
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
