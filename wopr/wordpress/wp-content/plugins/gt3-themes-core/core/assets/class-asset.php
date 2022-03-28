<?php

namespace GT3\ThemesCore\Assets;

use Elementor\Plugin as Elementor_Plugin;
use Elementor\Widget_Base;
use ElementorModal\Widgets\GT3_Core_Widget_Base;
use GT3\ThemesCore\Assets;

abstract class Asset {
	/** @var self */
	protected static $instance = null;

	protected $is_child_theme = false;
	protected $is_preview     = false;
	protected $register       = array();
	protected $enqueued       = array();
	protected $done           = array();

	protected $sub_folder  = '';
	protected $ext         = '';
	protected $option_name = 'optimization';
	protected $upload_dir  = array();

	const DIR   = 'gt3-assets';
	const CORE  = 'gt3-core';
	const THEME = 'gt3-theme';
	const CHILD = 'gt3-child';

	protected $global_queue = array();
	protected $global_registered = array();
	protected $global_done = array();

	protected $isMinimized = false;

	protected $asset_core = array(
		'path' => '',
		'js'   => '',
	);

	protected $asset_theme = array(
		'path' => '',
		'js'   => '',
	);

	protected $asset_child = array(
		'path' => '',
		'js'   => '',
	);

	/** @return \GT3\ThemesCore\Assets\Asset */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function get_reg(){
		return $this->register;
	}

	private function __construct(){
		add_action('elementor/init',function(){
			$this->init();
		});

		$this->init_params();
		$this->create_assets_dir();
	}

	protected function create_assets_dir(){
		$this->upload_dir = wp_upload_dir();

		$this->upload_dir['basedir'] .= '/'.static::DIR;
		$this->upload_dir['baseurl'] .= '/'.static::DIR;
		$this->upload_dir['path']    = sprintf('%s/%s/', $this->upload_dir['basedir'], $this->sub_folder);
		$this->upload_dir['url']     = sprintf('%s/%s/', $this->upload_dir['baseurl'], $this->sub_folder);

		$this->maybe_create_folder();
	}

	private function maybe_create_folder(){
		$path = $this->upload_dir['basedir'];
		if(false === stream_resolve_include_path($path) || !is_dir($path)) {
			@mkdir($path);
		}
		$path = $this->upload_dir['path'];
		if(false === stream_resolve_include_path($path) || !is_dir($path)) {
			@mkdir($path);
		}
	}

	protected function init(){
		static $started = false;
		if($started) {
			return;
		}
		$started = true;


		add_action('wp_enqueue_scripts', array( $this, 'process_header' ), 8);
		add_action('wp_print_footer_scripts', array( $this, 'process_footer' ), 5);
		add_action('elementor/elements/elements_registered', array( $this, 'elements_registered' ), 0);
	}

	public function elements_registered(){
		$enabled = (is_singular() && !!gt3_option($this->option_name));
		if (!!gt3_option('optimize_merge_all_css')) {
			$enabled = false;
		}

		$this->is_preview  = class_exists('Elementor\Plugin') && (Elementor_Plugin::instance()->preview->is_preview() || Elementor_Plugin::instance()->editor->is_edit_mode());
		$this->isMinimized = $this->is_preview ? false : $enabled;
	}


	protected function init_params(){
		$this->is_child_theme = (get_stylesheet_directory() !== get_template_directory());

		$this->asset_core['path'] = sprintf('%sdist/%s/', plugin_dir_path(GT3_THEMES_CORE_PLUGIN_FILE), $this->sub_folder);
		$this->asset_core['url']  = sprintf('%sdist/%s/', plugin_dir_url(GT3_THEMES_CORE_PLUGIN_FILE), $this->sub_folder);

		$this->asset_theme['path'] = sprintf('%sdist/%s/', trailingslashit(get_parent_theme_file_path()), $this->sub_folder);
		$this->asset_theme['url']  = sprintf('%sdist/%s/', trailingslashit(get_parent_theme_file_uri()), $this->sub_folder);

		if($this->is_child_theme) {
			$this->asset_child['path'] = sprintf('%sdist/%s/', trailingslashit(get_stylesheet_directory()), $this->sub_folder);
			$this->asset_child['url']  = sprintf('%sdist/%s/', trailingslashit(get_stylesheet_directory_uri()), $this->sub_folder);
		}
	}

