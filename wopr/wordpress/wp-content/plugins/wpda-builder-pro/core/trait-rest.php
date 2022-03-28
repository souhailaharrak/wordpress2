<?php

namespace WPDaddy\Builder;

use WPDaddy\Builder\Settings;
use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') OR exit;

trait Trait_REST {
	private $rest_namespace = 'wpda-builder/v2/';

	private function init_trait_rest(){
		if(!Settings::is_user_can()) {
			return;
		}

		add_action('rest_api_init', array( $this, 'action_rest_api_init_trait' ));
	}

	public function action_rest_api_init_trait(){
		if(!Settings::is_user_can()) {
			return;
		}

		register_rest_route(
			'wpda-builder/v2/library',
			'/get/header',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => array( Settings::class, 'is_user_can' ),
					'callback'            => array( $this, 'rest_get_header' ),
				)
			)
		);
		register_rest_route(
			'wpda-builder/v2/library',
			'/get/footer',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => array( Settings::class, 'is_user_can' ),
					'callback'            => array( $this, 'rest_get_footer' ),
				)
			)
		);
	}

	public function rest_get_header(WP_REST_Request $request){
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
					'value' => 'wpda-header'
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

	public function rest_get_footer(WP_REST_Request $request){
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
					'value' => 'wpda-footer'
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
}
