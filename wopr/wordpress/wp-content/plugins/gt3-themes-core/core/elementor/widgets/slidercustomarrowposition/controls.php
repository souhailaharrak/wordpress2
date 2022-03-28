<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Controls_Manager;

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_SliderCustomArrowPosition $widget */

$widget->start_controls_section(
	'section_content',
	array(
		'label' => esc_html__('Content', 'gt3_themes_core'),
	)
);

$widget->add_control('module_title',
	array(
		'type' => \Elementor\Controls_Manager::HEADING,
		'label' => 'Custom Arrows Position for Slick Slider',
	));

$widget->add_control('module_description',
	array(
		'type' => \Elementor\Controls_Manager::RAW_HTML,
		'raw' => 'Can be used for GT3 modules with <span style="color:red">slick-arrows</span> only.',
	));

$widget->add_control(
	'arrows_color',
	array(
		'label'     => esc_html__('Arrows Color', 'gt3_themes_core'),
		'type'      => Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} .gt3_section_arrows_position' => 'color: {{VALUE}};'
		),
	)
);

$widget->end_controls_section();
