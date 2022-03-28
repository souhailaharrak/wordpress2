<?php

namespace GT3\ThemesCore\Assets;

use Elementor\Widget_Base;
use ElementorModal\Widgets\GT3_Core_Widget_Base;

class Script extends Asset {
	protected        $sub_folder  = 'js';
	protected        $ext         = 'js';
	protected        $option_name = 'optimize_js';
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
			} else if(method_exists($widget, 'get_script_depends')) {
				$depts = array_merge($depts, $widget->get_script_depends());
			}
		}

		return $depts;
	}

	protected static function enqueue_wp_asset($handle, $url = '', $deps = array(), $ver = false){
		wp_enqueue_script($handle, $url, $deps, $ver, true);
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
			static::save_file($assets, $path, ';try{', '}catch(e){console.warn(e)};');
		}
		$this->done();
		$content = ';window.resturl = window.resturl || "'.get_rest_url().'";';

		wp_enqueue_script('gt3-assets-footer', $this->upload_dir['url'].$hash.'.'.$this->ext, array(), filemtime($path), true);
		wp_script_add_data('gt3-assets-footer', 'data', $content);

		return true;
	}
}
