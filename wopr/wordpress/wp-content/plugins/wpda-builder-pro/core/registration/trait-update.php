<?php

namespace WPDaddy\Builder\Registration;

use DateTime;
use WP_Error;
use WPDaddy\Builder\Registration;

trait Update_Trait {
	private $cron_name = 'wpda_builder_license';

	protected function init_update(){
		$enabled = $this->get('check_update', false);
		if($enabled) {
			add_filter('pre_set_site_transient_update_plugins', array( $this, 'check_plugins_update' ), 0);
		}

		add_action($this->cron_name, array( $this, 'cron_action' ));

		if(!wp_next_scheduled($this->cron_name)) {
			$datetime = new DateTime( 'now', wp_timezone() );
			$datetime->modify('+1 Hour');
			wp_schedule_single_event($datetime->getTimestamp(), $this->cron_name);
			do_action($this->cron_name);
		}
	}

	public function cron_action(){
		$data = $this->fetch_check_user();

		if(is_wp_error($data)) {
			/** @var WP_Error $data */
			$data = array(
				'error'   => true,
				'respond' => $data->get_error_message()
			);
		}

		$actions = array(
			'purchase_code'  => '',
			'support_time'   => '',
			'activated'      => false,
			'already_linked' => false,
		);

		if(key_exists('actions', $data) && is_array($data['actions'])) {
			$actions = array_merge($actions, $data['actions']);
			if($actions['already_linked']) {
				$this->save($data, true);
			}
		}
	}

	public function rest_set_update(\WP_REST_Request $request){
		$update = $request->get_param('check_update');

		$this->set('check_update', (bool) $update);

		return rest_ensure_response(
			array(
				'success' => true,
				'respond' => 'Saved',
				'data'    => array(
					'check_update' => $update,
				)
			)
		);
	}

	public function check_plugins_update($transient){
		static $loaded = false;

		$slug = $this->product_slug;
		global $wp_version;
		$file_path = sprintf('%1$s/%1$s.php', $slug);

		if(!isset($transient->checked) || !isset($transient->response) || !empty($transient->response[$slug])) {
			return $transient;
		}

		$url = $this->get_service_url();

		$response = $this->fetch(
			$url, array(
				'action' => Registration::ACTION_CHECK_UPDATE,
			)
		);

		if(is_wp_error($response)) {
			return $transient;
		}

		$this->save($response, true);

		$response = array_merge(
			array(
				'allow_update' => false,
				'transient'    => array(
					'changelog'   => '',
					'new_version' => '0.0.1',
					'package'     => false,
					'url'         => '',
					'icons'       => array(),
					'banners'     => array(),
				)
			),
			$response
		);

		if(!$response['allow_update']) {
			return $transient;
		}

		$_transient = (array) $response['transient'];

		if(isset($response['allow_update']) && $response['allow_update']
		   && version_compare($transient->checked[$file_path], $_transient['new_version'], '<')
		   && false !== $_transient['package']) {

			$_transient['package']           = sprintf('%1$s?action=upgrade&ut=%2$s', $this->get_service_url(), $response['transient']['package']);
			$transient->response[$file_path] = (object) $_transient;
		}

		return $transient;
	}

	protected function modify_changelog($content){
		if(!empty($content)) {
			$pattern = array(
				'/(\*\*\*)(.+)(\*\*\*)/',
				'/(\=\=)(.+)(\=\=)/',
				'/(\*)/'
			);
			$replace = array( '<h1>${2}</h1>', '</br><h2>${2}</h2>', '</br>&#9642;' );
			$content = preg_replace($pattern, $replace, $content);
		}

		return $content;
	}
}
