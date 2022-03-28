<?php

namespace WPDaddy\Builder\Registration;

trait Notice_Trait {
	public static $NOTICE_TYPE = array(
		'success' => 'success',
		'error'   => 'error',
		'warning' => 'warning',
		'info'    => 'info',
	);

	private static $action_nonce = 'wpda_builder_notice';

	private $notice = array();

	private function get_notice(){
		return array(
			'registration' => array(
				'option'    => sprintf('%s_registration_notice', $this->product_slug),
				'render'    => array( $this, 'registration_notice' ),
				'condition' => function(){
					return !$this->is_active();
				}
			),
			'support'      => array(
				'option'    => sprintf('%s_support_notice', $this->product_slug),
				'render'    => array( $this, 'support_notice' ),
				'condition' => function(){
					$support = $this->get_support_time_left();

					return $this->is_active() && ($support['expired'] || $support['notice_start']);
				}
			),
		);
	}

	function init_notice(){
		$this->notice = $this->get_notice();

		if(is_array($this->notice) && count($this->notice)) {
			$has_notice = false;

			foreach($this->notice as $notice) {
				$notice          = array_merge(
					array(
						'option'    => '',
						'type'      => false,
						'img'       => false,
						'msg'       => '',
						'callback'  => '',
						'condition' => function(){
							return true;
						}
					), $notice
				);
				$render_function = array( $this, 'basic_render' );

				if(!get_user_meta(get_current_user_id(), $notice['option'], true)
				   && call_user_func($notice['condition'])) {
					if(is_string($notice['render'])) {
						$render_function = function_exists($notice['render']) && is_callable($notice['render'])
							? $notice['render'] : (method_exists($this, $notice['render']) && is_callable(array( $this, $notice['render'] )) ? array(
								$this,
								$notice['render']
							) : array( $this, 'basic_render' ));
					} else if(is_array($notice['render']) && is_callable($notice['render'])) {
						$render_function = $notice['render'];
					}

					$has_notice = true;
					add_action(
						'admin_notices', function() use ($notice, $render_function){
						call_user_func($render_function, $notice);
					}
					);
				}
			}
			if($has_notice) {
				add_action('wp_ajax_'.self::$action_nonce.'_disable_notice', array( $this, 'ajax_handler' ));
			}
		}
	}

	private function render_dismiss_script($name){
		?>
		<script>
			(function () {
				var notice = document.querySelector('.<?php echo $name?>_info');
				if (notice) {
					notice = notice.querySelector('.notice-dismiss');
					notice && notice.addEventListener && notice.addEventListener('click', function (e) {
						jQuery.ajax({
							url: ajaxurl,
							method: "POST",
							data: {
								action: "<?php echo self::$action_nonce.'_disable_notice'; ?>",
								wpda_action: "<?php echo $name?>",
								_nonce: "<?php echo wp_create_nonce(self::$action_nonce); ?>",
							}
						})
					})
				}
			})();
		</script>
		<?php
	}

	function ajax_handler(){
		if(!current_user_can('manage_options') || !isset($_POST['wpda_action']) || !key_exists('_nonce', $_POST) || !wp_verify_nonce($_POST['_nonce'], self::$action_nonce)) {
			wp_die(0);
		}
		$action = $_POST['wpda_action'];

		if(key_exists($action, $this->notice)) {
			$notice = $this->notice[$action];
			if(key_exists('action_callback', $this->notice[$action])) {
				if(method_exists($this, $notice['action_callback']) && is_callable(array( $this, $notice['action_callback'] ))) {
					call_user_func(array( $this, $notice['action_callback'] ));
				}
			} else {
				update_user_meta(get_current_user_id(), $notice['option'], true);
			}
		}
		wp_die(1);
	}


