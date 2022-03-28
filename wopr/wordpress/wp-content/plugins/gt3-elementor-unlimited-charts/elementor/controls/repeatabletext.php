<?php

namespace Elementor;

if(!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Elementor\Base_Data_Control;
use ElementorModal\Widgets\GT3_Elementor_UnlimitedCharts;


if(!class_exists('\Elementor\GT3_Core_Elementor_Control_RepeatableText')) {
	class GT3_Core_Elementor_Control_RepeatableText extends Base_Data_Control {

		public function get_type(){
			return self::type();
		}

		public static function type(){
			return 'gt3-elementor-core-repeatable-text';
		}

		protected function get_default_settings() {
			return [
				'options' => [],
			];
		}

		public function enqueue() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script(
				'wp-color-picker-alpha',
				ELEMENTOR_ASSETS_URL . 'lib/wp-color-picker/wp-color-picker-alpha' . $suffix . '.js',
				[
					'wp-color-picker',
				],
				'2.0.1',
				true
			);

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker-alpha' );
		}

		public function content_template(){
			?>
			<# 
			var color_class = '';
			if(data.color_mode){
				color_class = ' color_mode';
			}
			 #>
			<div class="elementor-control-field">
				<div class="elementor-control-gt3-repeatable-text elementor-control-type-gt3-repeatable-text{{color_class}}">
					<div class="control-repeatable-title">{{ label }}</div>					
					<div class="control-gt3-repeatable-blank-wrapper"><input type="text" class="control-gt3-repeatable-blank" data-alpha="true" value="{{data.blank}}" /></div>
					<div class="elementor-button elementor-control-item-add"><?php echo esc_html__('+ Add', 'gt3_unlimited_chart') ?></div>
					<div class="control-repeatable-text-items"></div>
					<input type="hidden" data-setting="{{ name }}" class="control-gt3-repeatable-text-input" />
				</div>
			</div>
			<?php
		}
	}
}
