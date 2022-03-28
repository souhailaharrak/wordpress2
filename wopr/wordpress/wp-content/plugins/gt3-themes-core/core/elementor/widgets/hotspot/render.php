<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_Hotspot $widget */

$settings = array(
	'info_position' => 'top_left',
	'title'         => '',
	'description'   => '',
);

$settings = wp_parse_args($widget->get_settings(), $settings);

$widget->add_render_attribute('wrapper', 'class', array(
	'gt3-hotspot-wrapper',
	'gt3-hotspot-info-align-'.$settings['info_position'],
));

?>
	<div <?php $widget->print_render_attribute_string('wrapper') ?>>
		<div class="gt3-hotspot-button"></div>
		<div class="gt3-hotspot-info">
			<?php echo (!empty($settings['title'])) ? '<div class="gt3_hotspot_title"><h3>'.$settings['title'].'</h3></div>' : '';  ?>
			<?php echo (!empty($settings['description'])) ? '<div class="gt3_hotspot_descr">'.$settings['description'].'</div>' : '';  ?>
		</div>
	</div>
<?php



