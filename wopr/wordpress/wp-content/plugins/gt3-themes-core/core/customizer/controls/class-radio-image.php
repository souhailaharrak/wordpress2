<?php

namespace GT3\ThemesCore\Customizer\Controls;

use GT3\ThemesCore\Assets\Script;
use GT3\ThemesCore\Assets\Style;
use WP_Customize_Control;

class Radio_Image extends WP_Customize_Control {

	/**
	 * The control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'gt3-radio-image';

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @access public
	 */
	public function enqueue(){
		Script::enqueue_core_asset('customizer/gt3-radio-image');
		Style::enqueue_core_asset('customizer/gt3-radio-image');
	}

	public function to_json(){
		parent::to_json();
		$this->json['choices'] = $this->choices;
		$this->json['id']      = $this->id;
		$this->json['value']   = $this->value();
	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see Kirki_Customize_Control::to_json()}.
	 *
	 * @see    WP_Customize_Control::print_template()
	 *
	 * @access protected
	 */
	protected function content_template(){
		?>
		<# if ( data.tooltip ) { #>
		<a href="#" class="tooltip hint--left" data-hint="{{ data.tooltip }}"><span class='dashicons dashicons-info'></span></a>
		<# } #>
		<label class="customizer-text">
			<# if ( data.label ) { #>
			<span class="customize-control-title">{{{ data.label }}}</span>
			<# } #>
			<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
		</label>
		<div id="input_{{ data.id }}" class="image">
			<# for ( key in data.choices ) { #>
			<span class="image-radio-option-wrapper">
			<input class="image-select" type="radio" value="{{ key }}" name="_customize-radio-{{ data.id }}" id="{{ data.id }}{{ key }}" {{{ data.link }}}<# if ( data.value === key ) { #> checked="checked"<# } #>>
			<label for="{{ data.id }}{{ key }}">
				<img src="{{ data.choices[ key ] }}">
				<span class="image-clickable"></span>
			</label>
				</input>
			</span>
			<# } #>
		</div>
		<?php
	}
}

