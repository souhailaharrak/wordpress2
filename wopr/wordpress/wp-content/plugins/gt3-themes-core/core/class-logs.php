<?php

namespace GT3\ThemesCore;

use Elementor\Core\Logger\Items\PHP;
use WP_REST_Server;
use WP_REST_Request;

defined('ABSPATH') or exit;

class Logs {
	private static $instance = null;

	private static $option_key = 'gt3_logs';
	private        $upload_dir = '';
	private static $status     = true;

	private static $errors = array(
		1     => 'E_ERROR',
		2     => 'E_WARNING',
		4     => 'E_PARSE',
//		8     => 'E_NOTICE',
		16    => 'E_CORE_ERROR',
		32    => 'E_CORE_WARNING',
		64    => 'E_COMPILE_ERROR',
		128   => 'E_COMPILE_WARNING',
		256   => 'E_USER_ERROR',
		512   => 'E_USER_WARNING',
//		1024  => 'E_USER_NOTICE',
		2048  => 'E_STRICT',
		4096  => 'E_RECOVERABLE_ERROR',
		8192  => 'E_DEPRECATED',
//		16384 => 'E_USER_DEPRECATED',
//		30719 => 'E_ALL'
	);

	/** @return Logs */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		$upload_dir       = wp_upload_dir();
		$this->upload_dir = trailingslashit($upload_dir['basedir']).'gt3-logs/';
		$this->maybe_create_folder($this->upload_dir);

		self::$status = get_option(self::$option_key, self::$status);

		add_action('rest_api_init', array( $this, 'rest_init' ));
		if(self::$status) {
//			ini_set('display_errors', 0);
			set_error_handler(array( $this, 'error_handler' ));
			register_shutdown_function(array( $this, 'shutdown_handler' ));
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
		$namespace = 'gt3_core/v1/logs';

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

	public static function get_status(){
		self::instance();

		return self::$status;
	}

	public function get_logs(){

		$dir = $this->upload_dir;

		$files = glob($dir.'*.log');
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

		$files = glob($dir.'*.log');
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

	public function error_handler($errno, $message, $file = null, $line = null){
		$error = array(
			'type'    => $errno,
			'message' => $message,
			'file'    => $file,
			'line'    => $line
		);

		$this->save_log($error);
	}

	public function shutdown_handler(){
		$error = error_get_last();

		if(is_null($error)) {
			return;
		} else if(!is_array($error)) {
			$error = array();
		}

		$error = array_merge(
			array(
				'type'    => -1,
				'message' => '',
				'file'    => '',
				'line'    => -1,
			), $error
		);

		$this->save_log($error);
	}

	public function save_log($error, $backtrace = false){
		$error = array_merge(
			array(
				'type'    => -1,
				'message' => '',
				'file'    => '',
				'line'    => -1,
			), $error
		);

		if(key_exists($error['type'], self::$errors)) {
			$name = date('d.m.y').'.log';
			$fp = fopen($this->upload_dir.$name, 'a+');
			fwrite($fp, date('\[d-m-Y H:i:s\]').' Error '.self::$errors[$error['type']].': '.$error['message'].' in '.$error['file'].':'.$error['line'].PHP_EOL);
			fflush($fp);
			fclose($fp);
		}
	}

	function get_file_url($file = __FILE__){
		$file = str_replace(wp_normalize_path(WP_CONTENT_DIR), "",wp_normalize_path($file));

		if($file) {
			return content_url($file);
		}

		return false;
	}
}
