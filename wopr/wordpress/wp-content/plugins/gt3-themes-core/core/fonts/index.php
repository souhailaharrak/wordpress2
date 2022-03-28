<?php

namespace GT3\ThemesCore;

use GT3\ThemesCore\Fonts\Font_Meta;
use WP_REST_Server;

class Fonts {
	private static $instance = null;
	const post_type = 'gt3_custom_font';
	const default   = array(
		'100' => '',
		'200' => '',
		'300' => '',
		'400' => '',
		'500' => '',
		'600' => '',
		'700' => '',
		'800' => '',
		'900' => '',
//		'100i' => '',
//		'200i' => '',
//		'300i' => '',
//		'400i' => '',
//		'500i' => '',
//		'600i' => '',
//		'700i' => '',
//		'800i' => '',
//		'900i' => '',
	);

	const ACTION_ADD    = 'add';
	const ACTION_REMOVE = 'remove';
	const ACTION_NONE   = 'none';

	const DIR = 'gt3_fonts';

	private $upload_path = '';
	private $upload_url  = '';

	private $to_enqueue = array();

	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	private function __construct(){
		add_action('init', array( $this, 'init' ));
		add_action('rest_api_init', array( $this, 'rest_api_init' ));
		add_action('wp_head', array( $this, 'wp_head' ), 8);

		add_filter('elementor/fonts/groups', array( $this, 'elementor_group' ));
		add_filter('elementor/fonts/additional_fonts', array( $this, 'add_elementor_fonts' ));
		add_action("elementor/fonts/print_font_links/".self::post_type, array( $this, 'print_font_links' ));

		$this->create_assets_dir();
	}

	public function print_font_links($font){
		$post = get_page_by_title($font, 'OBJECT', self::post_type);
		if(!is_null($post)) {
			$this->to_enqueue[] = $post->ID;
		}
	}

	public function elementor_group($font_groups){
		return array_merge(
			array(
				self::post_type => __('GT3 Fonts', 'gt3-themes-core')
			), $font_groups
		);
	}

	public function add_elementor_fonts($fonts){
		$_fonts = $this->get_fonts();

		if(count($_fonts)) {
			foreach($_fonts as $_font) {
				$fonts[$_font['title']] = self::post_type;
			}
		}

		return $fonts;
	}

	protected function create_assets_dir(){
		$upload_dir        = wp_upload_dir();
		$this->upload_path = $upload_dir['basedir'].'/'.self::DIR;
		$this->upload_url  = $upload_dir['baseurl'].'/'.self::DIR;

		$this->maybe_create_folder($this->upload_path);
	}

	private function maybe_create_folder($file){
		if(false === stream_resolve_include_path($file) || !is_dir($file)) {
			@mkdir($file);
		}
	}

	public function wp_head(){
		$fonts         = $this->get_fonts();
		$font_face_css = [];
		foreach($this->to_enqueue as $font) {
			$font = $this->get_font($font);
			foreach($font['_fonts'] as $weight => $file_name) {
				$file_path = $this->upload_path.'/'.$font['id'].'/'.$file_name.'.woff2';
				if(!empty($file_name) && stream_resolve_include_path($file_path)) {
					$f               = '@font-face {
					font-family: \''.esc_html($font['title']).'\';
	font-style: normal;
	font-weight: '.$weight.';
	font-display: swap;
	src: url('.$this->upload_url.'/'.$font['id'].'/'.$file_name.'.woff2) format(\'woff2\');
	unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
	}';
					$font_face_css[] = $f;
				}
			}
		}
		if(count($font_face_css)) {
			$font_face_css = implode(PHP_EOL, $font_face_css);
			wp_register_style('gt3-custom-font-handle', false);
			wp_enqueue_style('gt3-custom-font-handle');
			wp_add_inline_style('gt3-custom-font-handle', $font_face_css);
		}
	}

	public function init(){
		register_post_type(
			self::post_type,
			array(
				'label'               => sprintf(__('Fonts', 'gt3pg_pro'), 'GT3'),
				'labels'              => [],
				'with_front'          => false,
				'hierarchical'        => true,
				'show_in_menu'        => false,
				'publicly_queryable'  => false,
				'public'              => false,
				'show_ui'             => false,
				'show_in_rest'        => true,
				'show_in_nav_menus'   => false,
				'capability_type'     => 'page',
				'supports'            => array(
					'title',
				),
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'show_in_admin_bar'   => false,
			)
		);
	}

