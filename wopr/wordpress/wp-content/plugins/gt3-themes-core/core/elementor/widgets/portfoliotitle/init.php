<?php

namespace ElementorModal\Widgets;

use Elementor\Widget_Base;
use Elementor\Utils;
use WP_Query;
use Elementor\Modules;
use Elementor\GT3_Core_Elementor_Control_Query;

if(!defined('ABSPATH')) {
	exit;
}

if(!class_exists('ElementorModal\Widgets\GT3_Core_Elementor_Widget_PortfolioTitle')) {
	class GT3_Core_Elementor_Widget_PortfolioTitle extends \ElementorModal\Widgets\GT3_Core_Widget_Base {

		public function get_name(){
			return 'gt3-core-portfoliotitle';
		}

		public function get_title(){
			return esc_html__('Portfolio Title', 'gt3_themes_core');
		}

		public function get_icon(){
			return 'gt3-core-elementor-icon eicon-archive-title';
		}

		public $POST_TYPE = 'portfolio';
		public $TAXONOMY = 'portfolio_category';

	}
}











