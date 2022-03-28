<?php

use GT3\ThemesCore\Assets;
use GT3\ThemesCore\Logs;
use GT3\ThemesCore\Fonts;
use GT3\ThemesCore\Customizer;

if(!defined('ABSPATH')) {
	exit;
}

//Variable
$gt3_theme_check          = wp_get_theme();
$gt3_theme_check_template = $gt3_theme_check->get('Template');
$options_name             = !empty($gt3_theme_check_template) ? $gt3_theme_check_template : $gt3_theme_check->get('TextDomain');

define('GT3_THEME_OPTIONS_NAME', $options_name);
define('GT3_CORE_WIDGETS_IMG', plugin_dir_url(__FILE__).'core/elementor/assets/image/');
define('GT3_CORE_URL', plugin_dir_url(__FILE__));

require_once __DIR__.'/core/autoload.php';
Assets::instance();
Logs::instance();
Fonts::instance();

add_action( 'elementor_rest_api_before_init', 'gt3_theme_elementor_rest_init', 99);
add_action('rest_api_init', 'gt3_theme_elementor_rest_init', 99);

function gt3_theme_elementor_rest_init() {
	$namespace = 'elementor/v1/globals/typography';
		register_rest_route(
			$namespace,
			'/(?P<id>[\w-]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => '__return_true',
					'callback'            =>  'gt3_theme_elementor_globals_typography',
				)
			)

		);

	$namespace = 'elementor/v1/globals/colors';

		register_rest_route(
			$namespace,
			'/(?P<id>[\w-]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => '__return_true',
					'callback'            => 'gt3_theme_elementor_globals_colors',
				)
			)
		);
}

/** @param  \WP_REST_Request  $request*/
 function gt3_theme_elementor_globals_typography($request) {
	return 	 \Elementor\Plugin::$instance->data_manager_v2->controllers['globals']->endpoints['globals/typography']->get_item($request->get_param('id'), $request);
}

/** @param  \WP_REST_Request  $request*/
 function gt3_theme_elementor_globals_colors($request) {
	return 	\Elementor\Plugin::$instance->data_manager_v2->controllers['globals']->endpoints['globals/colors']->get_item($request->get_param('id'), $request);

}

// Aq_Resizer
require_once __DIR__.'/core/aq_resizer.php';

//Post type
require_once __DIR__.'/core/cpt/init.php';

//Load meta-box
require_once __DIR__.'/core/meta-box/meta-box.php';
require_once __DIR__.'/core/metabox-addon.php';
require_once __DIR__.'/core/theme-adding-functions.php';
require_once __DIR__.'/core/theme_icons_svg.php';

//Load assets
require_once __DIR__.'/assets/init.php';

/*column-tabs*/
//require_once __DIR__.'/core/fix_elementor/index.php';

