<?php

namespace GT3\ChartElementor;

/**
 * @property string $VERSION Version
 * @property string $NAME Name
 * @property string $FILE File Path
 */
class Loader {
	private static $instance = null;
	private $required_plugin = 'elementor/elementor.php';
	private $required_plugin_name = 'Elementor';
	public $VERSION = '1.0';
	private $NAME = 'Plugin Name';
	private $require_php = '5.6';
	private $FILE = 'gt3-elementor-unlimited-charts.php';

	public static function instance(){
		if(!self::$instance instanceof self) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct(){
		$this->actions();

		$this->FILE = dirname(__FILE__).'/'.$this->FILE;

		if(!function_exists('get_plugin_data')) {
			require_once(ABSPATH.'wp-admin/includes/plugin.php');
		}
		$plugin_info   = get_plugin_data($this->FILE);
		$this->VERSION = $plugin_info['Version'];
		$this->NAME    = $plugin_info['Name'];
		define('GT3_ChartElementor_VERSION',$plugin_info['Version']);
	}

	private function actions(){
		add_action('plugins_loaded', array( $this, 'plugins_loaded' ));

	}

	function _is_plugin_installed(){
		$installed_plugins = get_plugins();

		return isset($installed_plugins[$this->required_plugin]);
	}

	public function plugins_loaded(){
		if(!did_action('elementor/loaded')) {
			add_action('admin_notices', array( $this, 'elementor_not_loaded' ));
		} else {
			require_once __DIR__.'/plugin.php';
			load_plugin_textdomain('gt3_unlimited_chart', false, dirname(plugin_basename(__FILE__)).'/languages/');
		}
	}

	public function fail_php_version(){
		$message      = sprintf(esc_html__('%1$s requires PHP version %2$s+, plugin is currently NOT ACTIVE.', 'gt3_unlimited_chart'), $this->NAME, $this->require_php);
		$html_message = sprintf('<div class="error">%s</div>', wpautop($message));
		echo wp_kses_post($html_message);
	}

	public function elementor_not_loaded(){
		$screen = get_current_screen();
		if(isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
			return;
		}

		if($this->_is_plugin_installed()) {
			if(!current_user_can('activate_plugins')) {
				return;
			}

			$activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin='.$this->required_plugin.'&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_'.$this->required_plugin);

			$message = '<p>'.sprintf(esc_html__('%1$s is not working because you need to activate the %2$s plugin.', 'gt3_unlimited_chart'), $this->NAME, $this->required_plugin_name).'</p>';
			$message .= sprintf('<p><a href="%s" class="button-primary">%s</a></p>', $activation_url, sprintf(esc_html__('Activate %s Now', 'gt3_unlimited_chart'), $this->required_plugin_name));
		} else {
			if(!current_user_can('install_plugins')) {
				return;
			}

			$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin='.dirname($this->required_plugin)), 'install-plugin_'.dirname($this->required_plugin));

			$message = '<p>'.sprintf(esc_html__('%1$s is not working because you need to install the %2$s plugin.', 'gt3_unlimited_chart'), $this->NAME, $this->required_plugin_name).'</p>';
			$message .= '<p>'.sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, sprintf(esc_html__('Install %s Now', 'gt3_unlimited_chart'), $this->required_plugin_name)).'</p>';
		}

		echo '<div class="error"><p>'.$message.'</p></div>';
	}

	public function __get($name){
		return property_exists($this, $name) ? $this->$name : null;
	}

	public function __set($name, $value){
		// TODO: Implement __set() method.
	}
}

Loader::instance();

