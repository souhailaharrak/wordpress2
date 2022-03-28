<?php

namespace WPDaddy\Builder\Elementor\Widgets;

use Elementor\Widget_Base;
use WPDaddy\Builder\Elementor\Basic;
use WPDaddy\Builder\Elementor\Widgets\Menu\Trait_Controls;
use WPDaddy\Builder\Elementor\Widgets\Menu\Trait_Render;

if(!defined('ABSPATH')) {
	exit;
}

class Menu extends Basic {
	use Trait_Controls;
	use Trait_Render;

	public function get_name(){
		return 'wpda-builder-menu';
	}

	public function get_title(){
		return esc_html__('Menu', 'wpda-builder');
	}

	public function get_icon(){
		return 'eicon-nav-menu';
	}

	private function get_menu_list(){
		$menus     = wp_get_nav_menus();
		$menu_list = array();
		foreach($menus as $menu => $menu_obj) {
			$menu_list[$menu_obj->slug] = $menu_obj->name;
		}

		return $menu_list;
	}

}

