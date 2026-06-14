<?php
/**
 * The search form template.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e('搜索关键词', 'origin'); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e('搜索文章', 'origin'); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s">
	</label>
	<button type="submit" class="search-submit" aria-label="<?php esc_attr_e('提交搜索', 'origin'); ?>">
		<span><?php esc_html_e('搜索', 'origin'); ?></span>
	</button>
</form>
