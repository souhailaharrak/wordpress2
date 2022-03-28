<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;
global $_wp_additional_image_sizes;


/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_ImageCarousel $widget */

$settings = array(
	'autoplay_carousel'        => 'yes',
	'auto_play_time'           => 3000,
	'use_pagination_carousel'  => 'yes',
	'crop_img_size_for_iphone' => 'yes',
	'slides'                   => array(),
	'slider_style'             => '',
	'margin_between_slides'    => '0',
	'custom_links_target'      => '',
	'img_size'                 => 'thumbnail',
	'use_center_mode'                 => 'yes'
);

$settings = wp_parse_args($widget->get_settings(), $settings);

//if($settings['slider_style'] == 'iphone_view') {
//	$default_src = get_template_directory_uri().'/img/modules/iphone_type_no_image.png';
//} else {
//	$default_src = vc_asset_url('vc/no_image.png');
//}

$link_start = '';
$link_end   = '';
$el_start   = '<div class="slider_item_thumb"><div class="slider_item_inner">';
$el_end     = '</div></div>';

$iphone_visibility_status = $crop_for_iphone = '';

if($settings['slider_style'] == 'iphone_view') {
	$iphone_visibility_status = 'iphone_visible';

	if((bool) $settings['crop_img_size_for_iphone']) {
		$crop_for_iphone = 'crop_for_iphone_enable';
	}
}

$carousel_parent = 'gt3_module_carousel';

$settings['auto_play_time'] = (int) $settings['auto_play_time'];

\GT3\ThemesCore\Assets\Script::enqueue_core_asset('slick');

$slick_settings = '';
$slick_settings = array(
	'slidesToShow'  => 1,
	'centerMode'    => ($settings['slider_style'] == 'iphone_view' && $settings['use_center_mode'] == 'yes') ? true : false,
	'variableWidth' => true,
	'speed'         => 300,
	'useCSS'         => false,
	'waitForAnimate'         => false,
	'useTransform'         => false,
	'infinite'      => true,
	'focusOnSelect' => true,
	'arrows'        => false,
	'autoplaySpeed' => intval($settings['auto_play_time']),
	'autoplay'      => (bool) $settings['autoplay_carousel'] ? true : false,
	'dots'          => (bool) $settings['use_pagination_carousel'] ? true : false,
	'rtl'          => is_rtl() ? true : false
);

if(gt3_get_theme_option("color_scheme") == 'light') {
	$skin_style = 'light';
} else {
	$skin_style = 'dark';
}

$widget->add_render_attribute('wrapper','class',array(
	'gt3_module_image_slider',
	'gt3_skin_style_'.$skin_style,
	'margin_between_slides_'.$settings['margin_between_slides'],
	$iphone_visibility_status,
	$crop_for_iphone,
	'gt3_module_carousel',
	($settings['use_center_mode'] == 'yes') ? 'gt3_center-mode-enable' : 'gt3_center-mode-disable',
));

$data_slide_vars = 'style="--slides_spacing:'.$settings['margin_between_slides'].'px;"';

if ($settings['slider_style'] == 'iphone_view') {
	$data_slide_vars = 'style="--slides_spacing:'.$settings['margin_between_slides'].'px; --device_width:'.apply_filters( 'gt3_image_carousel_device_width', '358' ).'px; --device_height:'.apply_filters( 'gt3_image_carousel_device_height', '710' ).'px; --slide_width:'.apply_filters( 'gt3_image_carousel_phone_width', '312' ).'px; --slide_height:'.apply_filters( 'gt3_image_carousel_phone_height', '670' ).'px;"';
}

?>
<div <?php $widget->print_render_attribute_string('wrapper')?> <?php echo $data_slide_vars ?>>
	<div class="gt3_carousel_list" data-slick="<?php echo esc_attr(wp_json_encode($slick_settings)); ?>">
		<?php
		foreach($settings['slides'] as $image) {
			$image = wp_prepare_attachment_for_js($image);
			if($image) {
				if (isset($image['sizes']) && isset($image['sizes'][$settings['img_size']])) {
					$image_src = $image['sizes'][$settings['img_size']]['url'];
				} else if (isset($image['sizes']) && isset($image['sizes']['full'])) {
					$image_src = $image['sizes']['full']['url'];
				} else {
					continue;
				}

				$image_size = '';
				if ($settings['slider_style'] == 'iphone_view') {
					$image_size = 'style="width:' . apply_filters( 'gt3_image_carousel_phone_width', '312' ) . 'px;"';
				}

				if ((bool)$settings['crop_img_size_for_iphone'] && $settings['slider_style'] == 'iphone_view') {
					$image_src = aq_resize($image['sizes']['full']['url'], apply_filters( 'gt3_image_carousel_phone_width', '312' ),apply_filters( 'gt3_image_carousel_phone_height', '670' ),true, true,false);
				}
				if ($image_src == false) continue;
				?>
				<div class="slider_item_thumb">
					<div class="slider_item_inner">
						<img src="<?php echo esc_url($image_src)?>" <?php echo ($image_size)?> alt="<?php echo esc_attr($image['alt']); ?>" />
					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
	<?php
	if($settings['slider_style'] == 'iphone_view') {
		?>
		<div class="gt3_phone_element"></div>
		<?php
	}
	?>
</div>




