<?php

use Elementor\Plugin as Elementor_Plugin;

class GT3_Import_Fix_Images {
	protected $real_ids = array();

	protected $media_controls = array();

	public function __construct(){

		$this->get_real_ids_cache();
		if($_REQUEST['content'] == 1) {
			$this->set_real_ids_cache(array());
			echo 'Clear cache.';
		}

		add_action('import_end', array( $this, 'import_end' ));
		add_action('gt3/import/attachment', array( $this, 'import_attachment' ), 10, 2);
		add_filter('gt3/import/post', array( $this, 'import_post' ), 10, 2);
		add_filter('radium_theme_import_widget_settings', array( $this, 'import_widget' ), 10, 2);
		add_filter('radium_theme_import_theme_options', array( $this, 'import_theme_options' ));
	}

	public function import_end(){
		$this->set_real_ids_cache();
	}

	public function import_attachment($post_id, $postmeta){
		if(key_exists('import_id', $postmeta) && $postmeta['import_id'] !== $post_id) {
			$this->set_real_id($post_id, $postmeta['import_id']);
		}
	}

	public function import_post($post, $postdata){
		return $this->fix_elementor_page($post);
	}

	public function import_widget($widget, $widget_name){
		if(in_array($widget_name, array( 'media_image' ))) {
			if(is_object($widget) && property_exists($widget, 'attachment_id')) {
				$widget->attachment_id = $this->get_local_id($widget->attachment_id);
				$widget->url           = wp_get_attachment_image_url($widget->attachment_id, 'full');
			}
		}

		return $widget;
	}

	public function import_theme_options($data){
		if(is_array($data) && count($data)) {
			foreach($data as &$value) {
				if(is_array($value)) {
					if(
						key_exists('url', $value)
						&& key_exists('id', $value)
						&& key_exists('thumbnail', $value)
					) {
						$value['id']  = $this->get_local_id($value['id']);
						$value['url'] = wp_get_attachment_image_url($value['id'], 'full');
						$value['thumbnail']  = wp_get_attachment_image_url($value['id'], 'thumbnail');
					} else if(key_exists('background-image', $value)
					          && key_exists('media', $value)
					) {
						$value['media']['id'] = $this->get_local_id($value['media']['id']);
						$value['media']['thumbnail'] = wp_get_attachment_image_url($value['media']['id'], 'thumbnail');
						$value['background-image'] = wp_get_attachment_image_url($value['media']['id'], 'full');
					}
				}
			}
		}

		return $data;
	}


	protected function get_real_ids_cache(){
		$ids = get_transient('gt3-importer-real-ids');
		if(false !== $ids) {
			$ids = json_decode($ids, true);
		}
		if(is_array($ids)) {
			$this->real_ids = $ids;
		}
		$this->get_media_controls_names();
	}

	protected function set_real_ids_cache($ids = false){
		if(false === $ids) {
			$ids = $this->real_ids;
		}
		set_transient('gt3-importer-real-ids', json_encode($ids), 3600);
		$this->real_ids = $ids;
	}

	protected function get_media_controls_names(){
		if(class_exists('Elementor\Plugin')) {
			$this->media_controls = array(
				Elementor\Controls_Manager::MEDIA,
				Elementor\Controls_Manager::GALLERY,
			);
		}
	}


	protected function set_real_id($local, $real){
		if($local !== $real) {
			$this->real_ids[$real] = $local;
		}
	}

	protected function get_local_id($real){
		return key_exists($real, $this->real_ids) ? $this->real_ids[$real] : $real;
	}

	protected function fix_elementor_page($post){
		if(class_exists('Elementor\Plugin')) {
			if(key_exists('postmeta', $post)) {
				foreach($post['postmeta'] as &$post_meta) {
					if(is_array($post_meta) && $post_meta['key'] == '_elementor_data') {
						$meta = json_decode($post_meta['value'], true);
						if(json_last_error()) {
							$meta = array();
						}

						if(is_array($meta) && count($meta)) {
							foreach($meta as &$level_0) {
								$this->gt3_clear_elementor_tabs_clearMeta($level_0);
							}
						}
						$post_meta['value'] = wp_json_encode($meta);
					}
				}
			}
		}

		return $post;
	}

	protected function gt3_clear_elementor_tabs_clearMeta(&$item){
		if(key_exists('elType', $item) && !in_array($item['elType'], array( 'section', 'column' ))) {
			$this->replace_image_id($item);
		}
		if(key_exists('elements', $item) && is_array($item['elements']) && count($item['elements'])) {
			foreach($item['elements'] as &$element) {
				$this->gt3_clear_elementor_tabs_clearMeta($element);
			}
		}
	}

	protected function replace_image_id(&$item){
		$widget_controls = $this->get_widget_controls($item['widgetType']);
		if(is_array($widget_controls) && count($widget_controls)) {
			foreach($item['settings'] as $control => &$control_settings) {
				if(in_array($control, $widget_controls)) {
					if(key_exists('id', $control_settings)) {
						$_new_id = $this->get_local_id($control_settings['id']);
						if($_new_id !== $control_settings['id']) {
							$control_settings['id']  = $_new_id;
							$control_settings['url'] = wp_get_attachment_image_url($_new_id, 'full');
						}
					} else {
						foreach($control_settings as &$control_setting) {
							$_new_id = $this->get_local_id($control_setting['id']);
							if($_new_id !== $control_setting['id']) {
								$control_setting['id']  = $_new_id;
								$control_setting['url'] = wp_get_attachment_image_url($_new_id, 'full');
							}
						}
					}
				}
			}
		}
	}

	protected function get_widget_controls($widget = false){
		static $widgets = null;

		if(false === $widget) {
			return $widgets;
		}

		if(null !== $widgets && key_exists($widget, $widgets)) {
			return $widgets[$widget];
		}

		$manager  = Elementor_Plugin::instance()->widgets_manager;
		$_widget  = $manager->get_widget_types($widget);
		$controls = is_null($_widget) ? array() : $_widget->get_controls();


		$controls = array_filter(
			$controls, function($control){
			return (key_exists('type', $control) && in_array($control['type'], $this->media_controls));
		}
		);

		$widgets[$widget] = array_keys($controls);

		return $widgets[$widget];
	}

}

new GT3_Import_Fix_Images();
