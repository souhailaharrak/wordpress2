<?php

namespace WPDaddy\Builder\Elementor\Widgets;

use Elementor\Widget_Base;
use WPDaddy\Builder\Elementor\Basic;
use WPDaddy\Builder\Elementor\Widgets\Cart\Trait_Controls;
use WPDaddy\Builder\Elementor\Widgets\Cart\Trait_Render;

if(!defined('ABSPATH')) {
	exit;
}

class Cart extends Basic {
	use Trait_Controls;
	use Trait_Render;

	public function get_name(){
		return 'wpda-builder-cart';
	}

	public function get_title(){
		return esc_html__('Cart', 'wpda-builder');
	}

	public function get_icon(){
		return 'eicon-cart-light';
	}

	protected function construct(){
		$this->init_actions();

	}

	protected function init_actions(){
		static $loaded = false;
		if($loaded) {
			return;
		}
		$loaded = true;


	}

	public static function woocommerce_add_to_cart_fragments($fragments){
		global $woocommerce;
		ob_start();
		?>
		<?php echo '<span class="wpda_cart-count-number">'.esc_html(WC()->cart->cart_contents_count).'</span>'; ?>
		<?php
		$fragments['.wpda_cart-count-number'] = ob_get_clean();

		ob_start();
		echo '<div class="wpda-cart-container">';
		woocommerce_mini_cart();
		echo '</div>';
		$fragments['.wpda-cart-container'] = ob_get_clean();

		return $fragments;
	}

}

