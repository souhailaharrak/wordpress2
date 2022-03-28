<?php

namespace GT3\PhotoVideoGalleryPro;

class Intro {
	private static $instance = null;

	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public static function getUrl() {
		return plugin_dir_url(__FILE__).'img/';
	}

	private function __construct(){

		$this->rooturl = plugins_url('/', GT3PG_PRO_FILE);

		add_action(
			'admin_menu', function(){
			add_submenu_page(
				'gt3_photo_gallery_options',
				'Intro',
				'Intro',
				'administrator',
				'gt3_photo_gallery_intro',
				array( $this, 'intro_page' ),
				50
			);
		}, 101
		);

		add_action(
			'current_screen',
			function($current_screen){
				if($current_screen->base === 'gt3-gallery-pro_page_gt3_photo_gallery_intro' ||
				$current_screen->base === 'gt3-gallery-lite_page_gt3_photo_gallery_intro') {
					remove_all_actions('admin_notices');
				}
			}
		);
	}

	public function intro_page(){
		if(!current_user_can('administrator')) {
			return;
		}

		wp_enqueue_script('block-library');
		wp_enqueue_script('wp-components');

		wp_enqueue_script('wp-api-fetch');

		wp_enqueue_script('gt3pg_intro', $this->rooturl.'dist/js/admin/intro.js');
		?>
		<style>
			#gt3_editor_intro {
				max-width: 800px;
				margin: 80px auto;
			}
			.MuiStepIcon-root.MuiStepIcon-completed {
				color: green;
			}

			.components-spinner {
				display: inline-block;
				background-color: #949494;
				width: 18px;
				height: 18px;
				opacity: .7;
				margin: 5px 11px 0;
				border-radius: 100%;
				position: relative
			}

			.components-spinner:before {
				content: "";
				position: absolute;
				background-color: #fff;
				top: 3px;
				left: 3px;
				width: 4px;
				height: 4px;
				border-radius: 100%;
				transform-origin: 6px 6px;
				animation: components-spinner__animation 1s linear infinite
			}

			@keyframes components-spinner__animation {
				0% {
					transform: rotate(0deg)
				}

				to {
					transform: rotate(1turn)
				}
			}

			#gt3_editor_intro input {
				border: none;
				box-shadow: none;
			}
			#gt3_editor_intro .step1 {
				display: flex;
				flex-direction: row;
				flex-wrap: wrap;
			}

			#gt3_editor_intro .form_code {
				margin-top: 13px;
				margin-bottom: 10px;
			}
			#gt3_editor_intro .step1 button {
				margin: 10px 16px auto;
				height: 36px;
			}

			#gt3_editor_intro .with_spinner {
				display: flex;
				align-items: center;
			}

			#gt3_editor_intro .with_spinner span {
				display: block;
				flex-grow: 1;
				margin: 0 30px;
			}

			#gt3_editor_intro .save_code_button {
				background: green;
				color: white;
			}
			.wrapper_step0 .controls {
				text-align: left;
			}

		</style>
		<div class="edit-post-layout">
			<div class="edit-post-sidebar">
				<div id="gt3_editor_intro" data-settings-url="<?php echo esc_attr(menu_page_url('gt3_photo_gallery_options',false));?>"></div>
			</div>
		</div>
		<?php
	}
}
