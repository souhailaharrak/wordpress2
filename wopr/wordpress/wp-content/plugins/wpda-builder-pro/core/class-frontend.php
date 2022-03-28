<?php

namespace WPDaddy\Builder;

use Elementor\Plugin as Elementor_Plugin;
use WPDaddy\Builder\Library\Header;

defined('ABSPATH') OR exit;

final class Frontend {
	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		$settings = Settings::instance()->get_settings();
		if($this->show_panel()) {
			define('WPDA_PANEL_ENABLED', true);
			add_action('wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ));
		}
	}

	public static function can_show(){
		return (
			!is_admin() && current_user_can('manage_options')
		);
	}

	public static function show_panel(){
		return (
			static::can_show()
			&& key_exists('wpda-show-panel', $_GET) && $_GET['wpda-show-panel']
		);
	}

	public function action_wp_enqueue_scripts(){
		if(!static::can_show()) {
			return;
		}
		wp_enqueue_script('react');
		wp_enqueue_script('react-dom');
		wp_enqueue_script('moment');
		wp_enqueue_script('lodash');
		wp_enqueue_script('wp-components');
		wp_enqueue_script('wp-api-fetch');
		wp_enqueue_script('wp-notices');

		Assets::enqueue_script('wpda-builder-pro', 'frontend/panel.js');
		Assets::enqueue_style('wpda-builder-pro', 'frontend/panel.css');
	}
}
