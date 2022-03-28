<?php

namespace WPDaddy\Builder\Elementor\Widgets;

use Elementor\Widget_Base;
use WPDaddy\Builder\Elementor\Basic;
use WPDaddy\Builder\Elementor\Widgets\Search\Trait_Controls;
use WPDaddy\Builder\Elementor\Widgets\Search\Trait_Render;

if(!defined('ABSPATH')) {
	exit;
}

class Search extends Basic {
	use Trait_Controls;
	use Trait_Render;

	public function get_name(){
		return 'wpda-builder-search';
	}

	public function get_title(){
		return esc_html__('Search', 'wpda-builder');
	}

	public function get_icon(){
		return 'eicon-site-search';
	}

}

