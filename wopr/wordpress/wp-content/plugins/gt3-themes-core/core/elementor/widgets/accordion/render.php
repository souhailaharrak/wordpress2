<?php

if(!defined('ABSPATH')) {
	exit;
}

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_Accordion $widget */

$settings = array(
	'items' => array(
		array(
			'title'   => esc_html__('Accordion #1', 'gt3_themes_core'),
			'content' => esc_html__('I am item content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'gt3_themes_core'),
		),
		array(
			'title'   => esc_html__('Accordion #2', 'gt3_themes_core'),
			'content' => esc_html__('I am item content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'gt3_themes_core'),
		),
		array(
			'title'   => esc_html__('Accordion #3', 'gt3_themes_core'),
			'content' => esc_html__('I am item content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'gt3_themes_core'),
		),
		array(
			'title'   => esc_html__('Accordion #4', 'gt3_themes_core'),
			'content' => esc_html__('I am item content. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'gt3_themes_core'),
		),
	),
	'collapsible' => '',
);

$settings = wp_parse_args($widget->get_settings(), $settings);

$css_class = array(
	'accordion_wrapper',
);

$widget->add_render_attribute('wrapper', 'class', $css_class);

$data = array(
	'collapsible' => (bool) $settings['collapsible'],
	'active'      => ($settings['collapsible'] === 'yes') ? false : 0,
);

$widget->add_render_attribute('wrapper', 'data-settings', wp_json_encode($data));

if(isset($settings['items']) && is_array($settings['items'])) {
	?>
	<div <?php $widget->print_render_attribute_string('wrapper') ?>>
	<?php
	foreach($settings['items'] as $item) {
		echo '<div class="item_title">'.esc_html($item['title']).'</div>'.
			'<div class="item_content">'.$item['content'].'</div>';
	}
	echo '</div>';
}

$widget->print_data_settings($data);


