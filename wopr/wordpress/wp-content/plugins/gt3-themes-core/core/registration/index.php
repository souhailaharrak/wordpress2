<?php

namespace GT3\ThemesCore;

use GT3\ThemesCore\Registration\Notice_Trait;
use GT3\ThemesCore\Registration\Rest_Trait;
use GT3\ThemesCore\Registration\Update_Trait;
use WP_Error;
use WP_REST_Server;
use WP_REST_Request;

final class Registration {
	use Update_Trait;
	use Rest_Trait;
	use Notice_Trait;
	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private $_nonce_key = 'gt3_dashboard_nonce';


	private $accounts_url = 'https://gt3accounts.com/app/';
	private $service_url  = 'https://gt3accounts.com/update/activate.php';
	private $update_url   = 'https://gt3accounts.com/update/upgrade.php';

	private $theme_id      = 0;
	private $theme         = '';
	private $theme_version = '';
	private $theme_name    = '';
	private $option_key    = '%s_registration';
	private $user_agent    = '';


	private $settings = array(
		'purchase_code'  => '',
		'support_time'   => 0,
		'activated'      => false,
		'already_linked' => false,
		'changeLogVer'   => '0.0',
		'check_update'   => true,
	);

	private $purchase_code  = '';
	private $support_time   = 0;
	private $activated      = false;
	private $already_linked = false;


	private function __construct(){
		global $wp_version;

		$theme    = wp_get_theme();
		$is_child = $theme->get('Template');
		if(!empty($is_child)) {
			$theme = wp_get_theme($is_child);
		}
		$this->theme         = $theme->get_stylesheet();
		$this->theme_version = $theme->get('Version');
		$this->theme_id      = function_exists('gt3_get_product_id') ? intval(gt3_get_product_id()) : 0;
		$this->option_key    = sprintf($this->option_key, $this->theme);
		$this->theme_name    = function_exists('gt3_get_product_name') ? gt3_get_product_name() : '';
		$this->user_agent    = 'WordPress/'.$wp_version.'; '.esc_url(home_url());

		$this->load();

		add_action('rest_api_init', array( $this, 'rest_init' ));

		$this->init_update();
		$this->init_notice();
	}

	public function get_nonce_code(){
		return wp_create_nonce($this->_nonce_key);
	}

	private function migrate(){
		$reg = false;

		if(class_exists('\Redux')) {
			$_redux = \Redux::getOption($this->get_theme_slug(), 'gt3_registration_id');
			if(is_array($_redux) && key_exists('puchase_code', $_redux) && !empty($_redux['puchase_code'])) {
				$reg = $_redux['puchase_code'];
			}

			if(false !== $reg) {
				$reg = array(
					'purchase_code'  => $reg,
					'support_time'   => get_option('gt3_registration_supported_until', ''),
					'activated'      => get_option('gt3_registration_status', '') === 'active',
					'already_linked' => get_option('gt3_account_attached', false),
				);
			}
		}

		return $reg;
	}


	protected function get_accounts_url($type = 'user_check', $ver = 2){
		$url = $this->accounts_url;
		if($ver < 2 && !empty($type)) {
			$url = add_query_arg(
				array(
					$type => 1,
				),
				$url
			);
		}

		return $url;
	}

	protected function get_service_url($type = '', $ver = 1){
		$url = $this->service_url;
		if($ver < 2 && !empty($type)) {
			$url = add_query_arg(
				array(
					$type => 1,
				),
				$url
			);
		}

		return $url;
	}

	protected function get_update_url($type = '', $ver = 1){
		$url = $this->update_url;
		if($ver < 2 && !empty($type)) {
			$url = add_query_arg(
				array(
					$type => 1,
				),
				$url
			);
		}

		return $url;
	}


	private function load(){
		$update = false;
		$opt    = get_option($this->option_key, null);
		if(is_string($opt) && !empty($opt)) {
			try {
				$_data = json_decode($opt, true);
				if(!json_last_error()) {
					$opt = $_data;
				}
			} catch(\Exception $exception) {

			}
		}
		if(!is_array($opt)) {
			$migrate = $this->migrate();

			if(false === $migrate) {
				$opt = array();
			} else {
				$opt    = $migrate;
				$update = true;
			}
		}

		$this->settings = array_merge($this->settings, $opt);

		if($update) {
			$this->save();
		}
	}

	private function save($data = array(), $from_request = false){
		if($from_request) {
			if(key_exists('actions', $data) && is_array($data['actions'])) {
				$data = $data['actions'];
			} else {
				$data = array();
			}
		}
		$this->settings = array_merge($this->settings, $data);
		update_option($this->option_key, json_encode($this->settings));
	}

