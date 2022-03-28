<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\GT3_Core_Elementor_Control_Query;
use Elementor\Group_Control_Typography;

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_PortfolioTitle $widget */

$widget->start_controls_section(
	'query',
	array(
		'label' => esc_html__('Query', 'gt3_themes_core'),
	)
);
$widget->add_control(
	'query',
	array(
		'label'       => esc_html__('Query', 'gt3_themes_core'),
		'type'        => GT3_Core_Elementor_Control_Query::type(),
		'settings'    => array(
			'showCategory'  => true,
			'showUser'      => true,
			'showPost'      => true,
			'post_type'     => $widget->POST_TYPE,
			'post_taxonomy' => $widget->TAXONOMY,
		),
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'section',
	array(
		'label' => esc_html__('Style', 'gt3_themes_core'),
	)
);

$widget->add_control(
	'show_category',
	array(
		'label' => esc_html__('Show Category', 'gt3_themes_core'),
		'type'  => Controls_Manager::SWITCHER,
		'default'   => 'yes'
	)
);

$widget->add_control(
	'show_image',
	array(
		'label' => esc_html__('Show Image (Hovered State)', 'gt3_themes_core'),
		'type'  => Controls_Manager::SWITCHER,
		'description' => esc_html__('If checked and post has featured image', 'gt3_themes_core'),
		'default'   => 'yes'
	)
);

$widget->add_group_control(
	Group_Control_Typography::get_type(),
	array(
		'name'      => 'title_typography',
		'label'     => esc_html__('Title Typography', 'gt3_themes_core'),
		'selector'  => '{{WRAPPER}}.elementor-widget-gt3-core-portfoliotitle .portfolio_item_wrap > a',
	)
);

$widget->add_group_control(
	Group_Control_Typography::get_type(),
	array(
		'name'      => 'categories_typography',
		'label'     => esc_html__('Categories Typography', 'gt3_themes_core'),
		'condition' => array(
			'show_category!' => '',
		),
		'selector'  => '{{WRAPPER}}.elementor-widget-gt3-core-portfoliotitle .portfolio_item_wrap',
	)
);

$widget->add_control(
	'title_color',
	array(
		'label'       => esc_html__('Text Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'label_block' => true,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-portfoliotitle .portfolio_item_wrap' => 'color: {{VALUE}};',
		),
	)
);

$widget->add_control(
	'title_color_active',
	array(
		'label'       => esc_html__('Active Text Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'label_block' => true,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-portfoliotitle .portfolio_item_wrap:hover' => 'color: {{VALUE}};',
		),
	)
);

$widget->end_controls_section();

