<?php

namespace WPDaddy\Builder\Elementor\Widgets\Login;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

trait Trait_Controls {

	protected function init_controls(){

		$this->start_controls_section(
			'login_section',
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

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'typography',
				'label'     => esc_html__('Typography', 'wpda-builder'),
				'selector'  => '{{WRAPPER}} .wpda-builder-login p',
			)
		);

		$this->add_control(
			'color',
			array(
				'label'       => esc_html__('Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-login'       => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

	}

}
