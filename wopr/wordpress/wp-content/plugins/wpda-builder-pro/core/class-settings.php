<?php

namespace WPDaddy\Builder;
defined('ABSPATH') OR exit;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class Settings {
	private static $instance = null;
	const REST_NAMESPACE      = 'wpda-builder/v2/settings';
	const permission          = 'manage_options';
	const menu_slug           = 'wpda-builder-settings';
	const settings_option_key = 'wpda-builder-settings';

	private $settings = array(
		'header_area'    => '',
		'footer_area'    => '',
		'current_header' => '',
		'current_footer' => '',
		'conditions'     => '',
	);

	/** @return self */
	public static function instance(){
		if(is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct(){
		$this->load_settings();

		if(self::is_user_can()) {
			add_action('rest_api_init', array( $this, 'action_rest_api_init' ));
			add_action('admin_print_scripts-toplevel_page_wpda-builder-settings', array( $this, 'action_admin_enqueue_scripts' ));
		}

		add_action(
			'admin_enqueue_scripts', function($a){
			global $current_screen;
			if($current_screen->id === 'edit-elementor_library'
			   && $current_screen->post_type === 'elementor_library'
			   && key_exists('tabs_group', $_GET)
			   && $_GET['tabs_group'] === 'library'
			) {
				wp_enqueue_script('react');
				wp_enqueue_script('react-dom');
				wp_enqueue_script('moment');
				wp_enqueue_script('lodash');
				wp_enqueue_script('wp-components');
				wp_enqueue_script('wp-api-fetch');
				wp_enqueue_script('wp-notices');

				wp_enqueue_style('wp-components');
				wp_enqueue_style('wp-element');
				wp_enqueue_style('wp-blocks-library');
				wp_enqueue_style('wpda-settings-font', '//fonts.googleapis.com/css?family=Poppins:400,500,700%7CMontserrat:400,600,700%7CNunito:700');

				Assets::enqueue_style('wpda-admin/cpt', 'admin/cpt.css');
				Assets::enqueue_script('wpda-admin/cpt', 'admin/cpt.js');
			}
		}
		);

	}

	public static function is_user_can(){
		return (is_user_logged_in() && current_user_can(self::permission));
	}

	public function action_rest_api_init(){
		if(!self::is_user_can()) {
			return;
		}

		register_rest_route(
			self::REST_NAMESPACE,
			'/save',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => array( __CLASS__, 'is_user_can' ),
					'callback'            => array( $this, 'rest_save_settings' ),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/get',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => array( __CLASS__, 'is_user_can' ),
					'callback'            => array( $this, 'rest_get_settings' ),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/template/(?P<id>\d+)',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'permission_callback' => array( __CLASS__, 'is_user_can' ),
					'callback' => array( $this, 'template_get_settings' ),
					'args'     => array(
						'id' => array(
							'validate_callback' => function($param, $request, $key){
								return is_numeric($param);
							}
						),
					),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/template/(?P<id>\d+)',
			array(
				array(
					'methods'  => WP_REST_Server::CREATABLE,
					'permission_callback' => array( __CLASS__, 'is_user_can' ),
					'callback' => array( $this, 'template_set_settings' ),
					'args'     => array(
						'id' => array(
							'validate_callback' => function($param, $request, $key){
								return is_numeric($param);
							}
						),
					),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/posts/settings/(?P<id>\d+)',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'permission_callback' => array( __CLASS__, 'is_user_can' ),
					'callback' => array( $this, 'posts_get_settings' ),
					'args'     => array(
						'id' => array(
							'validate_callback' => function($param, $request, $key){
								return is_numeric($param);
							}
						),
					),
				)
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/posts/settings/(?P<id>\d+)',
			array(
				array(
					'methods'  => WP_REST_Server::CREATABLE,
					'permission_callback' => array( __CLASS__, 'is_user_can' ),
					'callback' => array( $this, 'posts_set_settings' ),
					'args'     => array(
						'id' => array(
							'validate_callback' => function($param, $request, $key){
								return is_numeric($param);
							}
						),
					),
				)
			)
		);
	}

	function template_get_settings(WP_REST_Request $request){
		$response = new WP_REST_Response();
		$id       = $request->get_param('id');

		$defaults = array(
			'title'       => get_the_title($id),
			'active'      => false,
			'conditions'  => array(
				array(
					'type'  => 'include',
					'key'   => 'none',
					'value' => [],
				)
			),
			'_wpda_nonce' => wp_create_nonce('wpda-template-nonce-'.$id)
		);

		$_meta      = array(
			'active' => !!get_post_meta($id, '_wpda-builder-active', true),
		);
		$conditions = get_post_meta($id, '_wpda-builder-conditions', true);
		try {
			$conditions = json_decode($conditions, true);
			if(json_last_error() || !is_array($conditions)) {
				$conditions = $defaults['conditions'];
			}
		} catch(\Exception $ex) {
			$conditions = $defaults['conditions'];
		}
		$_meta['conditions'] = $conditions;

		$data = array_merge($defaults, $_meta);

		$response->set_data($data);

		return rest_ensure_response($response);
	}

	function template_set_settings(WP_REST_Request $request){
		$params   = array_merge(
			array(
				'id'          => '0',
				'_wpda_nonce' => '',
				'data'        => array(),
			), $request->get_params()
		);
		$response = new WP_REST_Response();

		if(!wp_verify_nonce($params['_wpda_nonce'], 'wpda-template-nonce-'.$params['id'])) {
			$response->set_status(403);
			$response->set_data(
				array(
					'msg' => 'not authorized'
				)
			);

			return rest_ensure_response($response);
		}

		$id    = $request->get_param('id');
		$_post = get_post($id);

		if(is_null($_post)) {
			$response->set_status(404);
			$response->set_data(
				array(
					'msg' => 'Post not found'
				)
			);

			return rest_ensure_response($response);
		}

		$res = array(
			'params' => $params
		);
		foreach($params['data'] as $key => $value) {
			$res[$key] = array(
				'status' => update_post_meta(intval($_post->ID), sprintf('_wpda-builder-%s', $key), (is_array($value) ? json_encode($value) : $value))
			);
		}
		$response->set_data($res);

		return rest_ensure_response($response);
	}

	function posts_get_settings(WP_REST_Request $request){
		$response = new WP_REST_Response();
		$id       = $request->get_param('id');
		$_post    = get_post($id);
		if(is_null($_post)) {
			$response->set_status(404);
			$response->set_data(
				array(
					'msg' => 'Post not found',
				)
			);

			return rest_ensure_response($response);
		}

		$defaults = array(
			'_wpda_nonce' => wp_create_nonce('wpda-posts-nonce-'.$id)
		);

		$_meta = array(
			'header' => get_post_meta($id, '_wpda-builder-header', true),
			'footer' => get_post_meta($id, '_wpda-builder-footer', true),
		);

		$data = array_merge($defaults, $_meta);

		$response->set_data($data);

		return rest_ensure_response($response);
	}

	function posts_set_settings(WP_REST_Request $request){
		$params   = array_merge(
			array(
				'id'          => '0',
				'_wpda_nonce' => '',
				'data'        => array(),
			), $request->get_params()
		);
		$response = new WP_REST_Response();

		if(!wp_verify_nonce($params['_wpda_nonce'], 'wpda-posts-nonce-'.$params['id'])) {
			$response->set_status(403);
			$response->set_data(
				array(
					'msg' => 'not authorized'
				)
			);

			return rest_ensure_response($response);
		}

		$id    = $request->get_param('id');
		$_post = get_post($id);

		if(is_null($_post)) {
			$response->set_status(404);
			$response->set_data(
				array(
					'msg' => 'Post not found'
				)
			);

			return rest_ensure_response($response);
		}

		$res = array(
			'params' => $params
		);
		foreach($params['data'] as $key => $value) {
			$res[$key] = array(
				'status' => update_post_meta(intval($_post->ID), sprintf('_wpda-builder-%s', $key), (is_array($value) ? json_encode($value) : $value))
			);
		}
		$response->set_data($res);

		return rest_ensure_response($response);
	}


	public function rest_save_settings(WP_REST_Request $request){
		$params = $request->get_params();

		$response = new WP_REST_Response();
		if(!self::is_user_can() || !wp_verify_nonce($params['_wpda_nonce'], '_wpda_nonce_settings')) {
			$response->set_status(403);
			$response->set_data(
				array(
					'msg' => 'not authorized'
				)
			);
		} else {
			$options = $params['settings'];

			$saved = $this->set_settings($options);
			if($saved) {
				$response->set_status(200);
				$response->set_data(
					array(
						'saved' => $saved,
						'msg'   => __('Saved', 'wpda-builder'),
					)
				);
			} else {
				$response->set_status(403);
				$response->set_data(
					array(
						'msg' => __('Error', 'wpda-builder'),
					)
				);
			}
		}

		return rest_ensure_response($response);
	}

	function rest_get_settings(){
		$response = new WP_REST_Response();

		if(!self::is_user_can()) {
			$response->set_status(403);
			$response->set_data(
				array(
					'msg' => 'not authorized'
				)
			);
		} else {
			$headers = new \WP_Query(
				array(
					'post_type'      => 'elementor_library',
					'posts_per_page' => '-1',
					'meta_query'     => array_merge(
						array(
							'relation' => 'AND',
						),
						array(
							array(
								'key'   => '_elementor_template_type',
								'value' => 'wpda-header',
							),
						)
					),
				)
			);

			$header_a = [];
			if($headers->have_posts()) {
				$default_conditions = array(
					array(
						'type'  => 'include',
						'key'   => 'none',
						'value' => [],
					)
				);

				foreach($headers->posts as $header_) {
					$header_id  = $header_->ID;
					$conditions = get_post_meta($header_id, '_wpda-builder-conditions', true);
					try {
						$conditions = json_decode($conditions, true);
						if(json_last_error() || !is_array($conditions)) {
							$conditions = $default_conditions;
						}
					} catch(\Exception $ex) {
						$conditions = $default_conditions;
					}

					$header_a[$header_id] = array(
						'id'         => $header_id,
						'title'      => $header_->post_title,
						'active'     => get_post_meta($header_id, '_wpda-builder-active', true),
						'conditions' => $conditions,
						'edit_link'  => Library\Basic::edit_url($header_id),
					);
				}
			}

			$footers = new \WP_Query(
				array(
					'post_type'      => 'elementor_library',
					'posts_per_page' => '-1',
					'meta_query'     => array_merge(
						array(
							'relation' => 'AND',
						),
						array(
							array(
								'key'   => '_elementor_template_type',
								'value' => 'wpda-footer',
							),
						)
					),
				)
			);

			$footer_a = [];
			if($footers->have_posts()) {
				$default_conditions = array(
					array(
						'type'  => 'include',
						'key'   => 'none',
						'value' => [],
					)
				);

				foreach($footers->posts as $footer_) {
					$footer_id  = $footer_->ID;
					$conditions = get_post_meta($footer_id, '_wpda-builder-conditions', true);
					try {
						$conditions = json_decode($conditions, true);
						if(json_last_error() || !is_array($conditions)) {
							$conditions = $default_conditions;
						}
					} catch(\Exception $ex) {
						$conditions = $default_conditions;
					}

					$footer_a[$footer_id] = array(
						'id'         => $footer_id,
						'title'      => $footer_->post_title,
						'active'     => get_post_meta($footer_id, '_wpda-builder-active', true),
						'conditions' => $conditions,
						'edit_link'  => Library\Basic::edit_url($footer_id),
					);
				}
			}

			$response->set_status(200);
			$response->set_data(
				array_merge(
					$this->get_settings(),
					array(
						'_wpda_nonce'      => wp_create_nonce('_wpda_nonce_settings'),
						'_elementor_nonce' => wp_create_nonce('elementor_ajax'),
						'select_area_link' => add_query_arg(
							array(
								'wpda-show-panel' => 1
							), get_home_url()
						),
						'version'          => WPDA_PRO_HEADER_BUILDER__VERSION,
						'headers'          => $header_a,
						'footers'          => $footer_a,
					)
				)
			);
		}

		return rest_ensure_response($response);
	}

	public static function settings_page(){
		?>
		<div class="wpda-settings-main-wrapper">
			<div class="wpda-page-title-wrap">
				<div class="wpda-page-title">
					<div class="wpda-page-title-image-box-wrapper">
						<figure class="elementor-image-box-img">
							<img width="100" height="86" src="<?php echo Assets::get_dist_url(); ?>img/wpda-builder-icon.png" class="attachment-full size-full" alt="" title="about_icon_01">
						</figure>
						<div class="wpda-page-title-image-box-content">
							<h1 style="color: #f1f1f1;">WPDaddy <span>Builder</span><span class="wpda_sup"></span></h1>
						</div>
					</div>
				</div>
			</div>
			<div id="wpda-settings">
				<span class="spinner is-active"></span>
			</div>
		</div>
		<?php
	}

	public function action_admin_enqueue_scripts(){
		remove_all_actions('admin_notices');
		Assets::enqueue_style('wpda-builder', 'admin/settings.css');

		wp_enqueue_script('react');
		wp_enqueue_script('react-dom');
		wp_enqueue_script('moment');
		wp_enqueue_script('lodash');
		wp_enqueue_script('wp-components');
		wp_enqueue_script('wp-api-fetch');
		wp_enqueue_script('wp-notices');

		wp_enqueue_style('wp-components');
		wp_enqueue_style('wp-element');
		wp_enqueue_style('wp-blocks-library');

		Assets::enqueue_script('wpda-builder', 'admin/settings.js');
		wp_enqueue_style('wpda-settings-font', '//fonts.googleapis.com/css?family=Poppins:400,500,700%7CMontserrat:400,600,700%7CNunito:700');
	}

	private function get_default_settings(){
		return apply_filters('wpda/builder/settings/defaults', $this->settings);
	}

	private function load_settings(){
		$options = get_option(self::settings_option_key, '');
		try {
			if(!is_array($options) && is_string($options)) {
				$options = json_decode($options, true);
				if(json_last_error() || !is_array($options) || !count($options)) {
					$options = array();
				}
			}
		} catch(\Exception $exception) {
			$options = array();
		}

		$options        = array_replace_recursive($this->get_default_settings(), $options);
		$this->settings = $options;
	}

	/**
	 * @param string|bool $option
	 *
	 * @return array|string|mixed $settings
	 */
	public function get_settings($option = false){
		return (false === $option) ? $this->settings :
			((is_string($option) && key_exists($option, $this->settings)) ? $this->settings[$option] : '');
	}

	/** @param array $options
	 ** @return bool
	 **/
	private function set_settings(array $options = array()){
		if(!is_array($options)) {
			return false;
		}
		$options = array_merge(
			$this->get_settings(),
			$options
		);
		update_option(self::settings_option_key, json_encode($options));
		$this->settings = $options;

		return true;
	}
}
