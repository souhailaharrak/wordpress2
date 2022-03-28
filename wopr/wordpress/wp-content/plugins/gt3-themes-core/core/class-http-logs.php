<?php

namespace GT3\ThemesCore;

use WP_REST_Server;
use WP_REST_Request;

defined('ABSPATH') or exit;

class Http_Logs {
	private static $instance = null;

	private static $option_key = 'gt3_http_logs';
	private        $upload_dir = '';

	/** @return Http_Logs */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		$upload_dir       = wp_upload_dir();
		$this->upload_dir = trailingslashit($upload_dir['basedir']).'gt3-http-logs/';
		$this->maybe_create_folder($this->upload_dir);


		add_action('rest_api_init', array( $this, 'rest_init' ));
		if(!!get_option(self::$option_key, false)) {
			add_action('http_api_debug', array( $this, 'http_api_debug' ), 10, 5);
		}
	}

	private function maybe_create_folder($folder){
		if(false === stream_resolve_include_path($folder) || !is_dir($folder)) {
			@mkdir($folder);

			$fp = fopen($folder.'/'.base64_decode('Lmh0YWNjZXNz'), 'w+');
			fwrite($fp, base64_decode('PEZpbGVzTWF0Y2ggIlwubG9nJCI+CiAgT3JkZXIgRGVueSxBbGxvdwogIERlbnkgZnJvbSBhbGwKPC9GaWxlc01hdGNoPg=='));
			fflush($fp);
			fclose($fp);
		}
	}

	public function rest_init(){
		$namespace = 'gt3_core/v1/http_logs';

		register_rest_route(
			$namespace,
			'commands',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(WP_REST_Request $request){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'commands' ),
				)
			)
		);

		register_rest_route(
			$namespace,
			'get_logs',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(WP_REST_Request $request){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'get_logs' ),
				)
			)
		);

		register_rest_route(
			$namespace,
			'get_log',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(WP_REST_Request $request){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'get_log' ),
				)
			)
		);

		register_rest_route(
			$namespace,
			'clear',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(WP_REST_Request $request){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'clear' ),
				)
			)
		);


	}

	public function commands(\WP_REST_Request $request){
		if(!current_user_can('administrator')) {
			return rest_ensure_response(
				array(
					'error' => true,
				)
			);
		}
		$command = $request->get_param('command');

		update_option(self::$option_key, $command);

		return rest_ensure_response(
			array(
				'error' => false,
				'state' => $command,
			)
		);
	}

	public function http_api_debug($response, $a, $b, $request, $url){
		if(strpos($url, 'wp-cron') !== false || (strpos($url, 'gt3accounts.com') === false && strpos($url, 'users.loc') === false)) {
			return;
		}

		$file = 'debug';
		list ($micro, $timestamp) = explode(' ', microtime());
		$date = date('d.m.Y_H.i.s');

		$file = $this->upload_dir.$file.'_'.$date.'_'.$micro.'.json';
		$data = array(
			'url'      => $url,
			'response' => array(),
			'request'  => array(),
		);

		if(is_array($response) || is_object($response)) {
			if(is_object($response)) {
				$response = get_object_vars($response);
			}

			if(key_exists('errors', $response)) {
				$response = $response['errors'];
			} else if(key_exists('body', $response)) {
				$response = $response['body'];
			}
			$data['response'] = $response;

			$decoded = json_decode($response, true);
			if(json_last_error()) {
				$decoded = false;
			}
			if (false !== $decoded) {
				$data['response_decoded'] = $decoded;
			}
		}

		if(is_array($request) || is_object($request)) {
			if(is_object($request)) {
				$request = get_object_vars($request);
			}
			if(key_exists('method', $request) && $request['method'] === 'POST' && key_exists('body', $request)) {
				$request = $request['body'];
			}
			$data['request'] = $request;
		}

		$fp = fopen($file, 'w+');
		fwrite($fp, json_encode($data));
		fflush($fp);
		fclose($fp);
	}

	public static function get_status(){
		return get_option(self::$option_key, false);
	}

	public function get_logs(){

		$dir = $this->upload_dir;

		$files = glob($dir.'*.json');
		usort($files, function($a,$b) {
			return filemtime($a) > filemtime($b);
		});

		$files = array_map('basename', array_reverse($files));

		return rest_ensure_response(
			array(
				'logs' => $files,
			)
		);
	}

	public function clear(){

		$dir = $this->upload_dir;

		$files = glob($dir.'*.json');
		array_map('unlink', $files);

		return rest_ensure_response(
			array(
				'error' => false,
			)
		);
	}

	public function get_log(\WP_REST_Request $request){
		$file = $request->get_param('file');

		$file = $this->upload_dir.$file;
		if(file_exists($file)) {
			return rest_ensure_response(
				array(
					'log' => file_get_contents($file)
				)
			);
		}

		return rest_ensure_response(
			array(
				'error' => true,
			)
		);

	}


}
