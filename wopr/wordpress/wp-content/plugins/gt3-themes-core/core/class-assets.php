<?php

namespace GT3\ThemesCore;

use Elementor\Plugin as Elementor_Plugin;

use GT3\ThemesCore\Assets\Style;
use GT3\ThemesCore\Assets\Script;

final class Assets {
	private static $instance = null;

	public static $is_preview = false;

	private static $widgets_assets = array(
		'core'           => array(),
		'isotope'        => array( 'gt3-core/core' ),
		'widgets/column' => array( 'gt3-core/core' ),
		'slick'          => array( 'jquery' ),
	);

	/** @return \GT3\ThemesCore\Assets */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		add_action('elementor/widgets/widgets_registered', array( $this, 'widgets_registered' ), 50);
		add_action('elementor/elements/elements_registered', array( $this, 'elements_registered' ), 50);
//		add_action('elementor/init', array($this,'register_widgets_assets'));
		add_action('elementor/preview/init', array( $this, 'elementor_preview_init' ));

		add_action('wp_enqueue_scripts', array( $this, 'print_elementor_styles' ), 7);

		if(!is_admin()) {
			add_action('dynamic_sidebar', array( $this, 'render_wp_widget' ));
			add_filter('widget_block_dynamic_classname', array( $this, 'render_wp_widget_filter_class' ), 10, 2);
		}

		add_action('elementor/widget/before_render_content', array( $this, 'render_widget' ));

		add_action('wp_ajax_gt3_clear_assets_cache', array( $this, 'clear_cache' ));

		Script::instance();
		Style::instance();

		add_action(
			'admin_print_styles', function(){
			wp_register_style('gt3-core-admin-styles', false);
			wp_enqueue_style('gt3-core-admin-styles');
			$css = '#adminmenu img{ width: 100%; height: 100%; max-width: 20px; max-height: 20px; }';
			wp_add_inline_style('gt3-core-admin-styles', $css);
		}
		);

	}






	public function register_widgets_assets(){
		static $loaded = false;
		if($loaded) {
			return;
		}
		$loaded               = true;
		self::$widgets_assets = apply_filters('gt3/core/assets/widgets_assets', self::$widgets_assets);

		if(is_array(self::$widgets_assets) && count(self::$widgets_assets)) {
			foreach(self::$widgets_assets as $name => $deps) {
				Style::register_widget($name, $deps);
				Script::register_widget($name, $deps);
			}
		}
	}

	/**
	 * @param \Elementor\Widgets_Manager $manager
	 */
	function widgets_registered($manager){
		static $loaded = false;
		if($loaded) {
			return;
		}
		$loaded = true;

		$widgets = $manager->get_widget_types();

		foreach($widgets as $widget_slug => $widget) {
			static::register_widget('widgets/'.$widget_slug, $widget);
		}
	}

	function elements_registered(){
		static $loaded = false;
		if($loaded) {
			return;
		}
		$loaded = true;

		self::$is_preview = class_exists('Elementor\Plugin') && Elementor_Plugin::instance()->preview->is_preview();

		$this->register_widgets_assets();

		$manager = Elementor_Plugin::instance()->elements_manager;

		$elements = $manager->get_element_types();
		foreach($elements as $element_slug => $element) {
			static::register_widget('widgets/'.$element_slug, $element);
		}
	}

	public function elementor_preview_init(){
		$this->elements_registered();
//		$this->widgets_registered();
	}

	public static function register_widget($widget_name, $depts = array()){
		Script::register_widget($widget_name, $depts);
		Style::register_widget($widget_name, $depts);
	}


	public function print_elementor_styles(){
		global $post;

		$this->register_widgets_assets();

		$content = ';window.resturl = window.resturl || "'.get_rest_url().'";';

		wp_script_add_data('gt3pg_pro--core', 'data', $content);
		Style::enqueue_widget('widgets/column');
		Script::enqueue_widget('widgets/column');
		if(is_singular()) {
			global $post;
			$post_type = $post->post_type;
			Style::enqueue_theme_asset("cpt/single");
			Style::enqueue_theme_asset("cpt/${post_type}");
		}

		if(is_singular() && class_exists('Elementor\Plugin') && Elementor_Plugin::$instance->db->is_built_with_elementor($post->ID)) {

			$post_id = $post->ID;
			$widgets = get_post_meta($post_id, '_elementor_controls_usage', true);
			if(!empty($widgets)) {
				$widgets = maybe_unserialize($widgets);
				if(is_array($widgets)) {
					foreach($widgets as $widget => $stats) {
						self::print_widget_assets($widget);
					}

					return;
				}
			}

			$document = Elementor_Plugin::$instance->documents->get_doc_for_frontend($post_id);
			// Change the current post, so widgets can use `documents->get_current`.
			Elementor_Plugin::$instance->documents->switch_to_document($document);
			$data = $document->get_elements_data();
			if(is_array($data) && count($data)) {
				foreach($data as $modules) {
					$this->elementor_recursive_style($modules);
				}
			}
			Elementor_Plugin::$instance->documents->restore_document();

		}
	}

	protected function elementor_recursive_style($data){
		if(key_exists('elType', $data)) {
			switch($data['elType']) {
				case 'section':
				case 'column':
					foreach($data['elements'] as $modules) {
						$this->elementor_recursive_style($modules);
					}

					break;
				case 'widget':
					self::print_widget_assets($data['widgetType']);
					break;
			}
		}
	}


	/** @param \Elementor\Widget_Base $widget */
	public function render_widget($widget){
		$widget_name = $widget->get_name();

		Style::enqueue_widget('widgets/'.$widget_name);
		Script::enqueue_widget('widgets/'.$widget_name);
	}

	public static function print_widget_assets($widget_name){
		Style::register_widget('widgets/'.$widget_name, true);
		Style::enqueue_widget('widgets/'.$widget_name);
//		Script::enqueue_widget('widgets/'.$widget_name);
	}

	public function render_wp_widget($widget){
		$widget_name = _get_widget_id_base($widget['id']);
		Style::enqueue_theme_asset('wp-widgets');
		$slug = 'wp-widgets/'.$widget_name;

		Style::register_widget($slug);
		Style::enqueue_widget($slug);
	}

	public function render_wp_widget_filter_class($class, $block_name){
		$pos = strpos($block_name, '/');
		if(false !== $pos) {
			$widget_name = substr($block_name, ++$pos);
			Style::enqueue_theme_asset('wp-widgets');
			$slug = 'wp-widgets/'.$widget_name;

			Style::register_widget($slug);
			Style::enqueue_widget($slug);
		}

		return $class;
	}

	public function clear_cache(){
		if(!wp_verify_nonce($_POST['_nonce'], 'gt3_clear_assets_cache')) {
			die('nonce');
		}

		Style::instance()->clear_cache();
		Script::instance()->clear_cache();
		die;
	}

}