	public static function register_widget($widget_name, $depts = array()){
		$self = static::$instance;
		if ($depts === true) {
			$depts = array();
			if (false !== strpos($widget_name, '/')) {
				list($folder, $_name) = explode('/', $widget_name);
				if (class_exists('Elementor\Plugin')) {
					$widget = Elementor_Plugin::instance()->widgets_manager->get_widget_types($_name);
					if($widget instanceof GT3_Core_Widget_Base) {
						$depts = $widget;
					}
				}
			}
		}
		if(is_object($depts)) {
			/** @var Widget_Base $widget */
			$widget = $depts;

			$depts = static::get_depends($widget);
		}

		$self->register_core_asset($widget_name, $depts);
		$self->register_theme_asset($widget_name, $depts);
		if($self->is_child_theme) {
			$self->register_child_asset($widget_name, $depts);
		}
	}

	public static function enqueue_widget($widget_name){
		$self = static::$instance;
		static::register_widget($widget_name);

		$self->enqueue_asset(static::CORE.'/'.$widget_name);
		$self->enqueue_asset(static::THEME.'/'.$widget_name);
		if($self->is_child_theme) {
			$self->enqueue_asset(static::CHILD.'/'.$widget_name);
		}
	}


	/**
	 * @param Widget_Base $widget
	 *
	 * @return array
	 */
	protected static function get_depends($widget){
		return array();
	}

	public static function register_core_asset($handle, $deps = array()){
		$self = static::$instance;

		$name   = $self->get_file_name($handle);
		$handle = static::CORE.'/'.$handle;
		$path   = stream_resolve_include_path($self->asset_core['path'].$name);
		$url    = $self->asset_core['url'].$name;

		static::register_asset($handle, $deps, $url, $path);
	}

	public static function register_theme_asset($handle, $deps = array()){
		$self = static::$instance;

		$name   = $self->get_file_name($handle);
		$handle = static::THEME.'/'.$handle;
		$path   = stream_resolve_include_path($self->asset_theme['path'].$name);
		$url    = $self->asset_theme['url'].$name;

		static::register_asset($handle, $deps, $url, $path);
	}

	public static function register_child_asset($handle, $deps = array()){
		$self   = static::$instance;
		$name   = $self->get_file_name($handle);
		$handle = static::CHILD.'/'.$handle;

		$path = stream_resolve_include_path($self->asset_child['path'].$name);
		$url  = $self->asset_child['url'].$name;

		static::register_asset($handle, $deps, $url, $path);
	}

	public static function enqueue_core_asset($handle, $deps = array(), $url = ''){
		$self = static::$instance;

		if(!in_array($handle, $self->done) && !in_array($handle, $self->enqueued)) {
			$self = static::$instance;

			if(false !== $url || !key_exists(static::CORE.'/'.$handle, $self->register)) {
				static::register_core_asset($handle, $deps);
			}

			$self->enqueue_asset(static::CORE.'/'.$handle);
		}

	}

	public static function enqueue_theme_asset($handle, $deps = array(), $url = false){
		$self = static::$instance;

		if(!in_array($handle, $self->done) && !in_array($handle, $self->enqueued)) {
			$self = static::$instance;

			if(false !== $url || !key_exists(static::THEME.'/'.$handle, $self->register)) {
				static::register_theme_asset($handle, $deps);
			}

			$self->enqueue_asset(static::THEME.'/'.$handle);
		}

	}

	public static function enqueue_child_asset($handle, $deps = array(), $url = ''){
		$self = static::$instance;

		if(!in_array($handle, $self->done) && !in_array($handle, $self->enqueued)) {
			$self = static::$instance;

			if(false !== $url || !key_exists(static::CHILD.'/'.$handle, $self->register)) {
				static::register_child_asset($handle, $deps);
			}

			$self->enqueue_asset(static::CHILD.'/'.$handle);
		}

	}

	public static function ContentUrlToLocalPath($path){
		if(stream_resolve_include_path($path)) {
			return $path;
		}

		$pos = strpos($path, 'wp-content/');
		if(false !== $pos) {
			return stream_resolve_include_path(ABSPATH.substr($path, $pos));
		}

		return false;
	}

