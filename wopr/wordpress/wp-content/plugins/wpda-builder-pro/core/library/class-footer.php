<?php

namespace WPDaddy\Builder\Library;
if(!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Core\DocumentTypes\Post;
use Elementor\Modules\Library\Documents\Library_Document;
use Elementor\Utils;
use WPDaddy\Builder\Elementor;
use WPDaddy\Builder\Settings;

class Footer extends Basic {

	const post_type = 'elementor_library';
	public static $name = 'wpda-footer';

	public function __construct(array $data = []){
		if($data) {
			$template = get_post_meta($data['post_id'], '_wp_page_template', true);

			if(empty($template)) {
				$template = 'default';
			}

			$data['settings']['template'] = $template;
		}

		parent::__construct($data);
	}

	public static function manage_posts_columns($column){
		return array(
			'cb'         => '<input type="checkbox" />',
			'title'      => esc_html__('Title', 'wpda-builder'),
			'status'     => esc_html__('Status', 'wpda-builder'),
			'conditions' => esc_html__('Conditions', 'wpda-builder'),
			'date'       => esc_html__('Date', 'wpda-builder'),
		);
	}

	public static function manage_posts_custom_column($column, $post_id){
		$this_url = $_SERVER['REQUEST_URI'];

		switch($column) {
			case 'status':
				echo '<span
				class="active-status"
				data-status="'.(!!get_post_meta($post_id, '_wpda-builder-active', true) ? 'true' : 'false').'"
				data-active='.__('Active', 'wpda-builder').'
				data-inactive='.__('Inactive', 'wpda-builder').'>
			</span>';
				break;
			case 'conditions':
				$conditionsArray = array(
					'none'        => __('None', 'wpda-builder'),
					'all'         => __('All Pages', 'wpda-builder'),
					'is_single'   => __('Posts', 'wpda-builder'),
					'is_page'     => __('Pages', 'wpda-builder'),
					'is_singular' => __('Singular', 'wpda-builder'),
					'is_search'   => __('Search', 'wpda-builder'),
					'is_404'      => __('404', 'wpda-builder'),
					'is_archive'  => __('Archive', 'wpda-builder'),
					'is_home'     => __('Homepage', 'wpda-builder'),
					'is_shop_wpda'     => __('Shop', 'wpda-builder'),
				);

				$conditions          = get_post_meta($post_id, '_wpda-builder-conditions', true);
				$defaults_conditions = array(
					array(
						'type'  => 'include',
						'key'   => 'none',
						'value' => [],
					)
				);
				try {
					$conditions = json_decode($conditions, true);
					if(json_last_error() || !is_array($conditions)) {
						$conditions = $defaults_conditions;
					}
				} catch(\Exception $ex) {
					$conditions = $defaults_conditions;
				}

				echo join(
					',', array_map(
					function($cond) use ($conditionsArray){
						return $conditionsArray[$cond['key']];
					}, $conditions
				)
				);
				break;
			default:
				break;
		}
	}

	public function filter_admin_row_actions($actions){
		if($this->is_built_with_elementor() && $this->is_editable_by_current_user()) {
			$actions['edit_with_elementor'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				$this->get_edit_url(),
				__('Edit Footer', 'wpda-builder')
			);

			$actions['wpda_builder_pro_settings'] = sprintf(
				'<a href="%1$s" data-id="%2$d" class="wpda-builder--settings" data-type="%4$s">%3$s</a>',
				'#',
				$this->get_main_id(),
				__('Settings', 'wpda-builder'),
				$this->get_name()
			);
			unset($actions['edit_vc']);
		}

		return $actions;
	}

	public static function static_get_edit_url($post_id){
		$url = add_query_arg(
			[
				'post'   => $post_id,
				'action' => 'elementor',
			],
			admin_url('post.php')
		);

		return $url;
	}

	public static function get_properties(){
		$properties = parent::get_properties();

		$properties['support_wp_page_templates'] = false;
		$properties['admin_tab_group']           = 'library';
		$properties['show_in_library']           = true;
		$properties['register_type']             = false;

		return $properties;
	}

	/**
	 * @access public
	 */
	public function get_name(){
		return self::$name;
	}

	protected static function get_editor_panel_categories(){
		return Utils::array_inject(
			parent::get_editor_panel_categories(),
			'theme-elements',
			[
				'theme-elements-single' => [
					'title'  => __('Single', 'wpda-builder'),
					'active' => false,
				],
			]
		);
	}

	public function get_css_wrapper_selector(){
		return '.wpda-builder-page-'.$this->get_main_id();
	}

	protected function register_controls(){
		parent::register_controls();

		Post::register_style_controls($this);
	}

	/**
	 * @access public
	 * @static
	 */
	public static function get_title(){
		return __('WPDaddy Footer', 'wpda-builder');
	}

	protected function get_remote_library_config(){
		$config = parent::get_remote_library_config();

		$config['category'] = '';
		$config['type']     = self::$name;

		return $config;
	}

	public function get_initial_config(){
		$config = parent::get_initial_config();

		return $config;
	}

	public static function load_canvas_template($single_template){
		global $post;
		$_elementor_template_type = get_metadata('post', $post->ID, '_elementor_template_type', true);

		if($post->post_type === 'elementor_library' && $_elementor_template_type === self::$name) {
			$single_template = __DIR__.'/template.php';
		}

		return $single_template;
	}
}
