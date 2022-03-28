<?php

namespace WPDaddy\Builder;
defined('ABSPATH') or exit;

use WP_REST_Request;
use WP_REST_Server;
use WPDaddy\Builder\Library\Menu as Menu_Library;

class Mega_Menu {
	const REST_NAMESPACE = 'wpda-builder/v2/nav-menu';
	const permission = 'manage_options';
	const post_type = 'elementor_library';
	const meta_key = '_wpda_menu_settings';
	public static $name = 'wpda-menu';

	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		add_action('rest_api_init', array( $this, 'rest_api_init' ));
		add_filter('wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ));
		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ));
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'wp_edit_nav_menu_walker' ), 10, 2 );

		add_action('elementor/preview/enqueue_scripts', array( $this, 'elementor_preview_init' ));


	}


	public function wp_edit_nav_menu_walker($args,$menu_id) {
		if(self::is_mega_menu($menu_id)) {
			do_action('wpda-builder/mega-menu/enable');
		}
		return $args;
	}

	public function admin_enqueue_scripts(){
		$screen = get_current_screen();

		if(in_array($screen->id, [ 'nav-menus' ])) {
			wp_enqueue_script('react');
			wp_enqueue_script('react-dom');
			wp_enqueue_script('wp-components');
			wp_enqueue_script('wp-api-fetch');
			wp_enqueue_style('wp-components');

			Assets::enqueue_script('admin/nav-menus');
			Assets::enqueue_style('admin/nav-menus');
		}
	}

	public function elementor_preview_init() {
		Assets::enqueue_style('wpda-mega-menu', 'frontend/mega-menu-frontend.css');
		Assets::enqueue_script('wpda-mega-menu', 'frontend/mega-menu-frontend.js');
	}

	public function wp_nav_menu_args($args){
		$menu = false;
		if(key_exists('menu', $args) && !empty($args['menu'])) {
			$menu = wp_get_nav_menu_object($args['menu']);;
		} else if(key_exists('theme_location', $args) && !empty($args['theme_location'])) {
			// Get the nav menu based on the theme_location.
			$locations = get_nav_menu_locations();
			if($locations && isset($locations[$args['theme_location']])) {
				$menu = wp_get_nav_menu_object($locations[$args['theme_location']]);
			}
		}

		if(false !== $menu) {
			if(self::is_mega_menu($menu->term_id)) {
				$args['walker'] = new Walker_Nav_Menu();
				Assets::enqueue_style('wpda-mega-menu', 'frontend/mega-menu-frontend.css');
				Assets::enqueue_script('wpda-mega-menu', 'frontend/mega-menu-frontend.js');
			}
		}

		return $args;
	}

	public function is_user_can(){
		return (is_user_logged_in() && current_user_can(self::permission));
	}

	public function rest_api_init(){
		register_rest_route(
			self::REST_NAMESPACE,
			'/item-settings/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => array( $this, 'is_user_can' ),
					'callback'            => array( $this, 'menu_item_set_settings' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function($param, $request, $key){
								return is_numeric($param);
							},
							'sanitize_callback' => function($param, $request, $key){
								return (int) $param;
							}
						),
					),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/settings/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => array( $this, 'is_user_can' ),
					'callback'            => array( $this, 'menu_set_settings' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => function($param, $request, $key){
								return is_numeric($param);
							},
							'sanitize_callback' => function($param, $request, $key){
								return (int) $param;
							}
						),
					),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/get_templates',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => array( $this, 'is_user_can' ),
					'callback'            => array( $this, 'rest_get_templates' ),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'create',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => array( $this, 'is_user_can' ),
					'callback'            => array( $this, 'rest_create_templates' ),
				)
			)
		);

	}

	public function rest_create_templates(WP_REST_Request $request){
		$title        = $request->get_param('title');
		$menu_item_id = (int) $request->get_param('menu_item_id');

		$respond = array(
			'error' => true,
			'msg'   => 'Error',
		);

		$post_id = wp_insert_post(
			array(
				'post_title'   => sanitize_text_field($title),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
				'post_type'    => 'elementor_library',
			)
		);

		if(!is_wp_error($post_id)) {
			update_post_meta($post_id, '_elementor_template_type', self::$name);

			$settings = array_merge(
				self::get_menu_item_meta($menu_item_id),
				array(
					'library' => $menu_item_id,
				)
			);

			update_post_meta($menu_item_id, self::meta_key, json_encode($settings));

			$url = add_query_arg(
				array(
					'post'   => $post_id,
					'action' => 'elementor',
				),
				admin_url('post.php')
			);

			$url = apply_filters('elementor/document/urls/edit', $url, $this);

			$respond = array(
				'error'     => false,
				'meta'      => self::get_menu_item_meta($menu_item_id),
				'edit_link' => $url,
			);
		}

		return rest_ensure_response($respond);
	}

	public function rest_get_templates(WP_REST_Request $request){
		$params    = array_merge(
			array(
				's'       => '',
				'include' => '',
				'exclude' => '',
				'page'    => 1,
			), $request->get_params()
		);
		$isSelect2 = ($request->get_param('typeQuery') === 'select2');

		$args = array(
			'post_status'    => 'publish',
			'post_type'      => 'elementor_library',
			'posts_per_page' => 5,
			'paged'          => $params['page'],
			'meta_query'     => [
				[
					'key'   => '_elementor_template_type',
					'value' => self::$name
				]
			]
		);

		if(!empty($params['s'])) {
			$args['s'] = $params['s'];
		}
		if(!empty($params['include'])) {
			$args['post__in'] = is_array($params['include']) ? $params['include'] : array( $params['include'] );
		}
		if(!empty($params['exclude'])) {
			$args['post__not_in'] = is_array($params['exclude']) ? $params['exclude'] : array( $params['exclude'] );
		}

		$response_array = array();
		$keys           = $isSelect2 ?
			[ 'label' => 'text', 'value' => 'id' ] :
			[ 'label' => 'label', 'value' => 'value' ];

		$posts = new \WP_Query($args);
		if($posts->post_count > 0) {
			foreach($posts->posts as /** \WP_Post */ $_post) {
				$response_array[] = array(
					$keys['label'] => !empty($_post->post_title) ? $_post->post_title : __('No Title', 'wpda-builder'),
					$keys['value'] => $_post->ID,
				);
			}
		}
		wp_reset_postdata();

		$return = array(
			'results'    => $response_array,
			'pagination' => array(
				'more' => $posts->max_num_pages >= ++$params['page'],
			)
		);

		return rest_ensure_response($return);
	}

	public function menu_item_get_settings(WP_REST_Request $request){
		$id = $request->get_param('id');

		$settings = self::get_menu_item_meta($id);

		return rest_ensure_response(
			array(
				'success' => true,
				'respond' => $settings,
				'raw'     => get_post_meta($id, self::meta_key, true),
				'id'      => $id,
			)
		);
	}

	public function menu_item_set_settings(WP_REST_Request $request){
		$get = $request->get_param('get');
		if($get) {
			return $this->menu_item_get_settings($request);
		}

		$id = $request->get_param('id');

		$settings = array_merge(
			self::get_menu_item_meta($id),
			(array) $request->get_param('data')
		);

		update_post_meta($id, self::meta_key, json_encode($settings));

		return rest_ensure_response(
			array(
				'success' => true,
				'respond' => $settings,
				'raw'     => get_post_meta($id, self::meta_key, true),
				'p'       => $request->get_params(),
			)
		);
	}

	public function menu_get_settings(WP_REST_Request $request){
		$id = $request->get_param('id');

		$settings = self::get_menu_meta($id);

		return rest_ensure_response(
			array(
				'success' => true,
				'respond' => $settings,
				'raw'     => get_term_meta($id, self::meta_key, true),
				'p'       => $request->get_params(),
			)
		);
	}

	public function menu_set_settings(WP_REST_Request $request){
		$get = $request->get_param('get');
		if($get) {
			return $this->menu_get_settings($request);
		}

		$id = $request->get_param('id');

		$settings = $request->get_param('data');
		$settings = array_merge(array(
			'enable'  => false,
			'library' => 0,
		), $settings);
		update_term_meta($id, self::meta_key, json_encode($settings));

		return rest_ensure_response(
			array(
				'success' => true,
				'respond' => $settings,
				'raw'     => get_term_meta($id, self::meta_key, true),
				'p'       => $request->get_params(),
			)
		);
	}

	/** @param int|array $meta */
	public static function get_menu_meta($meta){
		if(!is_array($meta)) {
			$meta = get_term_meta($meta, self::meta_key, true);
		}
		$_meta = array(
			'enable' => false,
		);

		$meta = json_decode($meta, true);
		if(!json_last_error() && is_array($meta)) {
			$_meta = array_merge($_meta, $meta);
		}

		return $_meta;
	}

	/** @param int|array $meta */
	public static function get_menu_item_meta($meta){
		if(!is_array($meta)) {
			$meta = get_post_meta($meta, self::meta_key, true);
		}

		$_meta = array(
			'enable'           => false,
			'originalOnMobile' => false,
			'library'          => 0,
		);

		$meta = json_decode($meta, true);
		if(!json_last_error() && is_array($meta)) {
			$_meta = array_merge($_meta, $meta);
		}

		return $_meta;
	}

	public static function is_mega_menu($id){
		$meta = self::get_menu_meta($id);

		return $meta['enable'];
	}

	public static function is_mega_menu_item($id){
		$meta = self::get_menu_item_meta($id);

		return $meta['enable'];
	}

}

