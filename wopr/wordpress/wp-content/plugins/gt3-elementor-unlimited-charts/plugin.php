<?php

namespace ElementorModal\Widgets;

if(!defined('ABSPATH')) {
	exit;
}

if (!class_exists('ElementorModal\Widgets\GT3_Elementor_UnlimitedCharts')) {
	class GT3_Elementor_UnlimitedCharts {

		public static $JS_URL = 'js';
		public static $CSS_URL = 'css';
		public static $IMAGE_URL = 'css';
		private $min = '';
		const version = GT3_ChartElementor_VERSION;

		///////////////////
		private $require_widgets = array(
			// Widgets
			'unlimited-charts' => 'Chart',
			'grid-gt3'    => 'Grid',
		);

		private $controls = array(
			// Controls
			'gt3-elementor-core-repeatable-text' => 'RepeatableText',
		);

		public function __construct(){
			$this->min       = '';//defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
			self::$JS_URL    = plugins_url('/assets/js/', __FILE__);
			self::$CSS_URL   = plugins_url('/assets/css/', __FILE__);
			self::$IMAGE_URL = plugins_url('/assets/img/', __FILE__);
			$this->actions();
		}

		private function actions(){
			add_action('elementor/init', array( $this, 'elementor_init' ), 50);
			add_action('elementor/controls/controls_registered', array( $this, 'controls_registered' ));
			add_action('elementor/elements/categories_registered', array( $this, 'categories_registered' ));
			add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
			add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ));
			add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ));
			add_action('elementor/editor/after_enqueue_scripts', array( $this, 'editor_after_enqueue_scripts' ));
			add_action('elementor/frontend/after_enqueue_scripts', array( $this, 'frontend_after_enqueue_scripts' ));
			add_action('elementor/editor/after_enqueue_styles', array( $this, 'editor_after_enqueue_styles' ));
		}

		/** @var \Elementor\Elements_Manager $elements_manager */
		public function categories_registered($elements_manager){
			$elements_manager = \Elementor\Plugin::instance()->elements_manager;
			$categories = $elements_manager->get_categories();
			if (!key_exists('gt3-core-elements',$categories)) {
				$elements_manager->add_category(
					'gt3-core-elements',
					array(
						'title' => esc_html__('GT3 Core Widgets', 'gt3_unlimited_chart'),
						'icon'  => 'fa fa-plug'
					)
				);
			}
		}


		public function elementor_init(){
			$elements_manager = \Elementor\Plugin::instance()->elements_manager;
			$categories = $elements_manager->get_categories();
			if (!key_exists('gt3-core-elements',$categories)) {
				$elements_manager->add_category(
					'gt3-core-elements',
					array(
						'title' => esc_html__('GT3 Core Widgets', 'gt3_unlimited_chart'),
						'icon'  => 'fa fa-plug'
					)
				);
			}

			$this->include_files();
		}

		public function controls_registered($controls_manager){
			if(is_array($this->controls) && !empty($this->controls)) {
				foreach($this->controls as $module) {
					/** @var \Elementor\\GT3_Elementor_Core_Control_{$module} $module */
					$module = sprintf('Elementor\\GT3_Core_Elementor_Control_%s', $module);

					if(class_exists($module)) {
						if($controls_manager->get_control($module::type()) === false) {
							$controls_manager->register_control($module::type(), new $module);
						}
					}
				}
			}
		}

		private function include_files(){
			foreach($this->require_widgets as $slug => $module) {
				$dir = __DIR__.'/elementor/widgets/'.sanitize_title($module).'.php';
				if(file_exists($dir) && is_readable($dir)) {
					require_once $dir;
					$module = sprintf('ElementorModal\\Widgets\\GT3_UnlimitedCharts_%s', $module);
					if(class_exists($module)) {
						new $module();
					}
				}
			}

			$this->controls = apply_filters('gt3/elementor/controls/register', $this->controls);

			if(is_array($this->controls) && !empty($this->controls)) {
				foreach($this->controls as $slug => $module) {
					require_once __DIR__.'/elementor/controls/'.strtolower($module).'.php';
				}
			}
		}



		public function admin_enqueue_scripts(){
		}

		public function enqueue_scripts(){
			// CSS

			// JS
			wp_register_script('gt3-unlimited-chart', plugins_url('/assets/js/Chart.min.js', __FILE__), array( 'jquery' ), null, true);
			wp_register_script('elementor-waypoints', plugins_url('/assets/js/jquery.waypoints.min', __FILE__), array( 'jquery' ), '4.0.1', true);
			wp_register_script('gt3-unlimited-chart-frontend', plugins_url('/assets/js/frontend.js', __FILE__), array('gt3-unlimited-chart'), $this::version, true);

		}

		public function frontend_after_enqueue_scripts(){
		}

		public function editor_after_enqueue_scripts(){
			wp_enqueue_script('gt3-unlimited-chart-editor.js', plugins_url('/assets/js/editor.js', __FILE__), array(), $this::version, true);
		}

		public function editor_after_enqueue_styles(){
			wp_enqueue_style('gt3-unlimited-chart-editor', plugins_url('/dist/editor'.$this->min.'.css', __FILE__), array(), $this::version);
		}
	}

	new GT3_Elementor_UnlimitedCharts();

}
