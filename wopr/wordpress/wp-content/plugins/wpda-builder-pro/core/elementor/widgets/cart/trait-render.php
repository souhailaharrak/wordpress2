<?php

namespace WPDaddy\Builder\Elementor\Widgets\Cart;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Api;
use Elementor\Plugin;
use Elementor\Utils;

trait Trait_Render {

	protected function render_widget(){
		$settings = array();

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute(
			'wrapper', 'class', array(
			'wpda-builder-cart'
		)
		);
		if(class_exists('WooCommerce')) { ?>
			<div <?php $this->print_render_attribute_string('wrapper') ?>>
				<a class="wpda_cart-icon" href="<?php echo wc_get_cart_url(); ?>">
					<i class='wpda_cart-count'>
						<?php if(!is_admin()) { ?>
							<span class="wpda_cart-count-number"><?php echo esc_html(WC()->cart->cart_contents_count); ?></span>
						<?php } ?>
						<?php echo apply_filters( 'wpda_cart_menu_icon', '' ); ?>
					</i>
				</a>
				<div class="wpda-cart-inner">
					<div class="wpda-cart-container">
						<?php if(!is_admin()) {
							woocommerce_mini_cart();
						} ?>
					</div>
				</div>
			</div>
			<?php
		}
	}
}

