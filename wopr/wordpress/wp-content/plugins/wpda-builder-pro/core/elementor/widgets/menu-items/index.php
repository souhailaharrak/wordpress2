<?php

namespace WPDaddy\Builder\Elementor\Widgets;

use Elementor\Widget_Base;
use WPDaddy\Builder\Elementor\Basic;
use WPDaddy\Builder\Elementor\Widgets\Menu_Items\Trait_Controls;
use WPDaddy\Builder\Elementor\Widgets\Menu_Items\Trait_Render;
use Elementor\Repeater;
use Elementor\Controls_Manager;

if(!defined('ABSPATH')) {
	exit;
}

class Menu_Items extends Basic {
	use Trait_Controls;
	use Trait_Render;

	public function get_name(){
		return 'wpda-builder-menu-items';
	}

	public function get_title(){
		return esc_html__('Menu Items', 'wpda-builder');
	}

	public function get_icon(){
		return 'eicon-editor-list-ul';
	}

	public function get_repeater_fields(){
		$repeater = new Repeater();

		$repeater->add_control(
			'menu_item_title',
			array(
				'label' => esc_html__('Title', 'wpda-builder'),
				'type'  => Controls_Manager::TEXTAREA,
			)
		);

		$repeater->add_control(
			'menu_item_link',
			array(
				'label' => esc_html__( 'Link', 'wpda-builder' ),
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

		$repeater->add_control(
			'menu_item_label',
			array(
				'label' => esc_html__('Label', 'wpda-builder'),
				'type'  => Controls_Manager::TEXT,
			)
		);

		$repeater->add_control(
			'menu_item_icon',
			array(
				'label'     => esc_html__('Icon', 'wpda-builder'),
				'type'      => Controls_Manager::ICON,
			)
		);

		$repeater->add_control(
			'custom_colors',
			array(
				'label' => esc_html__('Customize Colors?', 'wpda-builder'),
				'type'  => Controls_Manager::SWITCHER,
			)
		);


		$repeater->add_control(
			'custom_label_color',
			array(
				'label'       => esc_html__('Label Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items {{CURRENT_ITEM}} .menu_item_label' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'custom_colors' => 'yes'
				),
			)
		);

		$repeater->add_control(
			'custom_label_bg',
			array(
				'label'       => esc_html__('Label Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items {{CURRENT_ITEM}} .menu_item_label' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'custom_colors' => 'yes'
				),
			)
		);

		$repeater->add_control(
			'custom_icon_color',
			array(
				'label'       => esc_html__('Icon Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-menu-items {{CURRENT_ITEM}} .menu_item_icon' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'custom_colors' => 'yes',
					'menu_item_icon!' => ''
				),
			)
		);

		$repeater->add_control(
			'image_hover_preview',
			array(
				'label'       => esc_html__('Image', 'wpda-builder'),
				'type'        => Controls_Manager::MEDIA,
			)
		);

		return $repeater->get_controls();
	}

}

