<?php

namespace WPDaddy\Builder;

use WPDaddy\Builder\Registration\Notice_Trait;
use WPDaddy\Builder\Registration\Rest_Trait;
use WPDaddy\Builder\Registration\Update_Trait;

final class Registration {
	use Update_Trait;
	use Rest_Trait;
	use Notice_Trait;

	const ACTION_CODE_CHECK   = 'code_check';
	const ACTION_CODE_STATUS  = 'code_status';
	const ACTION_USER_CREATE  = 'user_create';
	const ACTION_ACTIVATE     = 'activate';
	const ACTION_DEACTIVATE   = 'deactivate';
	const ACTION_CHECK_UPDATE = 'check_update';
	const ACTION_UPDATE       = 'update';

	private static $instance = null;

	/** @return self */
	public static function instance(){
		if(is_null(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private $_nonce_key = 'wpda_registration_nonce';

	private $service_url = 'https://accounts.wpdaddy.com/app/';

	private $product_id      = 32876391;
	private $product_version = '';
	private $product_slug    = '';
	private $option_key      = '%s_registration';
	private $user_agent      = '';

	private $settings = array(
		'purchase_code'  => '',
		'support_time'   => 0,
		'activated'      => false,
		'already_linked' => false,
		'check_update'   => true,
		'changeLogVer'   => '0.0',
	);

	private $purchase_code  = '';
	private $support_time   = 0;
	private $activated      = false;
	private $already_linked = false;

	private function __construct(){
		global $wp_version;

		$plugin_file = WPDA_PRO_HEADER_BUILDER__FILE;

		if(!function_exists('get_plugin_data')) {
			require_once(ABSPATH.'wp-admin/includes/plugin.php');
		}

		$plugin_info = get_plugin_data($plugin_file);

		$this->product_version = $plugin_info['Version'];
		$this->product_slug    = $plugin_info['TextDomain'];
		$this->option_key      = sprintf($this->option_key, $this->product_slug);
		$this->user_agent      = 'WordPress/'.$wp_version.'; '.esc_url(home_url());

		add_action('rest_api_init', array( $this, 'rest_init' ));
		add_action('after_setup_theme', array($this, 'after_setup_theme'));
	}

	public function get_nonce_code(){
		return wp_create_nonce($this->_nonce_key);
	}

	protected function get_service_url(){
		return $this->service_url;
	}

	public function after_setup_theme() {
		$this->load();
		$this->init_update();
		$this->init_notice();
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
				$opt = array();
			}
		}
		if(!is_array($opt)) {
			$opt = array();
		}


		if($update) {
			$this->save();
		}
		$this->settings = apply_filters('wpda-builder/registration/settings', array_merge($this->settings, $opt));

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
		$url = $this->get_service_url();

		if(is_null($code)) {
			$code = $this->get('purchase_code');
		}

		return $this->fetch(
			$url, array(
				'action'        => self::ACTION_CODE_CHECK,
				'purchase_code' => $code,
				'try_activate'  => $activate,
			)
		);
	}

	protected function activate_code($code = null){
		$url = $this->get_service_url();
		if(is_null($code)) {
			$code = $this->get('purchase_code');
		}

		return $this->fetch(
			$url, array(
				'action'        => self::ACTION_ACTIVATE,
				'purchase_code' => $code,
			)
		);
	}

	protected function deactivate_code($code = null){
		$url = $this->get_service_url();

		return $this->fetch(
			$url, array(
				'action' => self::ACTION_DEACTIVATE
			)
		);
	}

	protected function register_user($data = array()){
		$url = $this->get_service_url();

		$data = array_merge(
			array(
				'email'  => '',
				'action' => self::ACTION_USER_CREATE,
			), $data
		);

		return $this->fetch($url, $data);
	}


	private function fetch($url, $data){
		$response = wp_remote_post(
			$url, array(
				'timeout'     => 30,
				'user-agent'  => $this->user_agent,
				'method'      => 'POST',
				'sslverify'   => false,
				'redirection' => 5,
				'body'        => array_merge(
					array(
						'purchase_code'   => $this->get('purchase_code'),
						'product_item_id' => $this->product_id,
						'product_slug'    => $this->product_slug,
						'product_version' => $this->product_version,
						'api_ver'         => 2,
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
			'themeVersion' => $this->product_version,
			'_nonce_reg'   => $this->get_nonce_code(),
			'support_url'  => $this->get_support_url(),
		);
	}

	public function get_settings(){
		return array(
			'activated'        => $this->get('activated'),
			'purchase_code'    => $this->get('purchase_code'),
			'support_time'     => $this->get_support_time_left(),
			'already_linked'   => $this->get('already_linked'),
			'check_update'     => $this->get('check_update'),
			'activatedByTheme' => $this->get('activatedByTheme'),
		);
	}

	public function get_product_version(){
		return $this->product_version;
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
			$time_left['time_to_left']       = date($date_format, $supported_until);
			$time_left['human_time_to_left'] = human_time_diff($supported_until, $current_time);

			return $time_left;
		} else {
			return $time_left;
		}
	}

	public static function active() {
		$self = self::instance();
		return $self->get('activated');

	}

	public function is_active(){
		return $this->get('activated');
	}

	public function get_support_url(){
		return sprintf('https://codecanyon.net/checkout/from_item/%s?license=regular&size=source&support=renew_6month', $this->product_id);
	}
}
