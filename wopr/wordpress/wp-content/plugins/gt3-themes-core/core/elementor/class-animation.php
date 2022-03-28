<?php

namespace GT3\ThemesCore\Elementor;

use GT3\ThemesCore\Assets\Style;

final class Animation {
	private static $instance = null;

	/** @return \GT3\ThemesCore\Assets */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public $animations = array();
	public $keys       = array();


	private function __construct(){
		$this->init_animations();

		add_filter('elementor/controls/animations/additional_animations', array( $this, 'additional_animations' ));
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_styles' ));

		add_action('elementor/element/before_section_end', array( $this, 'fix_conditions' ), 20, 3);
	}

	public function fix_conditions($element, $section_id, $args){
		if($section_id !== 'section_effects') {
			return;
		}

		$this->fix_control_conditions($element, '_animation_delay');
		$this->fix_control_conditions($element, 'animation_delay');
		$this->fix_control_conditions($element, 'animation_duration');
	}

	/**
	 * @param \Elementor\Widget_Base $element
	 * @param string                 $control_name
	 */
	protected function fix_control_conditions($element, $control_name){
		$control = $element->get_controls($control_name);
		if(is_array($control) && key_exists('condition', $control)) {
			$condition = ($control['condition']);
			reset($condition);
			$key       = key($condition);

			$condition[$key] = array_merge((array) $condition[$key], $this->keys);
			$element->update_control(
				$control_name, array(
					'condition' => $condition
				)
			);
		}
	}

	private function init_animations(){
		$this->animations = apply_filters(
			'gt3/core/elementor/animations', array(
				'gt3-slideup-animation' => esc_html__('Slide Up', 'gt3_themes_core'),
			)
		);

		$this->keys = array_keys($this->animations);
	}

	public function additional_animations($animations){
		return array_merge(
			$animations,
			array(
				'GT3' => $this->animations,
			)
		);
	}

	public function enqueue_styles(){
		Style::enqueue_core_asset('animations');
	}
}
