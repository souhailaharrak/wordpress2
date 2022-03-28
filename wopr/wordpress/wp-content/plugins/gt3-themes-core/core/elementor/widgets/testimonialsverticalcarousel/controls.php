<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_TestimonialsVerticalCarousel $widget */

$widget->start_controls_section(
	'general',
	array(
		'label' => esc_html__('General', 'gt3_themes_core'),
	)
);

$widget->add_control(
	'nav',
	array(
		'label'   => esc_html__('Navigation', 'gt3_themes_core'),
		'type'    => Controls_Manager::SELECT,
		'options' => array(
			'none'   => esc_html__('None', 'gt3_themes_core'),
			'arrows' => esc_html__('Arrows', 'gt3_themes_core'),
			'dots'   => esc_html__('Dots', 'gt3_themes_core'),
		),
		'default' => 'arrows',
	)
);

$widget->add_control(
	'autoplay',
	array(
		'label' => esc_html__('Autoplay', 'gt3_themes_core'),
		'type'  => Controls_Manager::SWITCHER,
	)
);

$widget->add_control(
	'autoplay_time',
	array(
		'label'     => esc_html__('Autoplay time', 'gt3_themes_core'),
		'type'      => Controls_Manager::NUMBER,
		'default'   => 4000,
		'min'       => '0',
		'step'      => 100,
		'condition' => array(
			'autoplay' => 'yes'
		),
	)
);

$widget->add_control(
	'author_position',
	array(
		'label'   => esc_html__('Author Info Position', 'gt3_themes_core'),
		'type'    => Controls_Manager::SELECT,
		'options' => array(
			'before'   => esc_html__('Before Content', 'gt3_themes_core'),
			'after'   => esc_html__('After Content', 'gt3_themes_core'),
		),
		'default' => 'after',
	)
);

$widget->add_control(
	'round_imgs',
	array(
		'label' => esc_html__('Circular Author Image?', 'gt3_themes_core'),
		'type'  => Controls_Manager::SWITCHER,
	)
);

$widget->add_control(
	'item_align',
	array(
		'label'   => esc_html__('Alignment', 'gt3_themes_core'),
		'type'    => Controls_Manager::CHOOSE,
		'options' => array(
			'left'   => array(
				'title' => esc_html__('Left', 'gt3_themes_core'),
				'icon'  => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => esc_html__('Center', 'gt3_themes_core'),
				'icon'  => 'eicon-text-align-center',
			),
			'right'  => array(
				'title' => esc_html__('Right', 'gt3_themes_core'),
				'icon'  => 'eicon-text-align-right',
			),
		),
		'label_block' => false,
		'style_transfer' => true,
		'prefix_class' => 'gt3-testimonials-aligment-',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'items',
	array(
		'label' => esc_html__('Items', 'gt3_themes_core'),
		'tab'   => Controls_Manager::TAB_CONTENT,
	)
);

$widget->add_control(
	'items',
	array(
		'label'       => esc_html__('Items', 'gt3_themes_core'),
		'type'        => Controls_Manager::REPEATER,
		'default'     => array(),
		'fields'      => $widget->get_repeater_fields(),
		'title_field' => '{{{ name }}}',
	)
);

$widget->end_controls_section();


$widget->start_controls_section(
	'section_style_testimonial_image',
	array(
		'label' => __( 'Image', 'gt3_themes_core' ),
		'tab' => Controls_Manager::TAB_STYLE,
	)
);
$widget->add_control(
	'image_size',
	array(
		'label' => __( 'Image Size', 'gt3_themes_core' ),
		'type' => Controls_Manager::SLIDER,
		'size_units' => array( 'px' ),
		'range' => array(
			'px' => array(
				'min' => 20,
				'max' => 200,
			),
		),
		'default' => array(
			'size' => 60
		),
		'selectors' => array(
			'{{WRAPPER}} .testimonials_author_wrapper .testimonials_photo img' => 'width: {{SIZE}}{{UNIT}} !important;height: {{SIZE}}{{UNIT}} !important;'

		),
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'section_style_testimonial_content',
	array(
		'label' => __( 'Content', 'gt3_themes_core' ),
		'tab' => Controls_Manager::TAB_STYLE,
	)
);

$widget->add_control(
	'color_title',
	array(
		'label'     => esc_html__('Text Color', 'gt3_themes_core'),
		'type'      => Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} .testimonials-text' => 'color: {{VALUE}};',
		)
	)
);

$widget->add_group_control(
	Group_Control_Typography::get_type(),
	array(
		'name'     => 'title_typography',
		'selector' => '{{WRAPPER}} .testimonials-text, {{WRAPPER}} .testimonials-text p'
	)
);

$widget->add_control(
	'item_wrap_bg',
	array(
		'label'   => esc_html__('Item Wrapper Background Color','gt3_themes_core'),
		'type'    => Controls_Manager::COLOR,
		'selectors'   => array(
			'{{WRAPPER}}.elementor-widget-gt3-core-testimonialsverticalcarousel .testimonial_item_wrapper' => 'background-color: {{VALUE}};',
		),
		'prefix_class' => 'has_items_bg color_',
	)
);

$widget->end_controls_section();

$widget->start_controls_section(
	'section_style_testimonial_author',
	array(
		'label' => __( 'Author', 'gt3_themes_core' ),
		'tab' => Controls_Manager::TAB_STYLE,
	)
);

$widget->add_control(
	'color_author',
	array(
		'label'     => esc_html__('Color Author', 'gt3_themes_core'),
		'type'      => Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} .testimonials_author_wrapper' => 'color: {{VALUE}};',
		)
	)
);

$widget->add_control(
	'color_author_position',
	array(
		'label'     => esc_html__('Color Author Position', 'gt3_themes_core'),
		'type'      => Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} .testimonials_author_wrapper .testimonials-sub_name' => 'color: {{VALUE}};',
		)
	)
);

$widget->add_group_control(
	Group_Control_Typography::get_type(),
	array(
		'name'     => 'author_typography',
		'selector' => '{{WRAPPER}} .testimonials_author_wrapper',
	)
);

$widget->end_controls_section();
$widget->start_controls_section(
	'section_style_testimonial_arrow',
	array(
		'label' => __( 'Arrow', 'gt3_themes_core' ),
		'tab' => Controls_Manager::TAB_STYLE,
		'condition' => array(
			'nav!' => 'none',
		),
	)
);

$widget->add_control(
	'color_slider_arrow',
	array(
		'label'     => esc_html__('Slider Navigation Color', 'gt3_themes_core'),
		'type'      => Controls_Manager::COLOR,
		'selectors' => array(
			'{{WRAPPER}} .slick-dots' => 'color: {{VALUE}};',
			'{{WRAPPER}} .slick-arrow' => 'color: {{VALUE}};'
		),
		'condition' => array(
			'nav!' => 'none',
		),
	)
);

$widget->end_controls_section();
