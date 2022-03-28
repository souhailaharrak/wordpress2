<?php

namespace GT3\ThemesCore\Customizer;

use Elementor\Core\Settings\Manager as SettingsManager;
use Elementor\Core\Settings\Page\Manager as PageManager;
use Elementor\Plugin;

class Elementor {
	private static $instance     = null;
	private        $settings     = array();
	private        $compiled     = array();
	private        $defaults     = array();
	private        $kits_manager = null;
	private        $active_kit   = null;
	private        $globals      = array();

	const DEVICE_ALL     = 'all';
	const DEVICE_DESKTOP = 'desktop';
	const DEVICE_TABLET  = 'tablet';
	const DEVICE_MOBILE  = 'mobile';


	/** @return self */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		$this->defaults = apply_filters('gt3/core/customizer/elementor/defaults', $this->defaults);
		if(!class_exists('Elementor\Plugin')) {
			$this->settings = array_merge_recursive($this->settings, $this->defaults);
			$this->globals  = (array) $this->settings['__globals__'];
			return;
		}
		$this->kits_manager = Plugin::instance()->kits_manager;
		$this->active_kit   = $this->kits_manager->get_active_kit();

		$this->settings = array_merge_recursive($this->settings, $this->kits_manager->get_active_kit_for_frontend()->get_settings());
		$this->globals  = (array) $this->settings['__globals__'];

