<?php

namespace WPDaddy\Builder;
defined('ABSPATH') or exit;

use Elementor\Plugin;
use WPDaddy\Builder\Library\Footer;
use WPDaddy\Builder\Library\Header;
use WPDaddy\Builder\Library\Menu as Menu_Library;

class Init {
	use Trait_REST;

	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		Menu::instance();
		Settings::instance();
		Assets::instance();

		add_action('init', array( $this, 'action_init' ));

		add_filter(
			'elementor/document/urls/preview', function($url, $post){
			if(isset($_GET['template_post']) && !empty($_GET['template_post'])) {
				$url = add_query_arg(
					array(
						'template_post' => $_GET['template_post'],
					), $url
				);
			}

			return $url;
		}, 999, 2
		);

		if (!did_action('elementor/documents/register')) {
			add_action('elementor/documents/register', array($this, 'register_default_types'));
		} else {
			$this->register_default_types();
		}


		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ));

		$this->init_trait_rest();
	}

	public function admin_enqueue_scripts(){
		Assets::enqueue_style('wpda-admin-style', 'admin.css');
	}

	public function register_default_types(){
		if (Registration::active()) {
			if (isset($_REQUEST['elementor_library_type'])) {
				switch ($_REQUEST['elementor_library_type'] === Header::$name) {
					case Header::$name:
						add_action('manage_elementor_library_posts_custom_column', array(Header::class, 'manage_posts_custom_column'), 10, 2);
						add_filter('manage_elementor_library_posts_columns', array(Header::class, 'manage_posts_columns'));
						break;
					case Footer::$name:
						add_action('manage_elementor_library_posts_custom_column', array(Footer::class, 'manage_posts_custom_column'), 10, 2);
						add_filter('manage_elementor_library_posts_columns', array(Footer::class, 'manage_posts_columns'));
						break;
				}
			}

			Plugin::instance()->documents->register_document_type(Header::$name, Header::class);
			Plugin::instance()->documents->register_document_type(Footer::$name, Footer::class);
			Plugin::instance()->documents->register_document_type(Menu_Library::$name, Menu_Library::class);
		}
	}

	public function action_init(){
		if (Registration::active()) {
			Elementor::instance();

			Frontend::instance();
			Buffer::instance();

			Mega_Menu::instance();

			add_filter('single_template', array( Header::class, 'load_canvas_template' ));
			add_filter('single_template', array( Footer::class, 'load_canvas_template' ));
			add_filter('single_template', array( Menu_Library::class, 'load_canvas_template' ));
		}
	}
}