	function registration_notice($notice){
		$name  = $notice['option'];
		$class = 'notice notice-error '.$name.'_info';
		$activation_url = menu_page_url('wpda-builder-settings', false);
		$purchase_url = sprintf('https://codecanyon.net/item/plugin/%s', $this->product_id);
		?>
		<div class="<?php echo $class; ?>" style="padding: 5px 25px 15px 75px;position: relative;">
			<i class="fa fa-exclamation" aria-hidden="true"
			   style="position: absolute; top: 50%; left: 15px; margin-top: -22px;font-size: 25px; line-height: 40px; width: 40px;text-align: center; border: 2px solid;border-radius: 40px;"></i>
			<p><?php esc_html_e('Purchase Validation! Please activate your product.', 'wpda-builder'); ?></p>
			<div>
				<a class="button button-primary" href="<?php echo esc_url($activation_url) ?>">
					<?php esc_html_e('Register Now', 'wpda-builder'); ?> <i class="fa fa-angle-right" aria-hidden="true"></i>
				</a>
				<a target="_blank" class="button button-primary" href="<?php echo esc_url($purchase_url)?>">
					<?php esc_html_e('Buy Plugin', 'wpda-builder'); ?> <i class="fa fa-angle-right" aria-hidden="true"></i>
				</a>
				<?php echo(current_user_can('manage_options') ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' : '') ?>
			</div>
		</div>
		<?php
		$this->render_dismiss_script($name);
	}

	function support_notice($notice){
		$support_time_left = $this->get_support_time_left();
		$name              = $notice['option'];
		$class             = 'notice notice-error '.$name.'_info';
		$update_support_url = $this->get_support_url();
		?>
		<div class="<?php echo $class; ?>" style="padding: 5px 25px 15px 75px;position: relative;">
			<i class="fa fa-exclamation" aria-hidden="true"
			   style="position: absolute; top: 50%; left: 15px; margin-top: -22px;font-size: 25px; line-height: 40px; width: 40px;text-align: center; border: 2px solid;border-radius: 40px;"></i>
			<p style="font-size: 1.4em;font-weight: 500;margin-bottom: 0;"><?php
				if(!empty($support_time_left['expired']) && $support_time_left['expired'] == true) {
					esc_html_e('Your support package for this plugin expired', 'wpda-builder'); ?><?php echo " ( ".$support_time_left['human_time_to_left']." ".esc_html__('ago', 'wpda-builder')." ).";
				} else {
					esc_html_e('Your support package for this plugin is about to expire', 'wpda-builder'); ?><?php echo " ( ".$support_time_left['human_time_to_left']." ".esc_html__('left', 'wpda-builder')." ).";
				}
				?></p>
			<div style="margin-top: 10px;">
				<a class="button button-primary" target="_blank" href="<?php echo esc_url($update_support_url); ?>"><?php esc_html_e('Update Support Package', 'wpda-builder'); ?>
					<i class="fa fa-angle-right" aria-hidden="true"></i></a>
				<?php echo(current_user_can('manage_options') ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' : '') ?>
			</div>
		</div>
		<?php
		$this->render_dismiss_script($name);
	}

	private function basic_render($notice){
		$notice = array_merge(
			array(
				'option' => false,
				'type'   => self::$NOTICE_TYPE['info'],
				'img'    => false,
				'msg'    => '',
			), $notice
		);
		$name   = $notice['option'];
		$type   = $notice['type'];
		$img    = $notice['img'];
		$msg    = $notice['msg'];
		if(!$name || !$msg) {
			return;
		}

		$class = array(
			'notice',
			'notice-'.$type,
			$name.'_info',
			$img ? 'with-image' : null,
		);
		echo '<div class="'.join(' ', $class).'" style="position: relative">'.
		     ($img ? '<img src="'.$img.'" class="icon"/>' : '').
		     $msg.
		     (current_user_can('manage_options') ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>' : '').
		     '</div>';
		?>
		<script>
			(function () {
				var notice = document.querySelector('.<?php echo $name?>_info');
				if (notice) {
					notice = notice.querySelector('.notice-dismiss');
					notice && notice.addEventListener && notice.addEventListener('click', function (e) {
						jQuery.ajax({
							url: ajaxurl,
							method: "POST",
							data: {
								action: "<?php echo self::$action_nonce?>_disable_notice",
								wpda_action: "<?php echo $name?>",
								_nonce: '<?php echo wp_create_nonce(self::$action_nonce); ?>',
							}
						}).then(function (){
							notice.remove();
						})
					})
				}
			})();
		</script>
		<?php
	}
}
