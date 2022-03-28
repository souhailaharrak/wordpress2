<?php

namespace WPDaddy\Builder\Elementor\Widgets\Search;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

trait Trait_Controls {

	protected function init_controls(){

		$this->start_controls_section(
			'search_section',
			array(
				'label' => esc_html__('General', 'wpda-builder'),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'        => __('Alignment', 'wpda-builder'),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
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
				'selectors'    => array(
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				),
				'prefix_class' => 'alignment-',
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

		$this->add_control(
			'icon_color',
			array(
				'label'       => esc_html__('Icon Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-search_icon' => 'color: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'       => esc_html__('Icon Font Size', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 25,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 10,
						'max'  => 50,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter spacing in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-search_icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'search_width',
			array(
				'label'       => esc_html__('Field Width (Open State)', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 200,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 180,
						'max'  => 280,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter width in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-search_inner'                  => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.alignment-center .wpda-search_inner' => 'margin-left: calc(-0.9*{{SIZE}}{{UNIT}} / 2);',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'spacing_field',
			array(
				'label'       => esc_html__('Spacing Above the Field', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 10,
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
					'{{WRAPPER}} .wpda-search_inner' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'field_bg',
			array(
				'label'       => esc_html__('Field Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-search_inner' => 'background: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'field_color',
			array(
				'label'       => esc_html__('Field Text Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-search_inner form input[type="text"]'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpda-search_inner form input[type="search"]' => 'color: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'       => esc_html__('Search Icon Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-search_inner form:after' => 'color: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->end_controls_section();

	}

}
