<?php

namespace WPDaddy\Builder\Elementor\Modify;

use Elementor\Controls_Manager;
use Elementor\Element_Section;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Plugin;
use Elementor\Shapes;
use WPDaddy\Builder\Elementor;
use WPDaddy\Builder\Library\Basic;
use WPDaddy\Builder\Library\Header as Header_Library;

class Section {
	const type = 'section';

	private static $instance = null;

	public static function instance() {
		if (!self::$instance instanceof self) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action('elementor/frontend/section/before_render', array($this, 'before_render'));
		add_action('elementor/elements/elements_registered', array($this, 'extend_controls'));
	}

	/** @param Element_Section $section */
	public function before_render($section) {
		$document = \Elementor\Plugin::instance()->documents->get_current();
		if (!($document instanceof Header_Library)) {
			return;
		}
		$devices = ['desktop', 'tablet', 'mobile'];
		$section->add_render_attribute('_wrapper', 'class', ["wpda_builder_section"]);

		forEach ($devices as $key) {
			$settingsKey = ($key === 'desktop' ? '' : "_${key}");

			$name      = 'sticky_section';
			$isEnabled = $section->get_settings("${name}${settingsKey}");
			if (!!$isEnabled) {
				$section->add_render_attribute('_wrapper', 'class', ["${name}_${key}"]);
			}

			$name      = 'section_over_bg';
			$isEnabled = $section->get_settings("${name}${settingsKey}");
			if (!!$isEnabled) {
				$section->add_render_attribute('_wrapper', 'class', ["${name}_${key}"]);
			}
		};
	}

