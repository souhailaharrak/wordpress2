<?php

namespace WPDaddy\Builder\Elementor\Widgets\Login;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Plugin;
use Elementor\Utils;

trait Trait_Render {
	protected function render_widget(){
		$settings = array();

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute(
			'wrapper', 'class', array(
				'wpda-builder-login',
			)
		);
		$editor = Plugin::$instance->editor->is_edit_mode();
		if(!$editor) {
			add_action('wp_footer', array( $this, 'render_wrapper' ), PHP_INT_MAX);
		}

		$this->add_render_attribute('wrapper', 'data-id', $this->get_id())

		?>
		<div <?php $this->print_render_attribute_string('wrapper') ?>>
			<?php
				if ( is_user_logged_in() ) {
					$user = wp_get_current_user();
					echo '<p><span class="user_avatar">'.get_avatar( $user->user_email, 25 ) . '</span><span class="user_login">'.$user->display_name . '</span></p>';
				}else {
					echo '<p>' . esc_html__( 'Login / Register', 'wpda-builder' ) . '</p>';
				}
			?>
		</div>
		<?php
	}

	public function render_wrapper(){
		if(!class_exists('WooCommerce')) {
			return;
		}

		echo "<div class='wpda-builder__login-modal login-id-".$this->get_id()." ".(get_option('woocommerce_enable_myaccount_registration') !='yes' ? ' without_register' : '').(is_user_logged_in() ? ' user_logged_in' : '')."'>";
		echo "<div class='wpda-builder__login-modal-cover'></div>";
		echo "<div class='wpda-builder__login-modal_container container'>";
		echo "<div class='wpda-builder__login-modal-close'></div>";
		if ( is_user_logged_in() ) {
			wc_get_template('myaccount/navigation.php');
		}
		if (!is_user_logged_in()) {
			$wpda_notice = wc_get_notices();
			echo do_shortcode('[woocommerce_my_account]');
			wc_set_notices($wpda_notice);
		}
		echo "</div>";

		echo "</div>";
	}

}

