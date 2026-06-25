<?php
/**
 * Theme user dashboard.
 *
 * @package Origin
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$current_user = wp_get_current_user();
$display_name = '' !== $current_user->display_name ? $current_user->display_name : $current_user->user_login;
$notice       = origin_get_account_notice();
$can_manage_content = current_user_can('publish_posts');

$approved_comments = (int) get_comments(
	array(
		'count'   => true,
		'status'  => 'approve',
		'user_id' => $current_user->ID,
	)
);
$pending_comments = (int) get_comments(
	array(
		'count'   => true,
		'status'  => 'hold',
		'user_id' => $current_user->ID,
	)
);
$published_posts = $can_manage_content ? (int) count_user_posts($current_user->ID, 'post', true) : 0;
$recent_comments = get_comments(
	array(
		'number'  => 5,
		'status'  => 'approve',
		'user_id' => $current_user->ID,
	)
);
$registered_at = $current_user->user_registered ? wp_date(get_option('date_format'), strtotime($current_user->user_registered)) : '';
$primary_role   = $current_user->roles[0] ?? '';
$role_name      = isset(wp_roles()->roles[$primary_role]['name']) ? translate_user_role(wp_roles()->roles[$primary_role]['name']) : __('用户', 'origin');

get_header();
?>

<main id="primary" class="site-main account-main">
	<section class="account-header gh-canvas">
		<div class="account-avatar">
			<?php echo get_avatar($current_user->ID, 96, '', $display_name, array('class' => 'account-avatar-image')); ?>
		</div>
		<div class="account-heading">
			<p class="account-kicker"><?php esc_html_e('个人中心', 'origin'); ?></p>
			<h1><?php echo esc_html($display_name); ?></h1>
			<p><?php esc_html_e('管理你的账户资料，查看近期互动。', 'origin'); ?></p>
		</div>
		<div class="account-header-actions">
			<a class="gh-btn" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('返回首页', 'origin'); ?></a>
		</div>
	</section>

	<div class="account-shell gh-canvas">
		<?php if ('' !== $notice['message']) : ?>
			<div class="account-notice is-<?php echo esc_attr($notice['type']); ?>" role="status">
				<?php echo esc_html($notice['message']); ?>
			</div>
		<?php endif; ?>

		<div class="account-layout">
			<section class="account-panel account-profile">
				<div class="account-section-heading">
					<h2><?php esc_html_e('账户资料', 'origin'); ?></h2>
					<p><?php esc_html_e('用户名不可修改，显示名称会用于站内展示。', 'origin'); ?></p>
				</div>

				<form class="account-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
					<input type="hidden" name="action" value="origin_update_profile">
					<?php wp_nonce_field('origin_update_profile', 'origin_update_profile_nonce'); ?>

					<label for="origin-account-login"><?php esc_html_e('用户名', 'origin'); ?></label>
					<input id="origin-account-login" type="text" value="<?php echo esc_attr($current_user->user_login); ?>" disabled>

					<label for="origin-account-display-name"><?php esc_html_e('显示名称', 'origin'); ?></label>
					<input id="origin-account-display-name" name="origin_display_name" type="text" value="<?php echo esc_attr($display_name); ?>" autocomplete="name" required>

					<label for="origin-account-email"><?php esc_html_e('邮箱', 'origin'); ?></label>
					<input id="origin-account-email" name="origin_email" type="email" value="<?php echo esc_attr($current_user->user_email); ?>" autocomplete="email" required>

					<div class="account-form-actions">
						<button class="gh-btn gh-primary-btn" type="submit"><?php esc_html_e('保存资料', 'origin'); ?></button>
					</div>
				</form>
			</section>

			<aside class="account-aside" aria-label="<?php esc_attr_e('账户概览', 'origin'); ?>">
				<div class="account-stats">
					<div class="account-stat">
						<span><?php esc_html_e('评论', 'origin'); ?></span>
						<strong><?php echo esc_html(number_format_i18n($approved_comments)); ?></strong>
					</div>
					<div class="account-stat">
						<span><?php esc_html_e('待审核', 'origin'); ?></span>
						<strong><?php echo esc_html(number_format_i18n($pending_comments)); ?></strong>
					</div>
					<?php if ($can_manage_content) : ?>
						<div class="account-stat">
							<span><?php esc_html_e('文章', 'origin'); ?></span>
							<strong><?php echo esc_html(number_format_i18n($published_posts)); ?></strong>
						</div>
					<?php endif; ?>
				</div>

				<?php if (current_user_can('manage_options')) : ?>
					<section class="account-panel account-access">
						<h2><?php esc_html_e('站点管理', 'origin'); ?></h2>
						<p><?php esc_html_e('当前账户可以进入管理后台。', 'origin'); ?></p>
						<a class="gh-btn gh-primary-btn" href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('进入管理', 'origin'); ?></a>
					</section>
				<?php elseif ($can_manage_content) : ?>
					<section class="account-panel account-access">
						<h2><?php esc_html_e('内容管理', 'origin'); ?></h2>
						<p><?php esc_html_e('当前账户可以进入写作后台。', 'origin'); ?></p>
						<a class="gh-btn gh-primary-btn" href="<?php echo esc_url(admin_url('edit.php')); ?>"><?php esc_html_e('进入写作', 'origin'); ?></a>
					</section>
				<?php endif; ?>

				<section class="account-panel account-session">
					<h2><?php esc_html_e('登录会话', 'origin'); ?></h2>
					<p><?php esc_html_e('退出后，你可以随时重新登录。', 'origin'); ?></p>
					<form class="account-logout" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
						<input type="hidden" name="action" value="origin_logout">
						<input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/')); ?>">
						<?php wp_nonce_field('origin_logout', 'origin_logout_nonce'); ?>
						<button class="gh-btn" type="submit"><?php esc_html_e('退出登录', 'origin'); ?></button>
					</form>
				</section>

				<section class="account-panel account-meta">
					<h2><?php esc_html_e('账户信息', 'origin'); ?></h2>
					<dl>
						<div>
							<dt><?php esc_html_e('注册时间', 'origin'); ?></dt>
							<dd><?php echo esc_html($registered_at ?: __('未知', 'origin')); ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e('账户角色', 'origin'); ?></dt>
							<dd><?php echo esc_html($role_name); ?></dd>
						</div>
					</dl>
				</section>
			</aside>
		</div>

		<section class="account-panel account-activity">
			<div class="account-section-heading">
				<h2><?php esc_html_e('最近评论', 'origin'); ?></h2>
				<p><?php esc_html_e('已通过审核的评论会出现在这里。', 'origin'); ?></p>
			</div>

			<?php if ($recent_comments) : ?>
				<ol class="account-comment-list">
					<?php foreach ($recent_comments as $comment) : ?>
						<li>
							<a href="<?php echo esc_url(get_comment_link($comment)); ?>">
								<span><?php echo esc_html(get_the_title($comment->comment_post_ID) ?: __('文章', 'origin')); ?></span>
								<time datetime="<?php echo esc_attr(get_comment_date('c', $comment->comment_ID)); ?>"><?php echo esc_html(get_comment_date(get_option('date_format'), $comment->comment_ID)); ?></time>
							</a>
							<p><?php echo esc_html(wp_trim_words(get_comment_excerpt($comment->comment_ID), 24)); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="account-empty"><?php esc_html_e('你还没有已公开的评论。', 'origin'); ?></p>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php
get_footer();