	public function rest_api_init(){
		register_rest_route(
			'gt3_core/v1/font',
			'create',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_create_font' ),
				)
			)
		);
		register_rest_route(
			'gt3_core/v1/font',
			'remove',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_remove_font' ),
				)
			)
		);
		register_rest_route(
			'gt3_core/v1/font',
			'save',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_save_font' ),
				)
			)
		);
		register_rest_route(
			'gt3_core/v1/font',
			'get',
			array(
				array(
					'methods'             => WP_REST_Server::ALLMETHODS,
					'permission_callback' => function(){
						return current_user_can('administrator');
					},
					'callback'            => array( $this, 'rest_get_font_list' ),
				)
			)
		);
	}

	public function get_fonts(){
		$query = new \WP_Query(
			array(
				'post_type'      => self::post_type,
				'posts_per_page' => '-1',
				'post_status'    => 'any'
			)
		);
		$posts = array();
		if($query->post_count) {
			foreach($query->posts as $post) {
				$_font = $this->get_font($post);
				if(!is_null($_font)) {
					$posts[] = $_font;
				}
			}
		}

		return $posts;
	}

	public function get_font($post_id){
		$post = $post_id;
		if(!($post instanceof \WP_Post)) {
			$post = get_post($post_id);
		}
		if(!($post instanceof \WP_Post)) {
			return null;
		}
		$post_id = $post->ID;
		$fonts   = get_post_meta($post_id, '_fonts', true);
		$_font   = json_decode($fonts, true);

		if(json_last_error()) {
			$fonts = array();
		} else {
			$fonts = $_font;
		}

		foreach(self::default as $weight => $value) {
			$weight         = (string) $weight;
			$fonts[$weight] = isset($fonts[$weight]) ? $fonts[$weight] : self::default[$weight];
		}

		return array(
			'id'     => $post_id,
			'title'  => $post->post_title,
			'_fonts' => $fonts,
		);
	}

	public function rest_get_font_list(){
		$posts = $this->get_fonts();

		return rest_ensure_response($posts);
	}

	public function rest_save_font(\WP_REST_Request $request){

		$id = $request->get_param('id');

		$post = get_post($id);
		if(!($post instanceof \WP_Post)) {
			return rest_ensure_response(
				array(
					'error' => true,
					'msg'   => 'Font not found',
				)
			);
		}

		$changed   = $request->get_param('changed');
		$new_title = $request->get_param('newTitle');

		$post_meta = get_post_meta($id, '_fonts', true);
		$_font     = json_decode($post_meta, true);
		$post_meta = json_last_error() ? array() : $_font;

		if($new_title && $post->post_title !== $new_title) {
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => $new_title,
				)
			);
		}

		if(is_array($changed) && count($changed)) {
			foreach($changed as $weight => $data) {
				$data = array_merge(
					array(
						'name'   => '',
						'file'   => '',
						'action' => self::ACTION_NONE
					), $data
				);

				switch($data['action']) {
					case self::ACTION_ADD:
						$name = $data['name'];
						if(!empty($name) && !empty($data['file'])) {
							$dir             = $this->upload_path.'/'.$id.'/';
							$file_name_ttf   = $dir.'/'.$name.'.ttf';
							$file_name_woff2 = $dir.'/'.$name.'.woff2';
							$this->maybe_create_folder($dir);

							$woff2 = $this->convert_to_woff2($data['file']);

							$content = base64_decode($woff2);
							$this->save_file($file_name_woff2, $content);
							$post_meta[$weight] = $name;
						}
						break;
					case self::ACTION_REMOVE:
						$dir       = $this->upload_path.'/'.$id.'/';
						$file_name = $dir.'/'.$post_meta[$weight].'.ttf';
						$this->remove_file($file_name);
						$file_name = $dir.'/'.$post_meta[$weight].'.woff2';
						$this->remove_file($file_name);
						unset($post_meta[$weight]);
						break;
				}
			}
			update_post_meta($id, '_fonts', json_encode($post_meta));
		}

		return rest_ensure_response(
			array(
				'error' => false,
				'msg'   => 'Saved',
			)
		);
	}

	protected function remove_file($file_name){
		if(file_exists($file_name)) {
			try {
				@unlink($file_name);
			} catch(\Exception $exception) {

			}
		}
	}

	protected function save_file($name, $content){
		$fp = fopen($name, 'w+');
		fwrite($fp, $content);
		fclose($fp);
	}


	public function rest_create_font(\WP_REST_Request $request){
		$title = $request->get_param('title');

		$post_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_status' => 'publish',
				'post_type'   => self::post_type,
			)
		);

		wp_update_post(
			array(
				'ID'        => $post_id,
				'post_name' => 'gt3-custom-font-'.$post_id,
			)
		);

		$_font = $this->get_font($post_id);

		return rest_ensure_response(array( 'font' => $_font ));
	}

	public function rest_remove_font(\WP_REST_Request $request){
		$id = $request->get_param('id');

		$post = get_post($id);

		if(!$post) {
			return rest_ensure_response(array( 'error' => true, 'msg' => 'Font not found.' ));
		}

		wp_delete_post($id);

		return rest_ensure_response(array( 'msg' => 'Removed', 'error' => false ));
	}


	public function convert_to_woff2($ttf_file){
		$form = array(
			'toWoff2' => true,
			'file'    => $ttf_file,
		);

		$response = wp_remote_post(
			'https://livewp.site/woff-converter/',
			array(
				'user-agent'  => 'WordPress/'.esc_url(home_url()),
				'method'      => 'POST',
				'sslverify'   => false,
				'redirection' => 5,
				'body'        => $form
			)
		);
		$code     = wp_remote_retrieve_response_code($response);
		$data     = wp_remote_retrieve_body($response);

		$data = json_decode($data, true);

		if(key_exists('woff2', $data)) {
			return $data['woff2'];
		}

		return false;
	}
}
