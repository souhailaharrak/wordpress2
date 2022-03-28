<?php

namespace WPDaddy\Builder\Elementor\Widgets\Cart;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

trait Trait_Controls {

	protected function init_controls(){

		$this->start_controls_section(
			'cart_section',
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
					'{{WRAPPER}} .wpda_cart-icon'       => 'color: {{VALUE}};',
					'{{WRAPPER}} .wpda_cart-icon:hover' => 'color: {{VALUE}} !important;',
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
					'size' => 19,
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
					'{{WRAPPER}} .wpda_cart-icon i.wpda_cart-count:before' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'icon_count_bg',
			array(
				'label'       => esc_html__('Icon Count Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda_cart-icon i.wpda_cart-count span:not(:empty)' => 'background: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'icon_count_color',
			array(
				'label'       => esc_html__('Icon Count Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda_cart-icon i.wpda_cart-count span:not(:empty)' => 'color: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'cart_width',
			array(
				'label'       => esc_html__('Cart Width', 'wpda-builder'),
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'size' => 250,
					'unit' => 'px',
				),
				'range'       => array(
					'px' => array(
						'min'  => 220,
						'max'  => 280,
						'step' => 1,
					),
				),
				'size_units'  => array( 'px' ),
				'description' => esc_html__('Enter width in pixels.', 'wpda-builder'),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-cart .wpda-cart-inner'                  => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.alignment-center .wpda-builder-cart .wpda-cart-inner' => 'margin-left: calc(-0.9*{{SIZE}}{{UNIT}} / 2);',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'spacing_cart',
			array(
				'label'       => esc_html__('Spacing Above the Cart', 'wpda-builder'),
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
					'{{WRAPPER}} .wpda-cart-inner'       => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wpda-cart-inner:after' => 'top: -{{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'cart_bg',
			array(
				'label'       => esc_html__('Cart Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-cart-inner' => 'background: {{VALUE}};',
					'{{WRAPPER}} .wpda-cart-inner:after' => 'border-color: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'cart_color',
			array(
				'label'       => esc_html__('Cart Text Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-cart-container' => 'color: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'remove_color',
			array(
				'label'       => esc_html__('Remove Icon Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} a.remove' => 'color: {{VALUE}} !important;',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'cart_btn_bg',
			array(
				'label'       => esc_html__('Buttons Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-cart .wpda-cart-inner .wpda-cart-container p.woocommerce-mini-cart__buttons a' => 'background: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'cart_btn_color',
			array(
				'label'       => esc_html__('Buttons Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-cart .wpda-cart-inner .wpda-cart-container p.woocommerce-mini-cart__buttons a' => 'color: {{VALUE}} !important;',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'custom_checkout',
			array(
				'label'     => esc_html__('Custom Checkout Button', 'wpda-builder'),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => array(
					'custom_settings!' => '',
				),
			)
		);

		$this->add_control(
			'cart_checkout_bg',
			array(
				'label'       => esc_html__('Checkout Button Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-cart .wpda-cart-inner .wpda-cart-container p.woocommerce-mini-cart__buttons a.checkout' => 'background: {{VALUE}};',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
					'custom_checkout!' => '',
				),
			)
		);

		$this->add_control(
			'cart_checkout_color',
			array(
				'label'       => esc_html__('Checkout Button Background Color', 'wpda-builder'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} .wpda-builder-cart .wpda-cart-inner .wpda-cart-container p.woocommerce-mini-cart__buttons a.checkout' => 'color: {{VALUE}} !important;',
				),
				'label_block' => true,
				'condition'   => array(
					'custom_settings!' => '',
					'custom_checkout!' => '',
				),
			)
		);

		$this->end_controls_section();

	}

}