	public static function register_asset($handle, $deps = array(), $url = '', $path = false){
		$self = static::instance();
		if(key_exists($handle, $self->register)) {
			return;
		}

		if(false === stream_resolve_include_path($path)) {
			$path = static::ContentUrlToLocalPath($url);
		}

		if(false === $path) {
			return;
		}
		$ver = filemtime($path);

		$self->register[$handle] = array(
			'url'  => $url,
			'deps' => $deps,
			'ver'  => $ver,
			'path' => $path,
		);
	}

	public function enqueue_asset($handle, $deps = array(), $url = false, $path = false){

		if(!in_array($handle, $this->done) && !in_array($handle, $this->enqueued)) {
			if(false !== $url || !key_exists($handle, $this->register)) {
				static::register_asset($handle, $deps, $url, $path);
			}

			if(!key_exists($handle, $this->register)) {
				return;
			}
			$asset = $this->register[$handle];

			foreach($asset['deps'] as $slug) {
				if(key_exists($slug, $this->register)) {
					static::enqueue_asset($slug);
				} else {
					static::enqueue_wp_asset($slug);
				}
			}
			if($this->is_preview || !$this->isMinimized) {
				static::enqueue_wp_asset($handle, $asset['url'], array(), $asset['ver']);
			} else {
				$this->enqueued[] = $handle;
			}

		}
	}

	protected static function enqueue_wp_asset($handle, $url = '', $deps = array(), $ver = false){

	}

	public function process_header(){
		if($this->is_preview) {
			$this->enqueue_all();
		}
	}

	public function process_footer(){
		if($this->is_preview) {
			$this->enqueue_all();
		}
	}

	public function hash(){
		$name = array();
		foreach($this->enqueued as $asset) {
			$ver = $this->register[$asset]['ver'];
			$name[] = $asset.'~'.$ver;
		}
		$name = md5(implode('::', $name));

		return $name;
	}

	public function save_file($assets, $path, $before = '', $after = ''){
		if(!$this->isMinimized) {
			return;
		}
		$stream = fopen($path, 'w+');
		if(false === $stream) {
			trigger_error("Can't create file", E_USER_NOTICE);

			return;
		}
		foreach($assets as $asset) {
			$f = $this->register[$asset]['path'];
			$FH = fopen($f, "r");
			fputs($stream, $before);
			fputs($stream, PHP_EOL.'/* '.$asset.'*/'.PHP_EOL);
			$line = $this->process_file(fgets($FH), $asset);
			while($line !== false) {
				fputs($stream, $line);
				$line = $this->process_file(fgets($FH), $asset);
			}
			fputs($stream, $after);
			fclose($FH);
		}
		fclose($stream);
	}

	public function get_file_name($handle){
		return strtolower($handle).'.'.$this->sub_folder;
	}

	protected function enqueue_all(){
		foreach($this->register as $item => $value) {
			$this->enqueue_asset($item);
		}
	}

	protected function remove_file($path){
		if(stream_resolve_include_path($path)) {
			@unlink($path);
		}
	}

	/**
	 * @param string       $type
	 * @param \WP_Post|int $post
	 *
	 * @return array
	 */
	protected function asset_get($type = 'header', $post = false){
		if('header' !== $type) {
			$type = 'footer';
		}
		if(false === $post) {
			if(is_singular()) {
				$post = get_post();
				$post = $post->ID;
			}
		}
		$meta_key = sprintf('_gt3_%s_file_%s', $type, $this->ext);

		$data = get_post_meta($post, $meta_key, true);
		$hash = $this->hash();
		$path = $this->upload_dir['path'].$hash.'.'.$this->ext;
		if($data === $hash) {
			return array( $hash, static::ContentUrlToLocalPath($path) );
		}

		$this->remove_file($this->upload_dir['path'].$data.'.'.$this->ext);

		update_post_meta($post, $meta_key, $hash);

		return array( $hash, false );
	}

	protected function done(){
		$this->done     = array_merge($this->done, $this->enqueued);
		$this->enqueued = array();
	}

	protected function process_file($content, $asset){
		return $content;
	}

	public function clear_cache() {
		array_map('unlink', glob($this->upload_dir['path']."*.".$this->ext));
	}
}
