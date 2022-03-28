<?php

namespace WPDaddy\Builder\Elementor\Widgets\Menu_Items;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Control_Media;
use Elementor\Utils;
use Elementor\Group_Control_Image_Size;

trait Trait_Render {
	protected function render_widget(){
		$settings = array(
			'items' => array(
				array(
					'menu_item_title'   => '',
					'menu_item_link' => '',
					'menu_item_label' => '',
					'menu_item_icon' => '',
				),
			),
			'select_alignment' => 'align_left',
		);

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute(
			'wrapper', 'class',
			array(
				'wpda-builder-menu-items',
				$settings['select_alignment'],
			)
		);


		if(isset($settings['items']) && is_array($settings['items'])) {
			echo '<div '. $this->get_render_attribute_string('wrapper') .'>';

			$current_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];


			foreach($settings['items'] as $item) {
				$item_title = $item_title_wrap = $item_icon = $item_label = '';

				if (!empty($item['menu_item_title'])) {
					$item_title = esc_html($item['menu_item_title']);
				}

				if ( ! empty( $item['menu_item_link']['url'] ) ) {
					$tab_title_setting_key = $this->get_repeater_setting_key( 'tab_title', 'tabs', $item['_id'] );
					$this->add_link_attributes( $tab_title_setting_key, $item['menu_item_link'] );

					$item_title = sprintf( '<a %1$s>%2$s</a>', $this->get_render_attribute_string( $tab_title_setting_key ), $item_title );
				}

				if (!empty($item_title)) {
					$img = '';
					if (key_exists('image_hover_preview',$item) && !empty($item['image_hover_preview']['id'])) {
						$img = wp_get_attachment_image($item['image_hover_preview']['id'], 'full');
						if (!empty($img)) {
							$img = '<div class="preview">'.$img.'</div>';
						}
					}
					$item_title_wrap = '<span class="menu_item-title">'. $item_title .$img. '</span>';
				}

				if(!empty($item['menu_item_icon'])) {
					$item_icon = '<span class="menu_item_icon '.esc_attr($item['menu_item_icon']).'"></span>';
				}

				if (!empty($item['menu_item_label'])) {
					$item_label = '<span class="menu_item_label">' . esc_html($item['menu_item_label']) . '</span>';
				}

				$menu_item_cont = $item_icon . $item_title_wrap . $item_label;


				if (!empty($menu_item_cont)) {
					$is_current = ($current_url === $item['menu_item_link']['url']  ? ' current' : '');
					echo '<div class="menu_item elementor-repeater-item-'.$item['_id'].$is_current.'">'. $menu_item_cont .'</div>';
				}

			}

			echo '</div>';
		}

	}
}

