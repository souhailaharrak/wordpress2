<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

use Elementor\Group_Control_Text_Shadow;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_AnimatedHeadlines $widget */


$widget->start_controls_section(
	'section_headline',
	array(
		'label' => esc_html__( 'Headline', 'gt3_themes_core' ),
	)
);

$widget->add_control(
	'title',
	array(
		'label' => esc_html__( 'Headline', 'gt3_themes_core' ),
		'type' => Controls_Manager::TEXTAREA,
		'dynamic' => array(
			'active' => true,
		),
		'placeholder' => esc_html__( 'Enter your title', 'gt3_themes_core' ),
		'default' => esc_html__( 'Add Your Heading Text Here', 'gt3_themes_core' ),
	)
);

$widget->add_control(
	'headlines_style',
	array(
		'label'     => esc_html__('Style', 'gt3_themes_core'),
		'type'      => Controls_Manager::SELECT,
		'options'   => array(
			'highlighter' => esc_html__('Highlighter', 'gt3_themes_core'),
		),
		'default'   => 'highlighter',
	)
);

$widget->add_control(
	'marked_word',
	array(
		'label'     => esc_html__('Marked Word', 'gt3_themes_core'),
		'type'      => Controls_Manager::SELECT2,
		'options'   => array(
			'0' => esc_html__('1', 'gt3_themes_core'),
			'1' => esc_html__('2', 'gt3_themes_core'),
			'2' => esc_html__('3', 'gt3_themes_core'),
			'3' => esc_html__('4', 'gt3_themes_core'),
			'4' => esc_html__('5', 'gt3_themes_core'),
			'5' => esc_html__('6', 'gt3_themes_core'),
			'6' => esc_html__('7', 'gt3_themes_core'),
			'7' => esc_html__('8', 'gt3_themes_core'),
			'8' => esc_html__('9', 'gt3_themes_core'),
			'9' => esc_html__('10', 'gt3_themes_core'),
		),
		'default'   => '2',
		'multiple'    => true,
	)
);

$widget->add_control(
	'highlighter_shap',
	array(
		'label'     => esc_html__('Shape', 'gt3_themes_core'),
		'type'      => Controls_Manager::SELECT,
		'options'   => array(
			'circled_type1' => esc_html__('Circled Type 1', 'gt3_themes_core'),
			'circled_type2' => esc_html__('Circled Type 2', 'gt3_themes_core'),
			'circled_type3' => esc_html__('Circled Type 3', 'gt3_themes_core'),
			'circled_type4' => esc_html__('Circled Type 4', 'gt3_themes_core'),
			'curved' => esc_html__('Curved', 'gt3_themes_core'),
			'doubled' => esc_html__('Doubled', 'gt3_themes_core'),
			'zigzagged' => esc_html__('Zigzagged', 'gt3_themes_core'),
			'underlined' => esc_html__('Underlined', 'gt3_themes_core'),
		),
		'default'   => 'underlined',
		'condition'  => array(
			'headlines_style' => 'highlighter',
		),
	)
);

$widget->add_control(
	'infinite_loop',
	array(
		'label'       => esc_html__('Loop', 'gt3_themes_core'),
		'type'        => Controls_Manager::SWITCHER,
		'default'     => 'yes',
		'condition'  => array(
			'headlines_style' => 'highlighter',
		),
	)
);

$widget->add_control(
	'highlighter_duration',
	array(
		'label'       => esc_html__('Duration (ms)', 'gt3_themes_core'),
		'type'        => Controls_Manager::SLIDER,
		'default'     => array(
			'size' => 1200,
			'unit' => 'ms',
		),
		'range'       => array(
			'ms' => array(
				'min'  => 100,
				'max'  => 5000,
				'step' => 100,
			),
		),
		'size_units'  => array( 'ms' ),
		'description' => esc_html__('Enter duration in ms.', 'gt3_themes_core'),
		'label_block' => true,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-animated-headlines .gt3_headline_word' => '--gt3-anim-duration: {{SIZE}}{{UNIT}};',
		),
		'condition'  => array(
			'headlines_style' => 'highlighter',
		),
	)
);

$widget->add_control(
	'highlighter_interval',
	array(
		'label'       => esc_html__('Interval (ms)', 'gt3_themes_core'),
		'type'        => Controls_Manager::SLIDER,
		'default'     => array(
			'size' => 5000,
			'unit' => 'ms',
		),
		'range'       => array(
			'ms' => array(
				'min'  => 1000,
				'max'  => 8000,
				'step' => 100,
			),
		),
		'size_units'  => array( 'ms' ),
		'description' => esc_html__('Enter interval in ms.', 'gt3_themes_core'),
		'label_block' => true,
		'condition'  => array(
			'headlines_style' => 'highlighter',
			'infinite_loop!' => '',
		),
	)
);

$widget->add_control(
	'link',
	array(
		'label' => esc_html__( 'Link', 'gt3_themes_core' ),
		'type' => Controls_Manager::URL,
		'dynamic' => array(
			'active' => true,
		),
		'default' => array(
			'url' => '',
		),
		'separator' => 'before',
	)
);

$widget->add_control(
	'size',
	array(
		'label' => esc_html__( 'Size', 'gt3_themes_core' ),
		'type' => Controls_Manager::SELECT,
		'default' => 'default',
		'options' => array(
			'default' => esc_html__( 'Default', 'gt3_themes_core' ),
			'small' => esc_html__( 'Small', 'gt3_themes_core' ),
			'medium' => esc_html__( 'Medium', 'gt3_themes_core' ),
			'large' => esc_html__( 'Large', 'gt3_themes_core' ),
			'xl' => esc_html__( 'XL', 'gt3_themes_core' ),
			'xxl' => esc_html__( 'XXL', 'gt3_themes_core' ),
		),
	)
);

