<?php

namespace WPDaddy\Builder\Elementor\Widgets\Menu;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

trait Trait_Controls {

	protected function init_controls(){

		$this->start_controls_section(
			'menu_section',
			array(
				'label' => esc_html__('General', 'wpda-builder'),
			)
		);

		$this->add_control(
			'menu_select',
			array(
				'label'   => esc_html__('Select Menu', 'wpda-builder'),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_menu_list(),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'       => __('Alignment', 'wpda-builder'),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'left'   => array(
						'title' => __('Left', 'wpda-builder'),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __('Center', 'wpda-builder'),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __('Right', 'wpda-builder'),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors'   => array(
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				),
				'description' => esc_html__('Available only if the element is not inline. Otherwise column alignment applies.', 'wpda-builder'),
			)
		);

		$this->add_control(
			'custom_settings',
			array(
				'label'   => esc_html__('Custom Settings', 'wpda-builder'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'menu_typography',
				'label'     => esc_html__('Menu Typography', 'wpda-builder'),
				'selector'  => '{{WRAPPER}} nav > ul > li > a',
				'condition' => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'menu_text_color',
			array(
				'label'       => esc_html__('Menu Text Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} nav > ul > li > a' => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'menu_text_color_active',
			array(
				'label'       => esc_html__('Menu Text Color (Active and Hover)', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} nav > ul > li > a:hover'                 => 'color: {{VALUE}};',
					'{{WRAPPER}} nav > ul > li.current-menu-item > a'     => 'color: {{VALUE}};',
					'{{WRAPPER}} nav > ul > li.current-menu-ancestor > a' => 'color: {{VALUE}};',
					'{{WRAPPER}} nav > ul > li.current-menu-parent > a'   => 'color: {{VALUE}};',
					'{{WRAPPER}} nav > ul > li:hover > a'                 => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'spacing_between_items',
			array(
				'label'       => esc_html__('Spacing Between Menu Items', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 15,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 30,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter spacing in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} nav > ul > li' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: calc({{SIZE}}{{UNIT}} - 5px);',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'menu_height',
			array(
				'label'       => esc_html__('Menu Height', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(),
				'range'       => array(
					'px' => array(
						'min'  => 35,
						'max'  => 150,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter spacing in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} nav > ul > li > a' => 'height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.mobile_menu_active nav > ul > li > a' => 'height: auto; line-height: inherit;',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'submenu_typography',
				'label'     => esc_html__('Sub Menu Typography', 'wpda-builder'),
				'selector'  => '{{WRAPPER}} nav ul.sub-menu li a',
				'condition' => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'submenu_bg',
			array(
				'label'       => esc_html__('Sub Menu Background', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} nav ul.sub-menu' => 'background: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'submenu_text_color',
			array(
				'label'       => esc_html__('Sub Menu Text Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} nav ul.sub-menu li a' => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'submenu_text_color_active',
			array(
				'label'       => esc_html__('Sub Menu Text Color (Active and Hover)', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} nav ul.sub-menu li > a:hover'                 => 'color: {{VALUE}};',
					'{{WRAPPER}} nav ul.sub-menu li:hover > a'                 => 'color: {{VALUE}};',
					'{{WRAPPER}} nav ul.sub-menu li.current-menu-item > a'     => 'color: {{VALUE}};',
					'{{WRAPPER}} nav ul.sub-menu li.current-menu-ancestor > a' => 'color: {{VALUE}};',
					'{{WRAPPER}} nav ul.sub-menu li.current-menu-parent > a'   => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'submenu_width',
			array(
				'label'       => esc_html__('Sub Menu Width', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 200,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 180,
						'max'  => 250,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter width in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} nav ul.sub-menu' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'spacing_submenu',
			array(
				'label'       => esc_html__('Spacing Above the Sub Menu', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 0,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter spacing in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .sub-menu' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} nav > ul > li:after' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'border_radius_submenu',
			array(
				'label'       => esc_html__('Border Radius of the Sub Menu', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 5,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 30,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter border-radius in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .sub-menu' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'       => esc_html__('Sub Menu Box Shadow', 'wpda-builder'),
				'name'     => 'submenu_box_shadow',
				'selector' => '{{WRAPPER}} ul.sub-menu',
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'custom_settings_mobile',
			array(
				'label'   => esc_html__('Custom Mobile Settings', 'wpda-builder'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'menu_text_color_mobile',
			array(
				'label'       => esc_html__('Menu Text Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}}.mobile_menu_active nav > ul > li > a' => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings_mobile!' => '',
				),
			)
		);

		$this->add_control(
			'menu_text_color_active_mobile',
			array(
				'label'       => esc_html__('Menu Text Color (Active and Hover)', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}}.mobile_menu_active nav > ul > li > a:hover'                 => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav > ul > li.current-menu-item > a'     => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav > ul > li.current-menu-ancestor > a' => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav > ul > li.current-menu-parent > a'   => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav > ul > li:hover > a'                 => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings_mobile!' => '',
				),
			)
		);

		$this->add_control(
			'mobile_menu_bg',
			array(
				'label'       => esc_html__('Menu Background', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}}.mobile_menu_active .wpda-navbar-collapse' => 'background: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings_mobile!' => '',
				),
			)
		);

		$this->add_control(
			'submenu_text_color_mobile',
			array(
				'label'       => esc_html__('Sub Menu Text Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}}.mobile_menu_active nav ul.sub-menu li a' => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings_mobile!' => '',
				),
			)
		);

		$this->add_control(
			'submenu_text_color_active_mobile',
			array(
				'label'       => esc_html__('Sub Menu Text Color (Active and Hover)', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}}.mobile_menu_active nav ul.sub-menu li > a:hover'                 => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav ul.sub-menu li:hover > a'                 => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav ul.sub-menu li.current-menu-item > a'     => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav ul.sub-menu li.current-menu-ancestor > a' => 'color: {{VALUE}};',
					'{{WRAPPER}}.mobile_menu_active nav ul.sub-menu li.current-menu-parent > a'   => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings_mobile!' => '',
				),
			)
		);

		$this->add_control(
			'mobile_icon_color',
			array(
				'label'       => esc_html__('Mobile Icon Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-mobile-navigation-toggle' => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings_mobile!' => '',
				),
			)
		);

		$this->add_control(
			'mobile_icon_color_active',
			array(
				'label'       => esc_html__('Mobile Icon Color (Active)', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}}.mobile_menu_active .wpda-mobile-navigation-toggle' => 'color: {{VALUE}};',
				),
				'condition'   => array(
					'custom_settings_mobile!' => '',
				),
			)
		);

		$this->end_controls_section();

	}

}
