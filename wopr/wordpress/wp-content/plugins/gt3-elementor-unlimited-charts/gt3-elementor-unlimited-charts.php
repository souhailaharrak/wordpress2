<?php
/*
	Plugin Name: GT3 Ultimate Charts for Elementor Page Builder
	Plugin URI: https://gt3themes.com/
	Description: Flexible and easy to use charts for Elementor page builder. Visualize your data in a cool way. Unlimiteted possibilities.
	Version: 1.0.3
	Author: GT3 Themes
	Author URI: https://gt3themes.com/
*/
function gt3_elementor_unlimited_chart__meta_links( $meta_fields, $file ) {
	if ( plugin_basename( __FILE__ ) == $file ) {
		$stars_color = "#ffb900";

		echo "<style>"
		     . ".gt3-rate-stars{display:inline-block;color: #ffb900;position:relative;top:3px;}"
		     . ".gt3-rate-stars svg{fill: #ffb900;}"
		     . ".gt3-rate-stars svg:hover{fill: #ffb900}"
		     . ".gt3-rate-stars svg:hover ~ svg{fill:none;}"
		     . "</style>";
	}

	return $meta_fields;
}

add_filter( "plugin_row_meta", 'gt3_elementor_unlimited_chart__meta_links', 10, 2 );


if(!version_compare(PHP_VERSION, '5.6', '>=')) {
	add_action('admin_notices', 'gt3_elementor_unlimited_chart__fail_php_version');
	return;
} else {
	require_once __DIR__.'/init.php';
}

function gt3_elementor_unlimited_chart__fail_php_version() {
	$message      = sprintf('GT3 Unlimited Charts requires PHP version %1$s+, plugin is currently NOT ACTIVE.', '5.6');
	$html_message = sprintf('<div class="error">%s</div>', wpautop($message));
	echo wp_kses_post($html_message);
}
add_action('plugins_loaded', 'gt3_elementor_unlimited_chart_plugins_loaded');
function gt3_elementor_unlimited_chart_plugins_loaded(){
	load_plugin_textdomain('gt3_unlimited_chart', false, __DIR__.'/languages/');
}
