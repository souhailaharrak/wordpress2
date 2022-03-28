<?php

namespace WPDaddy\Builder\Elementor\Widgets\Search;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

trait Trait_Render {

	protected function render_widget(){
		$settings = array();

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute(
			'wrapper', 'class', array(
			'wpda-builder-search',
		)
		);
		?>
		<div <?php $this->print_render_attribute_string('wrapper') ?>>
			<div class="wpda-search_icon"><i></i></div>
			<div class="wpda-search_inner">
				<?php echo get_search_form(); ?>
			</div>
		</div>
		<?php
	}
}

