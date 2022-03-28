<?php

namespace WPDaddy\Builder\Elementor\Widgets\Logo;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

trait Trait_Controls {

	protected function init_controls(){

		$this->start_controls_section(
			'logo_section',
			array(
				'label' => esc_html__('General', 'wpda-builder'),
			)
		);

		$this->add_control(
			'header_logo',
			array(
				'label'   => esc_html__('Header Logo', 'wpda-builder'),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => '',
				),
			)
		);

		$this->add_control(
			'logo_height_custom',
			array(
				'label'     => esc_html__('Enable Logo Height', 'wpda-builder'),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => array(
					'header_logo[url]!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'logo_height',
			array(
				'label'       => esc_html__('Set Logo Height', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 40,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 20,
						'max'  => 150,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter logo height in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-logo_container img' => 'height: {{SIZE}}{{UNIT}}; max-height: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'logo_height_custom!' => '',
					'header_logo[url]!'   => '',
				),
			)
		);

		$this->add_control(
			'logo_custom_link',
			array(
				'label'     => esc_html__('Enable Custom Link', 'wpda-builder'),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
			)
		);

		$this->add_control(
			'logo_custom_link_url',
			array(
				'label'       => esc_html__('Custom Logo Link', 'wpda-builder'),
				'type'        => Controls_Manager::URL,
				'description' => esc_html__('Add Link to Logo.', 'wpda-builder'),
				'default'     => array(
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				),
				'condition' => array(
					'logo_custom_link!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'header_logo_typography',
				'label'     => esc_html__('Logo Typography', 'wpda-builder'),
				'selector'  => '{{WRAPPER}} .wpda-builder-logo_container .wpda-builder-site_title',
				'condition' => array(
					'header_logo[url]' => '',
				),
			)
		);

		$this->add_control(
			'header_logo_text_color',
			array(
				'label'       => esc_html__('Logo Text Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-logo_container .wpda-builder-site_title' => 'color: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'header_logo[url]' => '',
				),
			)
		);

		$this->add_control(
			'logo_sticky',
			array(
				'label'     => esc_html__('Sticky Logo', 'wpda-builder'),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => '',
				),
				'condition' => array(
					'header_logo[url]!' => '',
				),
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

		$this->end_controls_section();

	}

}
