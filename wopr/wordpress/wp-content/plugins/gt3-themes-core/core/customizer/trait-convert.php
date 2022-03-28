<?php

namespace GT3\ThemesCore\Customizer;

use GT3\ThemesCore\DashBoard;

trait Convert_Trait {
	private $old_options       = array();
	private $converted_options = array();
	private $new_options       = array();

	private $current_key   = '';
	private $current_value = '';
	private $current_args  = '';

	protected function get_customizer_fields(){
		return array();
	}

	protected function convert(){
		$theme             = DashBoard::instance()->get_theme();
		$this->old_options = get_option($theme, false);
		if(false === $this->old_options) {
			return array();
		}
		if (did_action('elementor/init')) {
			$this->convert_elementor();
		} else add_action('elementor/init', array($this, 'convert_elementor'));

		$this->converted_options = array();

		$this->new_options = apply_filters('gt3/core/customizer/convert_fields',$this->get_customizer_fields());

		foreach($this->new_options as $key => $type) {
			$this->current_key = $key;
			$new_value         = key_exists($key, $this->old_options) ? $this->old_options[$key] : null;
			$new_value         = $this->convert_field($type, $new_value);

			if(null !== $new_value) {
				$this->converted_options[$key] = $new_value;
			}
		}

		return 	$this->converted_options;
	}

	public function convert_elementor() {
		Elementor::convert_fields($this->old_options);
//		do_action('gt3/core/customizer/elementor/convert', $this->old_options);
	}

	/**
	 * @return array
	 */

	private function convert_field($type, $value, $args = array()){
		$new_value = null;

		switch($type) {
			case Types::TYPE_INT:
				$new_value = $this->convert_int($value);
				break;
			case Types::TYPE_FLOAT:
				$new_value = $this->convert_float($value);

				break;
			case Types::TYPE_BOOL:
				$new_value = $this->convert_bool($value);
				break;
			case Types::TYPE_ARRAY:
				$field = key_exists('field', $args) ? $args['field'] : null;
				if (!is_array($value)) {
					$value = array();
				}

				if(null !== $field) {
					$new_value = $this->convert_array($value, $field);
				} else {
					$new_value = $value;
				}

				if (key_exists('filter_func', $args) && is_callable($args['filter_func'])) {
					$new_value = call_user_func($args['filter_func'], $new_value);
				}

				break;
			case Types::TYPE_STRING:
				$new_value = $this->convert_string($value);
				break;
			case Types::TYPE_IMAGE:
				$new_value = $this->convert_image($value);
				break;
			case Types::TYPE_BACKGROUND:
				$field = $args['field'];

				$new_value = key_exists($field, $this->old_options) ? $this->old_options[$field] : null;
				$new_value = $this->convert_background($new_value);

				foreach($new_value as $key => $value) {
					$this->converted_options[$field.'_'.$key] = $value;
				}

				$new_value = null;

				break;
			case Types::TYPE_COLOR:
				$new_value = $this->convert_color($value);
				break;
			default:
				if(is_array($type)) {
					$fn = key_exists('fn', $type) ? $type['fn'] : null;
					if(null !== $fn) {
						$new_value = $this->convert_field($fn, $value, $type['args']);
					}
				}
				break;
		}

		return $new_value;
	}

	/** @return int */
	private function convert_int($value){
		return intval($value);
	}

	/** @return float */
	private function convert_float($value){
		return floatval($value);
	}

	/** @return  boolean */
	private function convert_bool($value){
		return in_array($value, array( 'on', 'yes', 'true' ), true) ? true :
			(in_array($value, array( 'off', 'no', 'false' ), true) ? false :
				(bool) $value);
	}

	private function convert_image($value){
		$args = array_merge(
			array(
				"url"         => false,
				"id"          => "",
				"height"      => "",
				"width"       => "",
				"thumbnail"   => false,
				"title"       => "",
				"caption"     => "",
				"alt"         => "",
				"description" => "",
			), (array) $value
		);

		$id = '';

		if(isset($args['id']) && !empty($args['id'])) {
			$id = intval($args['id']);
		}

		return $id;
	}

	private function convert_background($value){
		$args = array_merge(
			array(
				"background-repeat"     => "",
				"background-size"       => "",
				"background-attachment" => "",
				"background-position"   => "",
				"background-image"      => "",
				"media"                 => array(
					"id"        => "",
					"height"    => "",
					"width"     => "",
					"thumbnail" => "",
				)
			), (array) $value
		);

		$value = array(
			'repeat'     => $args['background-repeat'],
			'size'       => $args['background-size'],
			'attachment' => $args['background-attachment'],
			'position'   => $args['background-position'],
			'image'      => $args['media']['id'],
		);

		return $value;
	}

	/** @return string */
	private function convert_string($value){
		return $value;//strval($value);
	}

	private function convert_array($arr, $key = null){
		if (!is_array($arr)) {
			$arr = array();
		}
		return key_exists($key, $arr) ? $arr[$key] : null;
	}

	/** @return string */
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
