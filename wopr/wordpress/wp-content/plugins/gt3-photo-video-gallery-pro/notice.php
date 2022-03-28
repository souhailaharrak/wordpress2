<?php
defined('ABSPATH') OR exit;

if(!get_option('gt3pg_pro_disable_optimizer_notice') && !defined('GT3PG_FHD_PLUGINPATH')) {
	add_action('admin_notices', 'gt3pg_pro_optimizer_notice');

	add_action('wp_ajax_gt3pg_pro_disable_notice', 'wp_ajax_gt3pg_pro_disable_notice');

	function wp_ajax_gt3pg_pro_disable_notice(){
		if(!current_user_can('manage_options') || !isset($_POST['gt3_action']) || !key_exists('_nonce', $_POST) || !wp_verify_nonce($_POST['_nonce'], 'gt3_notice')) {
			wp_die(0);
		}
		switch($_POST['gt3_action']) {
			case 'disable_optimizer_notice':
				update_option('gt3pg_pro_disable_optimizer_notice', true);
				break;
		}
		wp_die(1);
	}

	function gt3pg_pro_optimizer_notice(){
		$msg   = 'The Image Optimizer for GT3 Photo & Video Gallery Pro is now available.  Check it now -> <a href="http://bit.ly/2EuDsUW" target="_blank">GT3 Image Optimizer</a>';
		$class = 'notice notice-warning gt3pg-pro-optimizer-notice';
		echo '<div class="'.$class.'" style="position: relative"><p>'.$msg.'</p>'.
		     (current_user_can('manage_options') ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.esc_html__('Dismiss this notice','wordpress').'</span></button>' : '').
		     '</div>';
		?>
		<script>
			(function () {
				var notice = document.querySelector('.gt3pg-pro-optimizer-notice');
				if (notice) {
					var notice_dismiss = notice.querySelector('.notice-dismiss');
					notice_dismiss && notice_dismiss.addEventListener && notice_dismiss.addEventListener('click', function (e) {
						jQuery.ajax({
							url: ajaxurl,
							method: "POST",
							data: {
								action: "gt3pg_pro_disable_notice",
								gt3_action: "disable_optimizer_notice",
								_nonce: '<?php echo wp_create_nonce('gt3_notice'); ?>',
							}
						});
						jQuery(notice).fadeOut();
					});
				}
			})();
		</script>
		<?php
	}
};
