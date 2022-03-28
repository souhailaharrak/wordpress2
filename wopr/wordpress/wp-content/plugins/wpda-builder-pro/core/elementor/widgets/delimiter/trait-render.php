<?php

namespace WPDaddy\Builder\Elementor\Widgets\Delimiter;

if (!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

trait Trait_Render {

	protected function render_widget() {
		$settings = array(
			'height_tablet' => array(
				'unit' => 'px'
			),
			'height_mobile' => array(
				'unit' => 'px'
			),

		);

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute('wrapper', 'class', array(
			'wpda-builder-delimiter',
			$settings['height']['unit'] === '%' ? 'unit_percent' : '',
			$settings['height_tablet']['unit'] === '%' ? 'unit_percent_tablet' : '',
			$settings['height_mobile']['unit'] === '%' ? 'unit_percent_mobile' : '',
		));

		?>
		<div <?php $this->print_render_attribute_string('wrapper') ?>></div>
		<?php
	}

	protected function _content_template1() {
		?>
		<#
		console.log(settings.height,settings.height_tablet,settings.height_mobile)

		view.addRenderAttribute('wrapper','class',[
		'wpda-builder-delimiter',
		settings.height.unit === '%' ? 'unit_percent' : '',
		settings.height_tablet.unit === '%' ?'unit_percent_tablet' : '',
		settings.height_mobile.unit === '%' ?'unit_percent_mobile' : '',
		]);

		#>
		<div {{{ view.getRenderAttributeString( 'wrapper' ) }}}></div>
		<?php
	}
}

