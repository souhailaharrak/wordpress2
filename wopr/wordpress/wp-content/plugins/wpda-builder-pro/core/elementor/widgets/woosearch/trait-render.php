<?php

namespace WPDaddy\Builder\Elementor\Widgets\WooSearch;

if (!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

trait Trait_Render {

	protected function render_widget() {
		$settings = array();

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute(
			'wrapper', 'class', array(
				'wpda-builder-search',
			)
		);

		$form = get_search_form(array('echo' => false));

		$form = preg_replace(
			'/(<input(.*)?type=[\'|"]submit[\'|"](.*)?>)/i',
			'<input type="hidden" name="post_type" value="product">'.PHP_EOL.'$1',
			$form);

		?>
		<div <?php $this->print_render_attribute_string('wrapper') ?>>
			<div class="wpda-search_icon"><i></i></div>
			<div class="wpda-search_inner">
				<?php echo($form); ?>
			</div>
		</div>
		<?php
	}
}

