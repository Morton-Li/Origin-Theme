<?php
/**
 * The footer for Origin.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
?>
	</div>

	<footer class="gh-foot gh-outer">
		<div class="gh-foot-inner gh-inner">
			<div class="gh-copyright">
				<?php
				printf(
					/* translators: 1: Site title, 2: Current year. */
					esc_html__('%1$s © %2$s', 'origin'),
					esc_html(get_bloginfo('name')),
					esc_html(gmdate('Y'))
				);
				?>
			</div>
			<?php if (has_nav_menu('footer')) : ?>
				<nav class="gh-foot-menu" aria-label="<?php esc_attr_e('页脚导航', 'origin'); ?>">
					<?php
					wp_nav_menu(
						array(
							'container'      => false,
							'theme_location' => 'footer',
							'menu_class'     => 'menu',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
