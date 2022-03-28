<?php

namespace WPDaddy\Builder\Elementor\Widgets;

use Elementor\Widget_Base;
use WPDaddy\Builder\Elementor\Basic;
use WPDaddy\Builder\Elementor\Widgets\Logo\Trait_Controls;
use WPDaddy\Builder\Elementor\Widgets\Logo\Trait_Render;

if(!defined('ABSPATH')) {
	exit;
}

class Logo extends Basic {
	use Trait_Controls;
	use Trait_Render;

	public function get_name(){
		return 'wpda-header-logo';
	}

	public function get_title(){
		return esc_html__('Logo', 'wpda-builder');
	}

	public function get_icon(){
		return 'eicon-logo';
	}

}

