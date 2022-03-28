<?php
defined('ABSPATH') OR exit;


function is_shop_wpda() {
	static  $is_shop = null;
	if (!is_null($is_shop)) return $is_shop;
	/*if (class_exists('Elementor\Plugin') && \Elementor\Plugin::instance()->preview->is_preview()) {
		$is_shop = true;
		return $is_shop;
	}*/

	if(!class_exists('WooCommerce')) {
		$is_shop = false;

		return $is_shop;
	}

	if (is_singular()) {
		global $post;
		$post_id = $post->ID;
		$whitelist = apply_filters('wpda-builder/filter/allow-elementor/is_shop', array());
		if (is_array($whitelist) && count($whitelist)) {
			$meta = get_post_meta($post->ID, '_elementor_controls_usage', true);
			if (!empty($meta)) {
				$meta = maybe_unserialize($meta);

				foreach($whitelist as $item) {
					if(is_array($meta) && key_exists($item, $meta)) {
						$is_shop = true;
						break;
					}
				}
			}
			if (empty($meta) || !is_array($meta)) {

				function wpda_find_elementor_widget(&$key) {
					if(key_exists('widgetType', $key) && in_array($key['widgetType'], apply_filters('wpda-builder/filter/allow-elementor/is_shop', array()))) {
						return true;
					}

					if(key_exists('elements', $key) && is_array($key['elements']) && count($key['elements'])) {
						foreach($key['elements'] as &$element) {
							if (wpda_find_elementor_widget($element)) return true;
						}
					}
					return false;
				}

				$elementor       = \Elementor\Plugin::instance();
				$elementor_post  = $elementor->documents->get($post_id);
				$is_meta_updated = null;
				if($elementor_post !== false) {
					$meta = $elementor_post->get_json_meta('_elementor_data');
					foreach($meta as &$level0) {
						$is_shop =	wpda_find_elementor_widget($level0);
						if ($is_shop) break;
					}
				}

			}
		}
	}
	if (!$is_shop && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_product() || is_cart() || is_account_page() || is_checkout())) {
		$is_shop = true;
	}
	if (!$is_shop) $is_shop = false;

	return $is_shop;
}
