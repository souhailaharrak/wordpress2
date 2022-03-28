<?php

namespace GT3\ThemesCore\Customizer\Controls;

use GT3\ThemesCore\Assets\Script;
use GT3\ThemesCore\Assets\Style;
use WP_Customize_Control;

class Color extends WP_Customize_Control {

	public $type = 'gt3-color';

	public $palette = true;

	public $choices = array();
	public $alpha = false;

	public function to_json(){
		parent::to_json();
		$this->json['palette']          = $this->palette;
		$this->json['value']            = $this->value();
		$this->json['choices']['alpha'] = (isset($this->alpha) && $this->alpha) ? 'true' : 'false';
	}

	public function enqueue(){
		Script::enqueue_core_asset('customizer/gt3-color');
		Style::enqueue_core_asset('customizer/gt3-color');
	}

	protected function content_template(){
		?>
		<span class="customize-control-title">
					{{{ data.label }}}
				</span>
		<# if ( data.description ) { #>
		<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>
		<# if ( data.tooltip ) { #>
		<a href="#" class="tooltip hint--left" data-hint="{{ data.tooltip }}"><span class='dashicons dashicons-info'></span></a>
		<# } #>
		<label>
			<input type="text" data-palette="{{ data.palette }}" data-default-color="{{ data.default }}" data-alpha-enabled="{{ data.choices['alpha'] }}"
			       data-alpha="{{ data.choices['alpha'] }}" value="{{ data.value }}" data-alpha-color-type="grba" class="gt3-color-control color-picker" {{{ data.link }}} />
		</label>
		<?php
	}
}
