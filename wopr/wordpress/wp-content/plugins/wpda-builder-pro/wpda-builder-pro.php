<?php
/**
 ** Plugin Name: WP Daddy Builder Pro
 ** Plugin URI: https://wpdaddy.com/
 ** Description: WP Daddy Builder Pro
 ** Version: 1.2.6
 ** Author: WP Daddy
 ** Author URI: https://wpdaddy.com/
 ** Text Domain: wpda-builder-pro
 ** Domain Path:  /languages
 **/


defined('ABSPATH') OR exit;
global $wp_version;

if(!function_exists('get_plugin_data')) {
	require_once(ABSPATH.'wp-admin/includes/plugin.php');
}
$plugin_info = get_plugin_data(__FILE__);
define('WPDA_PRO_HEADER_BUILDER__VERSION', $plugin_info['Version']);
define('WPDA_PRO_HEADER_BUILDER__FILE', __FILE__);

if(!version_compare(PHP_VERSION, '7.3', '>=')) {
	add_action('admin_notices', 'wpda_hb_pro__fail_php_version');
} else if(!version_compare($wp_version, '5.3', '>=')) {
	add_action('admin_notices', 'wpda_hb_pro__fail_wp_version');
} else {
	add_action('plugins_loaded', 'wpda_hb_pro__plugins_loaded');
}

function wpda_hb_pro__plugins_loaded(){
	if(!did_action('elementor/loaded')) {
		add_action('admin_notices', 'wpda_hb_pro__fail_elementor_loader');

		return;
	}
	require_once __DIR__.'/core/support.php';
	require_once __DIR__.'/core/autoload.php';
	require_once __DIR__.'/core/dom/autoload.php';

	add_action('elementor/init', array( WPDaddy\Builder\Init::class, 'instance' ));
	add_action('init', 'wpda_hb_pro__load_textdomain');

	WPDaddy\Builder\Registration::instance();
}

function wpda_hb_pro__load_textdomain(){
	load_plugin_textdomain('wpda-builder', false, dirname(plugin_basename(__FILE__)).'/languages/');
}

function wpda_hb_pro__fail_php_version(){
	$message      = sprintf('WP Daddy Builder Pro requires PHP version %1$s+, plugin is currently NOT ACTIVE.', '7.3');
	$html_message = sprintf('<div class="error">%s</div>', wpautop($message));
	echo wp_kses_post($html_message);
}

function wpda_hb_pro__fail_elementor_loader(){
	$screen = get_current_screen();
	if(isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
		return;
	}

	$installed_plugins = get_plugins();

	$_is_elementor_installed = isset($installed_plugins['elementor/elementor.php']);

	if($_is_elementor_installed) {
		if(!current_user_can('activate_plugins')) {
			return;
		}

		$activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=elementor/elementor.php&amp;plugin_status=all&amp;paged=1&amp;', 'activate-plugin_elementor/elementor.php');

		$message = '<p>'.esc_html('WP Daddy Builder requires Elementor plugin to be activated.').'</p>';
		$message .= sprintf('<p><a href="%s" class="button-primary">%s</a></p>', $activation_url, esc_html__('Activate Elementor'));
	} else {
		if(!current_user_can('install_plugins')) {
			return;
		}

		$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor');

		$message = '<p>'.esc_html('WP Daddy Builder requires Elementor plugin to be installed.').'</p>';
		$message .= '<p>'.sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, esc_html__('Install Elementor')).'</p>';
	}

	echo '<div class="error"><p>'.$message.'</p></div>';
}

function wpda_hb_pro__fail_wp_version(){
	$message      = sprintf('WP Daddy Builder Pro requires WordPress version %1$s+, plugin is currently NOT ACTIVE.', '5.0');
	$html_message = sprintf('<div class="error">%s</div>', wpautop($message));
	echo wp_kses_post($html_message);
}

register_activation_hook(__FILE__, 'wpda_hb_pro__activation_hook');

function wpda_hb_pro__activation_hook(){
	update_option('elementor_disable_color_schemes', 'yes');
	update_option('elementor_disable_typography_schemes', 'yes');
}
