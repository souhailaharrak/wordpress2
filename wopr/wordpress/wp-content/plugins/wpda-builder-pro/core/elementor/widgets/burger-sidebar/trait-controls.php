<?php

namespace WPDaddy\Builder\Elementor\Widgets\Burger_Sidebar;

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
			'sidebar',
			array(
				'label'   => esc_html__('Select Sidebar', 'wpda-builder'),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_sidebars_list(),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

	}

}