	public function extend_controls() {
		$is_edit_mode = Plugin::$instance->editor->is_edit_mode();
		if ($is_edit_mode && !(\Elementor\Plugin::instance()->documents->get_current() instanceof Header_Library)) {
			return;
		}
		/** @var Element_Section $section */
		$section = Plugin::instance()->elements_manager->get_element_types('section');

		$section->start_injection(
			array(
				'type' => 'control',
				'at'   => 'after',
				'of'   => 'html_tag'
			)
		);

		$section->start_controls_section(
			'wpda_settings',
			[
				'label' => __('WPDaddy Settings', 'wpda-builder'),
				'tab'   => Elementor::TAB_WPDA_SETTINGS,
			]
		);

		$section->add_control(
			'wpda_settings_en', array(
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array('wpda_show' => 'never'),
				'default'   => 'yes',
			)
		);

		$section->add_responsive_control(
			'sticky_section',
			array(
				'label' => __('Sticky', 'wpda-builder'),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$section->end_controls_section();

		// Section background
		$section->start_controls_section(
			'sticky_section_background',
			[
				'label' => __('Background', 'wpda-builder'),
				'tab'   => Elementor::TAB_WPDA_SETTINGS,
			]
		);

		$section->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'sticky_background',
				'types'    => ['classic', 'gradient', 'video', 'slideshow'],
				'selector' => '{{WRAPPER}}.sticky_enabled:not(.elementor-motion-effects-element-type-background), {{WRAPPER}}.sticky_enabled > .elementor-motion-effects-container > .elementor-motion-effects-layer',
			]
		);

		$section->end_controls_section();

		// Section border
		$section->start_controls_section(
			'sticky_section_border',
			[
				'label' => __('Border', 'wpda-builder'),
				'tab'   => Elementor::TAB_WPDA_SETTINGS,
			]
		);

		$section->start_controls_tabs('sticky_tabs_border');

		$section->start_controls_tab(
			'sticky_tab_border_normal',
			[
				'label' => __('Normal', 'wpda-builder'),
			]
		);

		$section->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'sticky_border',
				'selector' => '{{WRAPPER}}.sticky_enabled'
			]
		);

		$section->add_responsive_control(
			'sticky_border_radius',
			[
				'label'      => __('Border Radius', 'wpda-builder'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}}.sticky_enabled, {{WRAPPER}}.sticky_enabled > .elementor-background-overlay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$section->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'sticky_box_shadow',
				'selector' => '{{WRAPPER}}.sticky_enabled'
			]
		);

		$section->end_controls_tab();

		$section->start_controls_tab(
			'sticky_tab_border_hover',
			[
				'label' => __('Hover', 'wpda-builder'),
			]
		);

		$section->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'sticky_border_hover',
				'selector' => '{{WRAPPER}}.sticky_enabled:hover',
			]
		);

		$section->add_responsive_control(
			'sticky_border_radius_hover',
			[
				'label'      => __('Border Radius', 'wpda-builder'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}}.sticky_enabled:hover, {{WRAPPER}}.sticky_enabled:hover > .elementor-background-overlay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$section->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'sticky_box_shadow_hover',
				'selector' => '{{WRAPPER}}.sticky_enabled:hover',
			]
		);

		$section->add_control(
			'sticky_border_hover_transition',
			[
				'label'      => __('Transition Duration', 'wpda-builder'),
				'type'       => Controls_Manager::SLIDER,
				'separator'  => 'before',
				'default'    => [
					'size' => 0.3,
				],
				'range'      => [
					'px' => [
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'sticky_background_background',
							'operator' => '!==',
							'value'    => '',
						],
						[
							'name'     => 'sticky_border_border',
							'operator' => '!==',
							'value'    => '',
						],
					],
				],
				'selectors'  => [
					'{{WRAPPER}}.sticky_enabled'                                 => 'transition: background {{background_hover_transition.SIZE}}s, border {{SIZE}}s, border-radius {{SIZE}}s, box-shadow {{SIZE}}s',
					'{{WRAPPER}}.sticky_enabled > .elementor-background-overlay' => 'transition: background {{background_overlay_hover_transition.SIZE}}s, border-radius {{SIZE}}s, opacity {{background_overlay_hover_transition.SIZE}}s',
				],
			]
		);

		$section->end_controls_tab();

		$section->end_controls_tabs();

		$section->end_controls_section();

		// Section Typography
		$section->start_controls_section(
			'sticky_section_typo',
			[
				'label' => __('Typography', 'wpda-builder'),
				'tab'   => Elementor::TAB_WPDA_SETTINGS,
			]
		);

		$section->add_control(
			'sticky_heading_color',
			[
				'label'     => __('Heading Color', 'wpda-builder'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}.sticky_enabled .elementor-heading-title' => 'color: {{VALUE}};',
				],
				'separator' => 'none',
			]
		);

		$section->add_control(
			'sticky_color_text',
			[
				'label'     => __('Text Color', 'wpda-builder'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}.sticky_enabled'                                                   => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .wpda-builder-delimiter'                           => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .wpda-builder-delimiter.unit_percent:after'        => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .wpda-builder-delimiter.unit_percent_tablet:after' => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .wpda-builder-delimiter.unit_percent_mobile:after' => 'color: {{VALUE}};',
				],
			]
		);

		$section->add_control(
			'sticky_color_link',
			[
				'label'     => __('Link Color', 'wpda-builder'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}.sticky_enabled a'                                                                                           => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled a .wpda-builder-site_title'                                                                  => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-menu:not(.mobile_menu_active) nav > ul > li > a'              => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-menu:not(.mobile_menu_active) .wpda-mobile-navigation-toggle' => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-search .wpda-search_icon'                                     => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-cart .wpda_cart-icon'                                         => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-cart .wpda_cart-icon:hover'                                   => 'color: {{VALUE}} !important;',
				],
			]
		);

		$section->add_control(
			'sticky_color_link_hover',
			[
				'label'     => __('Link Hover Color', 'wpda-builder'),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}.sticky_enabled a:hover'                                                                                              => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled a:hover .wpda-builder-site_title'                                                                     => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-menu:not(.mobile_menu_active) nav > ul > li > a:hover'                 => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-menu:not(.mobile_menu_active) nav > ul > li.current-menu-item > a'     => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-menu:not(.mobile_menu_active) nav > ul > li.current-menu-ancestor > a' => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-menu:not(.mobile_menu_active) nav > ul > li.current-menu-parent > a'   => 'color: {{VALUE}};',
					'{{WRAPPER}}.sticky_enabled .elementor-widget-wpda-builder-menu:not(.mobile_menu_active) nav > ul > li:hover > a'                 => 'color: {{VALUE}};',
				],
			]
		);

		$section->add_control(
			'sticky_text_align',
			[
				'label'     => __('Text Align', 'wpda-builder'),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __('Left', 'wpda-builder'),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __('Center', 'wpda-builder'),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __('Right', 'wpda-builder'),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}}.sticky_enabled > .elementor-container' => 'text-align: {{VALUE}};',
				],
			]
		);

		$section->end_controls_section();

		$section->end_injection();

		$control                                         = $section->get_controls('color_link');
		$selectors                                       = $control['selectors'];
		$selectors['{{WRAPPER}} a.wpda_cart-icon:hover'] = 'color: {{VALUE}}';
		$section->update_control(
			'color_link', array(
				'selectors' => $selectors
			)
		);
	}
}
