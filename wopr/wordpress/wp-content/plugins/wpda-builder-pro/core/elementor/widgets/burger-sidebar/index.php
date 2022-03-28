<?php

namespace WPDaddy\Builder\Elementor\Widgets;

use Elementor\Widget_Base;
use WPDaddy\Builder\Elementor\Basic;
use WPDaddy\Builder\Elementor\Widgets\Burger_Sidebar\Trait_Controls;
use WPDaddy\Builder\Elementor\Widgets\Burger_Sidebar\Trait_Render;

if(!defined('ABSPATH')) {
	exit;
}

class Burger_Sidebar extends Basic {
	use Trait_Controls;
	use Trait_Render;

	public function get_name(){
		return 'wpda-builder-burger-sidebar';
	}

	public function get_title(){
		return esc_html__('Burger Sidebar', 'wpda-builder');
	}

	public function get_icon(){
		return 'eicon-sidebar';
	}

	private function get_sidebars_list(){
		global $wp_registered_sidebars;
		$out = array('' => '' );
		if ( empty( $wp_registered_sidebars ) )
			return $out;
		foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar) :
			$out[$sidebar_id] = $sidebar['name'];
		endforeach;

		return $out;
	}
}