$widget->add_control(
	'header_size',
	array(
		'label' => esc_html__( 'HTML Tag', 'gt3_themes_core' ),
		'type' => Controls_Manager::SELECT,
		'options' => array(
			'h1' => 'H1',
			'h2' => 'H2',
			'h3' => 'H3',
			'h4' => 'H4',
			'h5' => 'H5',
			'h6' => 'H6',
			'div' => 'div',
			'span' => 'span',
			'p' => 'p',
		),
		'default' => 'h2',
	)
);

$widget->add_responsive_control(
	'align',
	array(
		'label' => esc_html__( 'Alignment', 'gt3_themes_core' ),
		'type' => Controls_Manager::CHOOSE,
		'options' => array(
			'left' => array(
				'title' => esc_html__( 'Left', 'gt3_themes_core' ),
				'icon' => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => esc_html__( 'Center', 'gt3_themes_core' ),
				'icon' => 'eicon-text-align-center',
			),
			'right' => array(
				'title' => esc_html__( 'Right', 'gt3_themes_core' ),
				'icon' => 'eicon-text-align-right',
			),
			'justify' => array(
				'title' => esc_html__( 'Justified', 'gt3_themes_core' ),
				'icon' => 'eicon-text-align-justify',
			),
		),
		'default' => '',
		'selectors' => array(
			'{{WRAPPER}}' => 'text-align: {{VALUE}};',
		),
	)
);

$widget->add_control(
	'view',
	array(
		'label' => esc_html__( 'View', 'gt3_themes_core' ),
		'type' => Controls_Manager::HIDDEN,
		'default' => 'traditional',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'section_headline_style',
	array(
		'label' => esc_html__( 'Headline', 'gt3_themes_core' ),
		'tab' => Controls_Manager::TAB_STYLE,
	)
);

$widget->add_control(
	'title_color',
	array(
		'label' => esc_html__( 'Text Color', 'gt3_themes_core' ),
		'type' => Controls_Manager::COLOR,
		'global' => array(
			'default' => Global_Colors::COLOR_PRIMARY,
		),
		'selectors' => array(
			'{{WRAPPER}} .gt3-headline-title' => 'color: {{VALUE}};',
		),
	)
);

$widget->add_group_control(
	Group_Control_Typography::get_type(),
	array(
		'name' => 'typography',
		'global' => array(
			'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
		),
		'selector' => '{{WRAPPER}} .gt3-headline-title',
	)
);

$widget->add_group_control(
	Group_Control_Text_Shadow::get_type(),
	array(
		'name' => 'text_shadow',
		'selector' => '{{WRAPPER}} .gt3-headline-title',
	)
);

$widget->add_control(
	'blend_mode',
	array(
		'label' => esc_html__( 'Blend Mode', 'gt3_themes_core' ),
		'type' => Controls_Manager::SELECT,
		'options' => array(
			'' => esc_html__( 'Normal', 'gt3_themes_core' ),
			'multiply' => esc_html__( 'Multiply', 'gt3_themes_core' ),
			'screen' => esc_html__( 'Screen', 'gt3_themes_core' ),
			'overlay' => esc_html__( 'Overlay', 'gt3_themes_core' ),
			'darken' => esc_html__( 'Darken', 'gt3_themes_core' ),
			'lighten' => esc_html__( 'Lighten', 'gt3_themes_core' ),
			'color-dodge' => esc_html__( 'Color Dodge', 'gt3_themes_core' ),
			'saturation' => esc_html__( 'Saturation', 'gt3_themes_core' ),
			'color' => esc_html__( 'Color', 'gt3_themes_core' ),
			'difference' => esc_html__( 'Difference', 'gt3_themes_core' ),
			'exclusion' => esc_html__( 'Exclusion', 'gt3_themes_core' ),
			'hue' => esc_html__( 'Hue', 'gt3_themes_core' ),
			'luminosity' => esc_html__( 'Luminosity', 'gt3_themes_core' ),
		),
		'selectors' => array(
			'{{WRAPPER}} .gt3-headline-title' => 'mix-blend-mode: {{VALUE}}',
		),
		'separator' => 'none',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'section_shape_style',
	array(
		'label' => esc_html__( 'Shape', 'gt3_themes_core' ),
		'tab' => Controls_Manager::TAB_STYLE,
		'condition'  => array(
			'headlines_style' => 'highlighter',
		),
	)
);

$widget->add_control(
	'stroke_width',
	array(
		'label'       => esc_html__('Stroke Width', 'gt3_themes_core'),
		'type'        => Controls_Manager::SLIDER,
		'default'     => array(
			'size' => 7,
			'unit' => 'px',
		),
		'range'       => array(
			'px' => array(
				'min'  => 1,
				'max'  => 25,
				'step' => 1,
			),
		),
		'size_units'  => array( 'px' ),
		'description' => esc_html__('Enter stroke width in pixels.', 'gt3_themes_core'),
		'label_block' => true,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-animated-headlines .gt3_headline_word svg path' => 'stroke-width: {{SIZE}}{{UNIT}};',
		),
		'condition'  => array(
			'headlines_style' => 'highlighter',
		),
	)
);

$widget->add_control(
	'stroke_color',
	array(
		'label'       => esc_html__('Stroke Color', 'gt3_themes_core'),
		'type'        => Controls_Manager::COLOR,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-animated-headlines .gt3_headline_word svg path' => 'stroke: {{VALUE}};',
		),
		'label_block' => true,
		'condition'  => array(
			'headlines_style' => 'highlighter',
		),
	)
);

$widget->end_controls_section();
