<?php

namespace GT3\ThemesCore;

use Elementor\Plugin;
use GT3\ThemesCore\Assets\Script;
use GT3\ThemesCore\Assets\Style;
use GT3\ThemesCore\Dashboard\Import;
use WP_REST_Server;

use \GT3\ThemesCore\DashBoard\Font_Meta;

class DashBoard {
	private static $instance = null;

	private $theme = null;

	private $requirments = array(
		'php_version'            => '7.3',
		//'max_input_vars'         => 3000,
		'memory_limit'           => 256*1024*1024, // 256M
		//'php_post_max_size'      => 50*1024*1024, // 50M
		'php_max_execution_time' => 600, // 10min
		'php_max_input_vars'     => 3000,
		'max_upload_size'        => 32*1024*1024, // 32M
	);

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		add_action('admin_menu', array( $this, 'admin_menu' ), 9);
		add_action('rest_api_init', array( $this, 'rest_init' ));

		if(!class_exists('WP_Importer')) {
			$class_wp_importer = ABSPATH.'wp-admin/includes/class-wp-importer.php';
			if(file_exists($class_wp_importer)) {
				require $class_wp_importer;
			}
		}

		if(class_exists('WP_Importer')) {
			Import::instance();
		}
		Registration::instance();
		Http_Logs::instance();

		add_action('admin_print_scripts-toplevel_page_gt3_dashboard', array( $this, 'remove_admin_notices' ));

