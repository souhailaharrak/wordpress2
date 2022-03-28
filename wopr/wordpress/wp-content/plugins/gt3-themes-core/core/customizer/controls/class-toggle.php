<?php

namespace GT3\ThemesCore\Customizer\Controls;

use GT3\ThemesCore\Assets\Script;
use GT3\ThemesCore\Assets\Style;
use WP_Customize_Control;

class Toggle extends WP_Customize_Control {

	/**
	 * The type of customize control.
	 *
	 * @access public
	 * @since  1.3.4
	 * @var    string
	 */
	public $type = 'gt3-toggle';

	/**
	 * Enqueue scripts and styles.
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function enqueue(){
		Script::enqueue_core_asset('customizer/gt3-toggle');
		Style::enqueue_core_asset('customizer/gt3-toggle');
	}

	/**
	 * Add custom parameters to pass to the JS via JSON.
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function to_json(){
		parent::to_json();

		// The setting value.
		$this->json['id']           = $this->id;
		$this->json['value']        = $this->value();
		$this->json['link']         = $this->get_link();
		$this->json['defaultValue'] = $this->setting->default;
	}

	/**
	 * Don't render the content via PHP.  This control is handled with a JS template.
	 *
	 * @access public
	 * @return void
	 * @since  1.0.0
	 */
	public function render_content(){ }

	public static function sanitize($checked){
		// Boolean check.
		return ((isset($checked) && (true === $checked || '1' === $checked)) ? true : false);
	}

	/**
	 * An Underscore (JS) template for this control's content.
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @return void
	 * @since  1.3.4
	 * @see    WP_Customize_Control::print_template()
	 *
	 * @access protected
	 */
	protected function content_template(){
		?>
		<label class="toggle">
			<div class="toggle--wrapper">

				<# if ( data.label ) { #>
				<span class="customize-control-title">{{ data.label }}</span>
				<# } #>

				<input id="toggle-{{ data.id }}" type="checkbox" class="toggle--input" value="{{ data.value }}" {{{ data.link }}} <# if ( data.value ) { #> checked="checked" <# }
				#> />
				<label for="toggle-{{ data.id }}" class="toggle--label"></label>
			</div>

			<# if ( data.description ) { #>
			<span class="description customize-control-description">{{ data.description }}</span>
			<# } #>
		</label>
		<?php
	}
}
