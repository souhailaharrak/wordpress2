<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_Hotspot $widget */

$widget->start_controls_section(
	'basic',
	array(
		'label' => esc_html__('General', 'gt3_themes_core')
	)
);

$widget->add_responsive_control(
	'button_radius',
	array(
		'label'       => esc_html__('Button Radius', 'gt3_themes_core'),
		'type'        => Controls_Manager::SLIDER,
		'default'     => array(
			'size' => 28,
			'unit' => 'px',
		),
		'range'       => array(
			'px' => array(
				'min'  => 15,
				'max'  => 50,
				'step' => 1,
			),
		),
		'size_units'  => array( 'px' ),
		'description' => esc_html__('Enter button radius in pixels.', 'gt3_themes_core'),
		'label_block' => true,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-hotspot .gt3-hotspot-wrapper' => 'width: calc({{SIZE}}{{UNIT}} * 2); height: calc({{SIZE}}{{UNIT}} * 2);',
			'{{WRAPPER}} .gt3-hotspot-info-align-top_left .gt3-hotspot-info' => 'top: {{SIZE}}{{UNIT}}; left: {{SIZE}}{{UNIT}};',
			'{{WRAPPER}} .gt3-hotspot-info-align-top_right .gt3-hotspot-info' => 'top: {{SIZE}}{{UNIT}}; right: {{SIZE}}{{UNIT}};',
			'{{WRAPPER}} .gt3-hotspot-info-align-bottom_right .gt3-hotspot-info' => 'bottom: {{SIZE}}{{UNIT}}; right: {{SIZE}}{{UNIT}};',
			'{{WRAPPER}} .gt3-hotspot-info-align-bottom_left .gt3-hotspot-info' => 'bottom: {{SIZE}}{{UNIT}}; left: {{SIZE}}{{UNIT}};',
		),
	)
);

$widget->add_control(
	'info_position',
	array(
		'label'     => esc_html__('Info block position', 'gt3_themes_core'),
		'type'      => Controls_Manager::SELECT,
		'options'   => array(
			'top_left' => esc_html__('Top Left', 'gt3_themes_core'),
			'top_right' => esc_html__('Top Right', 'gt3_themes_core'),
			'bottom_left' => esc_html__('Bottom Left', 'gt3_themes_core'),
			'bottom_right' => esc_html__('Bottom Right', 'gt3_themes_core'),
		),
		'default'   => 'top_left',
	)
);

$widget->add_control(
	'active_state',
	array(
		'label'       => esc_html__('Active State', 'gt3_themes_core'),
		'type'        => Controls_Manager::SWITCHER,
		'description' => esc_html__('Allow the active state of the block?', 'gt3_themes_core'),
		'prefix_class' => 'active_state-',
	)
);

$widget->add_control(
	'title',
	array(
		'label'       => esc_html__('Info block Title', 'gt3_themes_core'),
		'type'        => Controls_Manager::TEXT,
		'description' => esc_html__('Enter text for title line.', 'gt3_themes_core'),
		'label_block' => true,
	)
);

$widget->add_control(
	'description',
	array(
		'label'   => esc_html__('Info block Description', 'gt3_themes_core'),
		'type'    => Controls_Manager::WYSIWYG,
		'default' => '',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'button_style',
	array(
		'label' => esc_html__('Button', 'gt3_themes_core'),
		'tab'   => Controls_Manager::TAB_STYLE,
	)
);

$widget->add_control(
	'button_color',
	array(
		'label'       => esc_html__('Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-hotspot .gt3-hotspot-button' => 'color: {{VALUE}};',
			'{{WRAPPER}}.elementor-widget-gt3-core-hotspot .gt3-hotspot-info' => 'border-top-color: {{VALUE}};',
		),
		'label_block' => true,
		'default'   => '#4ef4ad',
	)
);

$widget->add_control(
	'point_color',
	array(
		'label'       => esc_html__('Point Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-hotspot .gt3-hotspot-button:after' => 'background-color: {{VALUE}};',
		),
		'label_block' => true,
		'default'   => '#0a2a43',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'title_style',
	array(
		'label' => esc_html__('Title', 'gt3_themes_core'),
		'tab'   => Controls_Manager::TAB_STYLE,
	)
);

$widget->add_control(
	'title_color',
	array(
		'label'       => esc_html__('Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'selectors'   => array(
			'{{WRAPPER}} .gt3_hotspot_title h3' => 'color: {{VALUE}};',
		),
		'label_block' => true,
	)
);

$widget->add_group_control(
	Group_Control_Typography::get_type(),
	array(
		'name'     => 'title_typography',
		'selector' => '{{WRAPPER}} .gt3_hotspot_title h3',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'description_style',
	array(
		'label' => esc_html__('Description', 'gt3_themes_core'),
		'tab'   => Controls_Manager::TAB_STYLE,
	)
);

$widget->add_control(
	'description_color',
	array(
		'label'       => esc_html__('Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'selectors'   => array(
			'{{WRAPPER}} .gt3_hotspot_descr' => 'color: {{VALUE}};',
		),
		'label_block' => true,
	)
);

$widget->add_group_control(
	Group_Control_Typography::get_type(),
	array(
		'name'     => 'description_typography',
		'selector' => '{{WRAPPER}} .gt3_hotspot_descr',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'area_style',
	array(
		'label' => esc_html__('Area', 'gt3_themes_core'),
		'tab'   => Controls_Manager::TAB_STYLE,
	)
);

$widget->add_control(
	'area_bg',
	array(
		'label'       => esc_html__('Background Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'selectors'   => array(
			'{{WRAPPER}} .gt3-hotspot-info' => 'background-color: {{VALUE}};',
		),
		'label_block' => true,
	)
);

$widget->end_controls_section();