		$theme = wp_get_theme()->get_template();
		add_action("admin_print_scripts-${theme}-theme_page_gt3-demo-import", array( $this, 'remove_admin_notices' ));
	}

	public function remove_admin_notices(){
		remove_all_actions('admin_notices');
	}

	public function rest_init(){
		register_rest_route(
			'gt3_core/v1/dashboard',
			'get_settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_get_settings' ),
				)
			)
		);

		register_rest_route(
			'gt3_core/v1/ticket',
			'create',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_create_ticket' ),
				)
			)
		);


	}

	public function rest_get_settings(){
		return rest_ensure_response(
			array_merge(
				array(
					'system'         => $this->get_system_info(),
					'headerLogo'     => get_template_directory_uri().'/core/admin/img/logo_options.png',
					'httpLogStatus'  => Http_Logs::get_status(),
					'debugLogStatus' => Logs::get_status(),
				),
				Registration::instance()->rest_get_settings()
			)
		);
	}

	public function rest_create_ticket(\WP_REST_Request $request){
		global $wp_version;

		$form = $request->get_param('form');

		if(!is_array($form)) {
			$form = array();
		}

		$form = array_merge(
			array(
				'name'          => '',
				'email'         => '',
				'subject'       => '',
				'message'       => '',
				'purchase_code' => '',
			), $form
		);

		$error = false;
		$msg   = '';

		if(empty($form['name'])) {
			$error = true;
			$msg   = esc_html__('Name field is empty', 'gt3_themes_core');
		} else if(empty($form['email']) || !is_email($form['email'])) {
			$error = true;
			$msg   = esc_html__('Invalid email', 'gt3_themes_core');
		} else if(empty($form['subject'])) {
			$error = true;
			$msg   = esc_html__('Subject field is empty', 'gt3_themes_core');
		} else if(empty($form['message'])) {
			$error = true;
			$msg   = esc_html__('Message field is empty', 'gt3_themes_core');
		} else if(empty($form['purchase_code']) || !$this->is_valid_key($form['purchase_code'])) {
			$error = true;
			$msg   = esc_html__('Invalid purchase code', 'gt3_themes_core');
		}

		if($error) {
			return rest_ensure_response(
				array(
					'error' => true,
					'msg'   => $msg
				)
			);
		}

		$response = wp_remote_post(
			'https://livewp.site/zendesk/',
			array(
				'user-agent'  => 'WordPress/'.$wp_version.'; '.esc_url(home_url()),
				'method'      => 'POST',
				'sslverify'   => false,
				'redirection' => 5,
				'body'        => $form
			)
		);
		$code     = wp_remote_retrieve_response_code($response);
		$data     = wp_remote_retrieve_body($response);

		if($code === 200) {
			$data = json_decode($data, true);
		} else {
			$data = array(
				'error' => true,
				'msg'   => 'Something went wrong, please try again later'
			);
		}

		return rest_ensure_response(
			$data
		);
	}

	private function is_valid_key($code){
		$code       = trim($code, " \t\n\r\0\x0B/");
		$matches    = array();
		$is_matches = preg_match("/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i", $code, $matches);

		return !!$is_matches;
	}


	public function formatBytes($bytes, $precision = 2){
		$base     = log($bytes, 1024);
		$suffixes = array( '', 'K', 'M', 'G', 'T' );

		return round(pow(1024, $base-floor($base)), $precision).' '.$suffixes[floor($base)];
	}

	public function get_system_info(){

		$wp_memory_limit = wp_convert_hr_to_bytes(WP_MEMORY_LIMIT);
		if(function_exists('memory_get_usage')) {
			$wp_memory_limit = max($wp_memory_limit, wp_convert_hr_to_bytes(@ini_get('memory_limit'))); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		$system = array(
			'php_version'            => phpversion(),
			//'max_input_vars'         => ini_get('max_input_vars'),
			'memory_limit'           => $wp_memory_limit,
			//'php_post_max_size'      => wp_convert_hr_to_bytes(ini_get('post_max_size')),
			'php_max_execution_time' => ini_get('max_execution_time'),
			'php_max_input_vars'     => ini_get('max_input_vars'),
			'max_upload_size'        => wp_convert_hr_to_bytes(ini_get('upload_max_filesize')),
		);

		$requirments = array(
			'php_version'            => array(
				'label'    => 'PHP Version:',
				'required' => $this->requirments['php_version'].'+',
				'value'    => $system['php_version'],
				'status'   => version_compare($this->requirments['php_version'], $system['php_version'], '<='),
			),
			'php_max_execution_time' => array(
				'label'    => 'PHP time limit (max_execution_time):',
				'required' => $this->requirments['php_max_execution_time'],
				'value'    => $system['php_max_execution_time'],
				'status'   => (int) $system['php_max_execution_time'] >= (int) $this->requirments['php_max_execution_time'],
			),
			'memory_limit'           => array(
				'label'    => 'PHP memory limit (memory_limit):',
				'required' => $this->formatBytes($this->requirments['memory_limit']),
				'value'    => $this->formatBytes($system['memory_limit']),
				'status'   => (int) $system['memory_limit'] >= (int) $this->requirments['memory_limit'],
			),
			/*'max_input_vars'         => array(
				'label'    => 'max_input_vars',
				'required' => $this->requirments['max_input_vars'],
				'value'    => $system['max_input_vars'],
				'status'   => $system['max_input_vars'] >= $this->requirments['max_input_vars'],
			),*/
			'max_upload_size'        => array(
				'label'    => 'Max upload size (upload_max_filesize):',
				'required' => $this->formatBytes($this->requirments['max_upload_size']),
				'value'    => $this->formatBytes($system['max_upload_size']),
				'status'   => (int) $system['max_upload_size'] >= (int) $this->requirments['max_upload_size'],
			),
			/*'php_post_max_size'      => array(
				'label'    => 'php_post_max_size',
				'required' => $this->requirments['php_post_max_size'],
				'value'    => $system['php_post_max_size'],
				'status'   => $system['php_post_max_size'] >= $this->requirments['php_post_max_size'],
			),*/
			'php_max_input_vars'     => array(
				'label'    => 'PHP Max Input Vars (max_input_vars):',
				'required' => $this->requirments['php_max_input_vars'],
				'value'    => $system['php_max_input_vars'],
				'status'   => (int) $system['php_max_input_vars'] >= (int) $this->requirments['php_max_input_vars'],
			),
		);

		return $requirments;
	}

	public function get_theme(){
		if(!empty($this->theme)) {
			return $this->theme;
		}

		$theme    = wp_get_theme();
		$template = $theme->get_template();
		if($template) {
			$theme = wp_get_theme($template);
		}

		$this->theme = $theme->get_stylesheet();

		return $this->theme;
	}

	public function admin_menu(){
		$theme    = wp_get_theme();
		$template = $theme->get_template();
		if($template) {
			$theme = wp_get_theme($template);
		}
		$name = $theme->get('Name');

		$this->theme = $theme->get_stylesheet();

		add_menu_page(
			sprintf(__('%s Theme'), $name),
			sprintf(__('%s Theme'), $name),
			'administrator',
			'gt3_dashboard',
			false,
			get_template_directory_uri().'/core/admin/img/logo_options.png', // icon
			2 // order
		);

		add_submenu_page(
			'gt3_dashboard',
			__('Dashboard'),
			__('Dashboard'),
			'administrator',
			'gt3_dashboard',
			array( $this, 'view_dashboard' )
		);

		/*$import_page = add_submenu_page(
			'gt3_dashboard',
			__('Demo Import'),
			__('Demo Import').' <span class="update-plugins count-1"><span class="theme-count">1</span></span>',
			'administrator',
			'gt3-demo-import',
			array( $this, 'view_import' )
		);
		add_filter(
			'admin_body_class', function($classes) use ($import_page){
			if(get_current_screen() && get_current_screen()->id === $import_page) {
				if(is_array($classes)) {
					$classes[] = 'gt3_dashboard--gt3-demo-import';
				} else if(is_string($classes)) {
					$classes .= ' gt3_dashboard--gt3-demo-import ';
				}
			}

			return $classes;
		}
		);*/
	}

	public function view_dashboard(){
		wp_enqueue_script('block-library');
		wp_enqueue_script('editor');
		wp_enqueue_script('wp-editor');
		wp_enqueue_script('wp-components');

		wp_enqueue_style('wp-components');
		wp_enqueue_style('wp-element');
		wp_enqueue_style('wp-blocks-library');

		Script::enqueue_core_asset('admin/dashboard');
		Style::enqueue_core_asset('admin/dashboard');

		$locale  = $this->get_jed_locale_data('gt3_theme_core');
		$content = ';document.addEventListener("DOMContentLoaded", function(){window.wp && wp.i18n && wp.i18n.setLocaleData('.json_encode($locale).', "gt3_themes_core" );});';

		wp_script_add_data('gt3-core/admin/dashboard', 'data', $content);

		$translation_array = array(
			'themeVersion'  => Registration::instance()->get_theme_version(),
			'customizerUrl' => wp_customize_url(),
			'kitUrl'        => \Elementor\Plugin::instance()->kits_manager->get_active_kit()->get_edit_url(),
		);
		wp_localize_script('gt3-core/admin/dashboard', 'gt3_dashboard_data', $translation_array);
		?>

		<div id="dashboard-wrapper"></div>
		<?php

	}

	/*	public function view_import(){
		wp_enqueue_script('block-library');
		wp_enqueue_script('editor');
		wp_enqueue_script('wp-editor');
		wp_enqueue_script('wp-components');

		wp_enqueue_style('wp-components');
		wp_enqueue_style('wp-element');
		wp_enqueue_style('wp-blocks-library');

		Script::enqueue_core_asset('admin/import');
		Style::enqueue_core_asset('admin/import');

		echo '<div id="dashboard-wrapper"></div>';
	}*/

	function get_jed_locale_data($domain){
		$translations = get_translations_for_domain($domain);

		$locale = array(
			'' => array(
				'domain' => $domain,
				'lang'   => is_admin() ? get_user_locale() : get_locale(),
			),
		);

		if(!empty($translations->headers['Plural-Forms'])) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach($translations->entries as $msgid => $entry) {
			$locale[$msgid] = $entry->translations;
		}

		return $locale;
	}


}