	protected function get($param, $default = null){
		return key_exists($param, $this->settings) ? $this->settings[$param] : $default;
	}

	protected function set($param, $new_value){
		if(key_exists($param, $this->settings)) {
			$this->settings[$param] = $new_value;
			$this->save();
		}
	}

	protected function fetch_check_user($code = null, $activate = false){
		global $wp_version;

		$url = $this->get_accounts_url('user_check', 1);

		if(is_null($code)) {
			$code = $this->get('purchase_code');
		}

		$response = $this->fetch(
			$url, array(
				'action'        => 'check_code',
				'purchase_code' => $code,
				'try_activate'  => $activate,
			)
		);

		return $response;
	}

	protected function activate_code($code = null){
		$url = $this->get_service_url();
		if(is_null($code)) {
			$code = $this->get('purchase_code');
		}

		$response = $this->fetch(
			$url, array(
				'action'        => 'activate',
				'purchase_code' => $code,
			)
		);

		return $response;
	}

	protected function deactivate_code($code = null){
		$url = $this->get_service_url();

		$response = $this->fetch(
			$url, array(
				'action' => 'deactivate'
			)
		);

		return $response;
	}

	protected function register_user($data = array()){
		$url = $this->get_accounts_url('createnewuser');

		$data = array_merge(
			array(
				'email'         => '',
				'createnewuser' => true
			), $data
		);

		$response = $this->fetch($url, $data);

		return $response;
	}


	private function fetch($url, $data){
		$response = wp_remote_post(
			$url, array(
				'user-agent'  => $this->user_agent,
				'method'      => 'POST',
				'sslverify'   => false,
				'redirection' => 5,
				'body'        => array_merge(
					array(
						'purchase_code'   => $this->get('purchase_code'),
						'product_version' => $this->theme_version,
						'product_id'      => $this->theme_id,
						'product_name'    => $this->theme_name,
						'product_slug'    => $this->theme,
						'ver'             => 2,
						'purchase_url'    => home_url(),
					),
					$data
				),
			)
		);
		$code     = wp_remote_retrieve_response_code($response);
		$data     = wp_remote_retrieve_body($response);
		if(is_string($data)) {
			try {
				$_data = json_decode($data, true);
				if(json_last_error()) {
					return new \WP_Error($code, 'Broken response', $data);
				}
				$data = $_data;

			} catch(\Exception $exception) {
				return new \WP_Error($code, 'Broken response', $data);
			}
		} else {
			return new \WP_Error($code, 'Broken response', $data);
		}

		return $data;
	}

	public function rest_get_settings(){
		return array(
			'registration' => $this->get_settings(),
			'themeVersion' => $this->theme_version,
			'_nonce_reg'   => $this->get_nonce_code(),
			'changeLog'    => $this->get_changelog(true),
			'support_url'  => $this->get_support_url(),
		);
	}

	public function get_settings(){
		return array(
			'activated'      => $this->get('activated'),
			'purchase_code'  => $this->get('purchase_code'),
			'support_time'   => $this->get_support_time_left(),
			'already_linked' => $this->get('already_linked'),
			'check_update'   => $this->get('check_update'),
		);
	}

	public function get_theme_version(){
		return $this->theme_version;
	}

	public function get_theme_slug(){
		return $this->theme;
	}

	public function get_support_time_left(){
		$time_left       = array(
			'expired'            => false,
			'notice_start'       => false,
			'time_to_left'       => '',
			'human_time_to_left' => '',
		);
		$supported_until = $this->get('support_time');
		if(!empty($supported_until)) {
			$supported_until = strtotime($supported_until);
			$current_time    = current_time('timestamp');

			$time_left['expired']      = false;
			$time_left['notice_start'] = false;
			if(($supported_until-$current_time) < (3600*24*7)) {
				$time_left['notice_start'] = true;
			}
			if($supported_until < $current_time) {
				$time_left['expired'] = true;
			}
			$date_format                     = get_option('date_format');
			$time_left['time_to_left']       = date($date_format, $supported_until);//human_time_diff($supported_until, $current_time);
			$time_left['human_time_to_left'] = human_time_diff($supported_until, $current_time);

			return $time_left;
		} else {
			return $time_left;
		}
	}

	public function is_active(){
		return $this->get('activated');
	}

	public function get_support_url(){
		return sprintf('https://themeforest.net/checkout/from_item/%s?license=regular&size=source&support=renew_6month', $this->theme_id);
	}
}
