<?php

namespace WPDaddy\Builder\Elementor\Widgets\Delimiter;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

trait Trait_Controls {

	protected function init_controls(){
		$this->start_controls_section(
			'delimiter_section',
			array(
				'label' => esc_html__('General', 'wpda-builder'),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label' => __( 'Alignment', 'wpda-builder' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => array(
					'left' => array(
						'title' => __( 'Left', 'wpda-builder' ),
						'icon' => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'wpda-builder' ),
						'icon' => 'eicon-text-align-center',
					),
					'right' => array(
						'title' => __( 'Right', 'wpda-builder' ),
						'icon' => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				),
				'description' => esc_html__('Available only if the element is not inline. Otherwise column alignment applies.', 'wpda-builder'),
			)
		);

		$this->add_control(
			'color',
			array(
				'label'       => esc_html__('Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-delimiter' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent:after' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent_tablet:after' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent_mobile:after' => 'color: {{VALUE}};',
				),
				'label_block' => true,
			)
		);

		$this->add_control(
			'width',
			array(
				'label'       => esc_html__('Width', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 1,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter width in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-delimiter' => 'border-left-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent:after' => 'margin-left: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent_tablet:after' => 'margin-left: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent_mobile:after' => 'margin-left: -{{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'       => esc_html__('Height', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array(
					'px',
					'em',
					'%'
				),
				'default' => [
					'unit' => 'px',
					'size' => '35',
				],
				'tablet_default' => [
					'unit' => 'px',
					'size' => '35',
				],
				'mobile_default' => [
					'unit' => 'px',
					'size' => '35',
				],
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-delimiter' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent:after' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent_tablet:after' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpda-builder-delimiter.unit_percent_mobile:after' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();


	}

}
