<?php

namespace GT3\ThemesCore\Assets;

use Elementor\Widget_Base;
use ElementorModal\Widgets\GT3_Core_Widget_Base;

class Image extends Asset {
	protected        $sub_folder = 'img';
	protected        $ext        = 'css';
	protected static $instance   = null;

	protected function path_to_url($a, $asset){
		$path_asset = $this->register[$asset]['path'];
		$path_file  = stream_resolve_include_path(str_replace(wp_normalize_path(ABSPATH), '', dirname($path_asset)).'/'.$a);

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

	protected function init(){
		$this->init_params();
	}

	public static function get_file($file){
		$self = self::instance();

		$path = $self->asset_core['path'].$file;
		if(file_exists($path)) {
			$url = $self->asset_core['url'].$file;

			return $url;
		}

		return '';


	}
}
