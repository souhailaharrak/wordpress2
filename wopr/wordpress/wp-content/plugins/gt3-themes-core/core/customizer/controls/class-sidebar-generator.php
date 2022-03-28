<?php

namespace GT3\ThemesCore\Customizer\Controls;

use GT3\ThemesCore\Assets\Script;
use WP_Customize_Control;

class Sidebar_Generator extends WP_Customize_Control {

	/**
	 * Customize control type.
	 *
	 * @since 4.9.0
	 * @var string
	 */
	public        $type        = 'gt3-sidebar-generator';
	public static $type_static = 'gt3-sidebar-generator';

	/**
	 * Type of code that is being edited.
	 *
	 * @since 4.9.0
	 * @var string
	 */
	public $code_type = '';

	/**
	 * Code editor settings.
	 *
	 * @see   wp_enqueue_code_editor()
	 * @since 4.9.0
	 * @var array|false
	 */
	public $editor_settings = array();

	/**
	 * Tooltips content.
	 *
	 * @access public
	 * @var string
	 */
	public $tooltip = '';

	/**
	 * Used to automatically generate all postMessage scripts.
	 *
	 * @access public
	 * @var array
	 */
	public $js_vars = array();

	/**
	 * Used to automatically generate all CSS output.
	 *
	 * @access public
	 * @var array
	 */
	public $output = array();

	/**
	 * Data type
	 *
	 * @access public
	 * @var string
	 */
	public $option_type = 'theme_mod';

	public $label = '';

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @since 4.9.0
	 */
	public function enqueue(){
		Script::enqueue_core_asset('customizer/gt3-sidebar-generator');
	}

	public function to_json(){
		parent::to_json();

		// The setting value.
		$this->json['id']           = $this->id;
		$this->json['newText'] = esc_html__('Add New Sidebar', 'gt3-themes-core');
	}


	/**
	 * Render a JS template for control display.
	 *
	 * @since 4.9.0
	 */
	protected function content_template(){
		?>
		<# if ( data.label ) { #>
		<span class="customize-control-title">{{ data.label }}</span>
		<# } #>
		<ul class="repeater-fields"></ul>
		<button class="add-new">{{data.newText}}</button>
		<?php
	}
}
