<?php

if(!defined('ABSPATH')) {
	exit;
}

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_SliderCustomArrowPosition $widget */

$widget->add_render_attribute('wrapper', 'class', 'gt3_section_arrows_position');

global $post;
$post_id = $post->ID;

?>
	<div <?php $widget->print_render_attribute_string('wrapper') ?>>
		<?php
			if ($widget->is_editor && current_user_can('edit_post', $post_id)) {
				echo '<div class="slick-prev slick-arrow gt3_modified"><div class="slick_arrow"></div></div><div class="slick-next slick-arrow gt3_modified"><div class="slick_arrow"></div></div>';
			}
		?>
	</div>
<?php
