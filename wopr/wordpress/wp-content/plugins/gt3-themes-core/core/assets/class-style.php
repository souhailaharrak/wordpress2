<?php

namespace GT3\ThemesCore\Assets;

use Elementor\Plugin as Elementor_Plugin;
use Elementor\Widget_Base;
use ElementorModal\Widgets\GT3_Core_Widget_Base;
use GT3\ThemesCore\Customizer;

class Style extends Asset {
	protected        $sub_folder  = 'css';
	protected        $ext         = 'css';
	protected        $option_name = 'optimize_css';
	protected static $instance    = null;

	/**
	 * @param Widget_Base $widget
	 *
	 * @return array
	 */
	protected static function get_depends($widget){
		$depts = array();
		if(is_object($widget) && ($widget instanceof GT3_Core_Widget_Base)) {
			if(method_exists($widget, 'get_main_script_depends')) {
				$depts = array_merge($depts, $widget->get_main_script_depends());
			} else if(method_exists($widget, 'get_style_depends')) {
				$depts = array_merge($depts, $widget->get_style_depends());
			}
		}
		return $depts;
	}

	protected static function enqueue_wp_asset($handle, $url = '', $deps = array(), $ver = false){
		wp_enqueue_style($handle, $url, $deps, $ver);
	}

	public function process_header(){
		parent::process_header();

		if(!$this->isMinimized) {
			return false;
		}

		$assets = $this->enqueued;
		if(!(is_array($assets) && count($assets))) {
			return false;
		}

		list($hash, $path) = $this->asset_get();

		if(false === $path) {
			$path = $this->upload_dir['path'].$hash.'.'.$this->ext;
			static::save_file($assets, $path, '', '');
		}
		$this->done();
		wp_enqueue_style('gt3-assets-header', $this->upload_dir['url'].$hash.'.'.$this->ext, array(), filemtime($path), 'all');

		return true;
	}

	public function process_footer(){
		parent::process_footer();
		if(!$this->isMinimized) {
			return false;
		}

		$assets = $this->enqueued;
		if(!(is_array($assets) && count($assets))) {
			return false;
		}

		list($hash, $path) = $this->asset_get('footer');

		if(false === $path) {
			$path = $this->upload_dir['path'].$hash.'.'.$this->ext;
			static::save_file($assets, $path, '', '');
		}

		wp_enqueue_style('gt3-assets-footer', $this->upload_dir['url'].$hash.'.'.$this->ext, array(), filemtime($path), 'all');
		$this->done();

		return true;
	}

	protected function process_file($content, $asset){
		$matches = array();
		preg_match_all("/url\(\s*['\"]?(?!data:)(?!http)(?![\/'\"])(.+?)['\"]?\s*\)/ui", $content, $matches);
		foreach($matches[1] as $a) {
			$content = str_replace(trim($a), $this->path_to_url($a, $asset), $content);
		}

		return $content;
	}

	protected function path_to_url($a, $asset){
		if($asset instanceof \_WP_Dependency) {
			$asset = $asset->handle;
		}

		if(key_exists($asset, $this->register)) {
			$path_asset = $this->register[$asset]['path'];
		} else if(key_exists($asset, $this->global_registered)) {
			$path_asset = $this->global_registered[$asset]->src;
		} else {
			return '';
		}

		$path_asset = $this->convert_url_to_path($path_asset);
		$file_full  = str_replace(wp_normalize_path(ABSPATH), '', dirname($path_asset)).'/'.$a;
		$path_file  = stream_resolve_include_path(dirname($file_full)).'/'.basename($file_full);

		return $this->abs_path_to_url($path_file);
	}

	function abs_path_to_url($path = ''){
		$url = str_replace(
			wp_normalize_path(untrailingslashit(ABSPATH)),
			site_url(),
			wp_normalize_path($path)
		);

		return esc_url_raw($url);
	}

	function convert_url_to_path($url){
		$try = str_replace(
			home_url('/'),
			ABSPATH,
			$url
		);

		$name = basename($try);
		if (false !== strpos($name, '?')) {
			$_name = explode('?', $name);
			$_name = $_name[0];
			$try = str_replace($name, $_name, $try);
		}

		if(file_exists($try) && is_file($try) && is_readable($try)) {
			return stream_resolve_include_path($try);
		}

		$try = ABSPATH.$url;
		if(file_exists($try) && is_file($try) && is_readable($try)) {
			return stream_resolve_include_path($try);
		}

		return null;
	}

	protected function init(){
		parent::init();
			add_action('wp_print_styles', array( $this, 'wp_print_styles' ), 50);
	}

	public function wp_print_styles(){
		global $wp_styles;

		if (!(!is_admin() && !!Customizer::instance()->get_option('optimize_merge_all_css')) ) {
			return;
		}

		$this->global_registered = &$wp_styles->registered;
		$this->global_queue      = [];

		$wp_styles->all_deps($wp_styles->queue);

		$hash = [];
		foreach($wp_styles->to_do as $key => $handle) {
			$asset = $this->global_registered[$handle];
			if (empty($asset->src) && key_exists('after', $asset->extra) && count($asset->extra['after'])) {
				$hash[$handle] = $handle.'-'.md5($key.$handle);
				$wp_styles->done[] = $handle;
				unset($wp_styles->to_do[$key]);
				continue;
			}
			$path  = $this->convert_url_to_path($asset->src);
			if(null !== $path) {
				$hash[$handle] = $handle.'-'.filemtime($path);
				$wp_styles->done[] = $handle;
				unset($wp_styles->to_do[$key]);
			}
		}
		$hash_file = md5(implode(',-,', $hash));
		$hash_file = $this->upload_dir['path'].$hash_file.'.css';

		if(!file_exists($hash_file)) {
			$fp = fopen($hash_file, 'w+');
			if(!$fp) {
				return;
			}

			foreach($hash as $handle => $ver) {
				$asset = $this->global_registered[$handle];
				$path  = $this->convert_url_to_path($asset->src);

				fwrite($fp, PHP_EOL.'/*-'.$handle.'-*/'.PHP_EOL);

				if (!empty($path) && is_file($path) && is_readable($path)) {
					$FH   = fopen($path, "r");
					$line = $this->process_file(fgets($FH), $asset);
					while($line !== false) {
						fputs($fp, $line);
						$line = $this->process_file(fgets($FH), $asset);
					}
					fclose($FH);
				}

				$output = $wp_styles->get_data( $handle, 'after' );

				if ( !empty( $output ) ) {
					$output = implode( "\n", $output );
					fputs($fp, PHP_EOL.'/*inline style - '.$handle.'*/'.PHP_EOL);
					fputs($fp, $output);
				}
			}
			fclose($fp);
		}

		wp_enqueue_style('gt3-combined', $this->abs_path_to_url($hash_file), array(), filemtime($hash_file));

	}

	protected function add_global_asset($asset){
		if(is_string($asset) && key_exists($asset, $this->global_registered)) {
			$asset = $this->global_registered[$asset];
		}
		if(!($asset instanceof \_WP_Dependency)) {
			return;
		}

		if(count($asset->deps)) {
			foreach($asset->deps as $style) {
				if(!key_exists($style, $this->global_registered)) {
					continue;
				}
				$asset = $this->global_registered[$style];
				$this->add_global_asset($asset);
			}
		}
		if(!in_array($style, $this->global_queue)) {
			$this->global_queue[] = $asset->handle;
		}
	}


}
