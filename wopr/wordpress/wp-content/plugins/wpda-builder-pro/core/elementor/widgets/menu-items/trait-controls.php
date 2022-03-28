<?php

namespace WPDaddy\Builder\Elementor\Widgets\Menu_Items;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

trait Trait_Controls {

	protected function init_controls(){

		$this->start_controls_section(
			'menu_section',
			array(
				'label' => esc_html__('General', 'wpda-builder'),
			)
		);

		$this->add_control(
			'select_alignment',
			array(
				'label'   => esc_html__('Select Alignment','wpda-builder'),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'align_left' => esc_html__('Left', 'wpda-builder'),
					'align_center' => esc_html__('Center', 'wpda-builder'),
					'align_right' => esc_html__('Right', 'wpda-builder'),
				),
				'default' => 'align_left'
			)
		);

		$this->add_control(
			'spacing_between_items',
			array(
				'label'       => esc_html__('Spacing between items', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 5,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter spacing in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item' => 'padding-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__('Items', 'wpda-builder'),
				'type'        => Controls_Manager::REPEATER,
				'default'     => array(
					array(
						'menu_item_title'   => '',
						'menu_item_link' => '',
						'menu_item_label' => '',
						'menu_item_icon' => '',
						'custom_colors' => '',
						'custom_label_color' => '',
						'custom_label_bg' => '',
						'custom_icon_color' => '',
					),
				),
				'fields'      => $this->get_repeater_fields(),
				'title_field' => '{{{menu_item_title}}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => esc_html__('Style', 'wpda-builder'),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->start_controls_tabs('style_items');

		$this->start_controls_tab(
			'style_item',
			array(
				'label' => esc_html__('Items','wpda-builder'),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'items_typography',
				'label'    => esc_html__('Items Typography','wpda-builder'),
				'selector' => '{{WRAPPER}} .wpda-builder-menu-items .menu_item',
			)
		);

		$this->add_control(
			'items_color',
			array(
				'label'       => esc_html__('Items Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item' => 'color: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-wpda-builder-menu-items .wpda-builder-menu-items .menu_item a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'items_color_active',
			array(
				'label'       => esc_html__('Active Item Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item.current' => 'color: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-wpda-builder-menu-items .wpda-builder-menu-items .menu_item a:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'style_label',
			array(
				'label' => esc_html__('Labels','wpda-builder'),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'label'    => esc_html__('Labels Typography','wpda-builder'),
				'selector' => '{{WRAPPER}} .wpda-builder-menu-items .menu_item_label',
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'       => esc_html__('Labels Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item_label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'label_bg',
			array(
				'label'       => esc_html__('Labels Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item_label' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'style_icon',
			array(
				'label' => esc_html__('Icons','wpda-builder'),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'       => esc_html__('Icon Size', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 16,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 10,
						'max'  => 30,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item_icon' => 'font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'       => esc_html__('Icon Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item_icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_color_active',
			array(
				'label'       => esc_html__('Active Item Icon Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items .menu_item.current .menu_item_icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

}
