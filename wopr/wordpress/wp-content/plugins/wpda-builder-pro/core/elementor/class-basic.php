<?php

namespace WPDaddy\Builder\Elementor;

if(!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Modules;
use WPDaddy\Builder\Library\Basic as Basic_Library;

abstract class Basic extends Widget_Base {

	public function get_categories(){
		$category = 'wpda_builder_hidden';

		$editor   = Plugin::$instance->editor->is_edit_mode();
		$document = \Elementor\Plugin::instance()->documents->get_current();
		if($editor) {
			$post = get_post();
			if($post->post_type === 'elementor_library' &&
			   ($document instanceof Basic_Library)) {
				$category = 'wpda_builder';
			}
		}

		return array( $category );
	}

	public function start_controls_section($section_id, array $args = []){
//		$section_id   .= '_section';
		$default_args = array(
			'condition' => apply_filters('wpda-builder/elementor/start_controls_section/'.$section_id, [])
		);
		$args         = array_merge($default_args, $args);
		parent::start_controls_section($section_id, $args);
	}

	/**
	 * @param array $data
	 * @param null  $args
	 *
	 * @throws \Exception
	 */
	public function __construct(array $data = array(), $args = null){
		parent::__construct($data, $args);

		$this->construct();
	}

	protected function construct(){
	}

	public function get_repeater_key($setting_key, $repeater_key, $repeater_item_index){
		return $this->get_repeater_setting_key($setting_key, $repeater_key, $repeater_item_index);
	}

	protected function register_controls(){
		do_action('wpda-builder/elementor/register_control/before/'.$this->get_name(), $this);
		$this->init_controls();
		do_action('wpda-builder/elementor/register_control/after/'.$this->get_name(), $this);
	}

	// php
	protected function render(){
		do_action('wpda-builder/elementor/render/before/'.$this->get_name(), $this);
		$this->render_widget();
		do_action('wpda-builder/elementor/render/after/'.$this->get_name(), $this);
	}

	protected function init_controls(){
	}

	protected function render_widget(){
	}


}