		$this->check_fields();
	}

	public function get_settings($field = false, $device = self::DEVICE_ALL){
		if(false !== $field) {
			return $this->get_setting($field, $device);
		}

		return $this->settings;
	}

	public function get_setting($setting, $device = self::DEVICE_ALL, $settings = null){
		$self_settings = false;
		if(null === $settings) {
			$settings = $this->settings;
			$self_settings = true;
		}

		$value = null;

		if ($self_settings) {
			foreach([ 'colors', 'typography' ] as $suffix) {
				$_key   = $setting.'_'.$suffix;
				$global = false;
				if(key_exists($setting, $this->globals)) {
					$global = $this->globals[$setting];
				} else if(key_exists($_key, $this->globals)) {
					$global = $this->globals[$_key];
				}

				if(false !== $global && preg_match('#globals/(\w+)\?id=([-_a-z]+)#i', $global, $matches)) {
					list($str, $key, $subkey) = $matches;
					switch($key) {
						case 'colors':
							$value = $this->_get_repeater_setting('system_colors', $subkey);
							$value = $value['color'];
							break;
						case 'typography':
							$value = $this->_get_repeater_setting('system_typography', $subkey);
							$value = $this->get_setting('typography', $device, $value);
							break;
					}
				}
			}
		}

		if(null !== $value) {
			return $value;
		}

		if (key_exists('system_colors', $settings)) {
			$value = $this->get_key_from_array($settings['system_colors'], $setting);
			if (false !== $value) {
				return $settings['system_colors'][$value]['color'];
			}
		}

		if(key_exists($setting, $settings)) {
			return $settings[$setting];
		}

		$value  = array();
		$length = strlen($setting)+1;
		foreach($settings as $setting_key => $setting_value) {
			if(0 === strpos($setting_key, $setting)) {
				$value[substr($setting_key, $length)] = $setting_value;
			}
		}

		return $this->extract_setting($value, $setting, $device);
	}

	/** @param string|array $key */
	public function set_setting($key, $value = null, $owerride = true){
		if(is_array($key) && is_null($value)) {
			foreach($key as $k => $v) {
				if(!is_null($value)) {
					$k = $value.'_'.$k;
				}
				$this->set_setting($k, $v);
			}
			return;
		}
		if(!key_exists($key, $this->settings) || (key_exists($key, $this->settings) && $owerride)) {
			$this->settings[$key] = $value;
		}
	}

	/** @param string|array $key */
	public function set_array_setting($values, $key = null){
		foreach($values as $k => $v) {
			if(!is_null($key)) {
				$k = $key.'_'.$k;
			}
			$this->set_setting($k, $v);
		}
	}


	public function save_settings(){
		$settings_ = $this->settings;

		$document_settings = $this->active_kit->get_meta(PageManager::META_KEY);

		if(!$document_settings) {
			$document_settings = [];
		}

		$settings_             = array_replace_recursive($document_settings, $settings_);
		$page_settings_manager = SettingsManager::get_settings_managers('page');
		$page_settings_manager->save_settings($settings_, $this->active_kit->get_id());
	}

	public function extract_setting($value, $key, $device = self::DEVICE_ALL){
		foreach(array( 'tablet', 'mobile' ) as $dev) {
			$temp = array();
			foreach($value as $setting_key => $setting_value) {
				$pos = strrpos($setting_key, $dev);
				if(false !== $pos) {
					$temp[substr($setting_key, 0, $pos-1)] = $setting_value;
					unset($value[$setting_key]);
				}
			}
			if(count($temp)) {
				$value[$dev] = $temp;
			}
		}

		switch($device) {
			case self::DEVICE_DESKTOP:
				unset($value[self::DEVICE_MOBILE]);
				unset($value[self::DEVICE_TABLET]);
				break;
			case self::DEVICE_MOBILE:
			case self::DEVICE_TABLET:
				$device_value = $value[$device];
				unset($value[self::DEVICE_MOBILE]);
				unset($value[self::DEVICE_TABLET]);

				foreach($device_value as $setting_key => $setting_value) {
					$value[$setting_key] = $setting_value;
				}

				break;
			default:
				break;
		}

		return $value;
	}

	public static function compress_settings($setting, $device = self::DEVICE_ALL){
		return self::instance()->get_setting($setting, $device);
	}

	public static function get_repeater_setting($setting, $subkey, $settings = null){
		$self = self::instance();

		return $self->_get_repeater_setting($setting, $subkey, $settings);
	}

	public function _get_repeater_setting($setting, $need_value, $settings = null){
		if(is_null($settings)) {
			$settings = $this->get_setting($setting);
		}
		$value = $this->get_key_from_array($settings, $need_value, '_id');
		$value = (false === $value) ? null : $settings[$value];

		return $value;
	}

	protected function get_key_from_array($settings, $need_value, $id = '_id'){
		return array_search($need_value, array_column($settings, $id));
	}

	public static function convert_fields($old){
		$self = self::instance();


		$fields_to_convert = $self->get_convert_fields();
		$changed           = false;
		foreach($fields_to_convert as $key => $new_field) {
			if(!key_exists($key, $old)) {
				continue;
			}
			$old_value = $old[$key];


			if(is_array($new_field)) {


				foreach($new_field as $type => $field) {
					switch($type) {
						case 'font':
							$new_value = $self->convert_font($old_value);
							if(is_array($field)) {
								$elementor_value  = $self->get_setting($field['field']);
								$new_value['_id'] = $field['id'];
								$key_exists       = $self->_get_repeater_setting($field['field'], $field['id']);
								$index = $self->get_key_from_array($self->settings[$field['field']], $field['id'], '_id');
								if(false === $index) {
									$index = $self->get_key_from_array($self->settings[$key], 'primary');
									if(false === $index) {
										$index = 0;
									}
									array_splice($elementor_value, $index, 0, array( $new_value ));
								} else {
									$elementor_value[$index] = $new_value;
								}
								$self->set_setting($field['field'], $elementor_value);
							} else {
								$self->set_array_setting($new_value, $field);
							}
							$changed = true;
							break;
						case 'color':
							$new_value = $self->convert_color($old_value);

							if(is_array($field)) {
								$elementor_value = $self->get_setting($field['field']);
								$key_exists      = $self->_get_repeater_setting($field['field'], $field['id']);
								$index = $self->get_key_from_array($self->settings[$field['field']], $field['id'], '_id');
								$new_value       = array(
									'_id'   => $field['id'],
									'title' => ucfirst($field['id']),
									'color' => $new_value,
								);
								if(false === $index) {
									$index = $self->get_key_from_array($self->settings[$key], 'primary');
									if(false === $index) {
										$index = 0;
									}
									array_splice($elementor_value, $index, 0, array( $new_value ));
								} else {
									$elementor_value[$index] = $new_value;
								}
								$self->set_setting($field['field'], $elementor_value);
							} else {
								$self->set_setting($field, $new_value);
							}
							$changed = true;
							break;
					}
				}
			}
		}

		if($changed) {
			$self->save_settings();
		}
	}

	public static function value_are_equal($value1, $value2){
		if(gettype($value1) !== gettype($value2)) {
			return false;
		}
		if(is_array($value1) && is_array($value2)) {
			array_multisort($value1);
			array_multisort($value2);
		}

		return (serialize($value1) === serialize($value2));
	}

	private function check_fields(){
		$changed = false;
		foreach($this->defaults as $key => $value) {
			$control = $this->active_kit->get_controls($key);
			if(is_null($control)) {
				continue;
			}
			$elementor_defaults = key_exists('default', $control) ? $control['default'] : null;

			switch($control['type']) {
				case 'global-style-repeater':
					// Global Typography, Colors
					foreach($value as $item => $item_value) {
						$_id   = $item_value['_id'];
						$exist = $this->_get_repeater_setting($item, $_id, $this->settings[$key]);
						if(is_null($exist)) {
							/** not exist */
							$index = $this->get_key_from_array($this->settings[$key], 'primary');
							if(false === $index) {
								$index = 0;
							}
							array_splice($this->settings[$key], $index, 0, array( $item_value )); // before primary
						} else {
							/** exist */
							$elementor_default = $this->_get_repeater_setting($item, $_id, $elementor_defaults);
							if(self::value_are_equal($elementor_default, $exist)) {
								$exist = array_merge($exist, $item_value);

								$this->settings[$key][$this->get_key_from_array($this->settings[$key], $_id)] = $exist;
							}
						}
						$changed = true;
					}
					break;
				case 'popover_toggle':
					if(key_exists('groupType', $control)) {
						switch($control['groupType']) {
							case 'typography':
								$is_default = true;
								foreach($value as $_key => $_value) {
									$_key     = $control['groupPrefix'].$_key;
									$_control = $this->active_kit->get_controls($_key);
									if(!self::value_are_equal($_control['default'], $this->settings[$_key])) {
										$is_default = false;
										break;
									}
								}
								if($is_default) {
									foreach($value as $_key => $_value) {
										$_key = $control['groupPrefix'].$_key;
										$this->set_setting($_key, $_value);
									}
									$changed = true;
								}

								break;
						}
					}
					break;
				default:
					$elementor_default = $elementor_defaults;//[$key];
					if(self::value_are_equal($elementor_default, $this->settings[$key])) {
						$this->settings[$key] = $value;
						$changed              = true;
					}
					break;
			}
		}

		if(key_exists('__globals__', $this->defaults)) {
			$_defaults = array();

			foreach($this->defaults['__globals__'] as $_key => $_value) {
				$_control = $this->active_kit->get_controls($_key);
				if(
					!key_exists($_key, $this->globals)
					&& key_exists($_key, $this->settings)
					&& $_control && key_exists('default', $_control)
				) {
					if(self::value_are_equal($_control['default'], $this->settings[$_key])) {
						$_defaults[$_key] = $_value;
					}
				}

			}

			if(count($_defaults)) {
				$this->globals = array_merge(
					(array) $this->globals,
					$_defaults
				);
				$this->set_setting('__globals__', $this->globals);
				$changed = true;
			}
		}

		if($changed) {
			$this->save_settings();
		}
	}

	protected function get_convert_fields(){
		return apply_filters('gt3/core/customizer/elementor/convert_fields', array());
	}

	private function convert_font($value){
		if(!is_array($value)) {
			$value = (array) $value;
		}

		$old_font = array_merge(
			array(
				"font-family"    => "",
				"font-options"   => "",
				"google"         => "",
				"font-weight"    => "",
				"font-style"     => "",
				"font-all-eight" => "",
				"subsets"        => "",
				"font-size"      => "",
				"line-height"    => "",
				"color"          => "",
				"text-transform" => "",
				"letter-spacing" => "",
			), $value
		);

		$font = array(
			"typography"      => "custom",
			"font_family"     => $old_font['font-family'],
			"font_size"       => array(
				"unit"  => "px",
				"size"  => "",
				"sizes" => array()
			),
			"font_weight"     => $old_font['font-weight'],
			"text_transform"  => $old_font['text-transform'],
			"font_style"      => $old_font['font-style'],
			"text_decoration" => "", // underline,
			"line_height"     => array(
				"unit"  => "px",
				"size"  => "",
				"sizes" => array()
			),
			"letter_spacing"  => array(
				"unit"  => "px",
				"size"  => "",
				"sizes" => array()
			)
		);

		$matched = preg_match('#(\d+)#', $old_font['font-size'], $match);
		if($matched) {
			$font['font_size']['size'] = $match[0];
		}

		$matched = preg_match('#(\d+)#', $old_font['line-height'], $match);
		if($matched) {
			$font['line_height']['size'] = $match[0];
		}

		$matched = preg_match('#(\d+)#', $old_font['letter-spacing'], $match);
		if($matched) {
			$font['letter_spacing']['size'] = $match[0];
		}

		return $font;
	}

	private function convert_color($value){
		switch(gettype($value)) {
			case 'array':
				$value = key_exists('rgba', $value) ? $value['rgba'] : (key_exists('color', $value) ? $value['color'] : '');
				break;
			case 'string':
				break;
			default:
				$value = '';
				break;
		}

		return $value;
	}

}
