<?php

namespace GT3\ThemesCore\Customizer\Controls;

use GT3\ThemesCore\Assets\Script;
use GT3\ThemesCore\Assets\Style;
use WP_Customize_Control;

class Button extends WP_Customize_Control {

	public $type = 'gt3-button';

	public $button_text  = '';
	public $action_click = '';

	public $nonce_key = '';

	public function to_json(){
		parent::to_json();
		$this->json['value']        = $this->value();
		$this->json['button_text']  = $this->button_text;
		$this->json['action_click'] = $this->action_click;
		$this->json['nonce']        = wp_create_nonce($this->nonce_key);
	}

	public function enqueue(){
		Script::enqueue_core_asset('customizer/gt3-button');
		Style::enqueue_core_asset('customizer/gt3-button');
	}

	protected function content_template(){
		?>
		<span class="customize-control-title">{{{ data.label }}}</span>
		<# if ( data.tooltip ) { #>
		<a href="#" class="tooltip hint--left" data-hint="{{ data.tooltip }}"><span class='dashicons dashicons-info'></span></a>
		<# } #>
		<label>

			<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
			<input type="button" value="{{{ data.button_text }}}" />
		</label>
		<?php
	}
}
