<?php

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

/** @var \ElementorModal\Widgets\GT3_Core_Elementor_Widget_AnimatedHeadlines $widget */

$settings = array(
	'headlines_style' => 'highlighter',
	'marked_word' => array(),
	'highlighter_shap' => 'underlined',
	'infinite_loop' => 'yes',
	'highlighter_duration'         => array(
		'size' => 1200,
		'unit' => 'ms',
	),
	'highlighter_interval'         => array(
		'size' => 5000,
		'unit' => 'ms',
	),
);

$settings["infinite_loop"] = ($settings["infinite_loop"] == "yes");

$settings = wp_parse_args($widget->get_settings(), $settings);

$widget->add_render_attribute('wrapper', 'class', array(
	'gt3-animated-headlines',
));

$settings = $widget->get_settings_for_display();

if ( '' === $settings['title'] ) {
	return;
}

$widget->add_render_attribute( 'title', 'class', 'gt3-headline-title' );

if ( ! empty( $settings['size'] ) ) {
	$widget->add_render_attribute( 'title', 'class', 'elementor-size-' . $settings['size'] );
}

$widget->add_inline_editing_attributes( 'title' );

$title = trim($settings['title']);
$settings['marked_word'] = (array)$settings['marked_word'];
$settings['marked_word'] = array_map('intval',$settings['marked_word']);
if (count($settings['marked_word'])) {
	$words = explode(' ', $title);

	$shape = '';
	if($settings['headlines_style'] == 'highlighter') {
		switch($settings['highlighter_shap']) {
			case 'circled_type1':
				$shape = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m43,98c16.7,29.7 47.6,35.6 72,40c24.4,4.4 80.2,11.2 157.6,9.6c77.4,-1.6 122.2,-15 142.6,-22.3c20.4,-7.3 77.2,-39.5 44.5,-69.3c-32.7,-29.8 -135.7,-49.6 -211.2,-53.8c-75.5,-4.2 -188.2,18.8 -220,48c-31.8,29.2 -23.1,67.8 -5.2,76.9" /></svg>';
				break;
			case 'circled_type2':
				$shape = apply_filters( 'gt3/core/render/highlighter_shap/circled_type2', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m444.37109,61.85547c-16.3,-20.3 -34.4,-27.4 -66,-40c-31.6,-12.6 -119.8,-21.8 -187.4,-17.4c-67.6,4.4 -97.8,13 -125.4,31.7c-27.6,18.7 -37.8,41.5 -10.5,69.7c27.3,28.2 143.3,35.4 194.8,36.2c51.5,0.8 135.8,-9.2 167,-19c31.2,-9.8 103.9,-67.2 31.8,-103.1" /></svg>' );
				break;
			case 'circled_type3':
				$shape = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m44.37109,65.85547c11.7,-22.3 35.6,-37.4 82,-50c46.4,-12.6 138.2,-15.8 195.6,-9.4c57.4,6.4 82.2,7 128.6,24.7c46.4,17.7 42.2,67.5 -11.5,85.7c-53.7,18.2 -90.7,21.4 -179.2,26.2c-88.5,4.8 -186.2,-14.2 -208,-27c-21.8,-12.8 -68.1,-39.2 -20.2,-80.1" /></svg>';
				break;
			case 'circled_type4':
				$shape = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m450,92c-18.3,18.7 -130.4,53.6 -191,55c-60.6,1.4 -136.8,-7.8 -185.4,-32.4c-48.6,-24.6 -36.8,-53 -14.4,-67.3c22.4,-14.3 37.2,-23.5 73.5,-31.3c36.3,-7.8 115.3,-16.6 177.8,-11.8c62.5,4.8 124.8,12.8 150,31c25.2,18.2 47.9,58.8 -1.2,93.9" /></svg>';
				break;
			case 'curved':
				$shape = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m23,132.1c17.56647,-8.8 13.86827,-18.8 32.25656,-18.8c16.02555,0 26.81198,19.1 40.26934,19.1c23.52469,0 27.73653,-17.6 46.12483,-17.6c17.56647,0 23.9356,17.5 40.57752,17.5c21.88104,0 22.39468,-17.6 40.06388,-17.6c22.70287,0 28.55836,17.8 47.04938,17.8c19.31285,0 36.46841,-18.6 48.28211,-18.6c20.95649,0 21.67559,20 41.91298,20c23.52469,0 31.64019,-21.6 50.02849,-19.6c18.18284,-1.6 9.34824,19.5 36.46841,18.8c17.56647,0 14.58736,-5.5 28.86654,-14.6" /></svg>';
				break;
			case 'doubled':
				$shape = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m37,146.4l141.3,-28.6c130.2,-11 232.2,-3.3 297.7,4.6"/><path d="m113.9,142.8c49.1,-17.1 91,-29.3 127.2,-29.1c68.5,-2.8 114.9,-1.8 177.9,7.4" /></svg>';
				break;
			case 'zigzagged':
				$shape = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m20.3,127.3c49.3,-3 142.7,-12.6 187.7,-12.4c120.9,-5.6 176.9,4.4 269.3,11.2c-122.2,-0.5 -274.1,-4.5 -385.3,8.9c80.6,-1.9 234.2,1 313.9,5.3c-56,1.4 -152.2,0.7 -216.1,8" /></svg>';
				break;
			default:
			case 'underlined':
				$shape = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="m20.69998,137.6c102.29522,-15.6 261.64822,-20.4 358.75135,-15.3c40.43563,3.2 77.13684,8.8 100.8487,19.7" /></svg>';
				break;
		}
	}


	$marked      = $settings['marked_word'];
	$marked_flip = array_flip($marked);
	$worked      = array();

	$inserted_offset = 0;
	foreach($marked as $index_key) {
		if(in_array($index_key, $worked) || !key_exists($index_key, $words)) {
			continue;
		}
		$word        = $words[$index_key];
		$first_index = $index_key;
		$count_next  = 0;

		$next = in_array($index_key+1, $marked);

		while(false !== $next) {
			$count_next++;

			$index_key++;
			$k = array_search($index_key, $marked);

			$worked[] = $index_key;
			$next     = in_array($index_key, $marked);
			unset($marked[$k]);
		}
		$index_key--;

		if($settings['headlines_style'] == 'highlighter') {
			if($count_next === 0) {
				$word = '<span class="gt3_headline_word '.($settings["infinite_loop"]  ? "gt3_headline_loop" : "").'">'.$word.$shape.'</span>';
				$words[$first_index] = $word;
			}else {
				$word = '<span class="gt3_headline_word '.($settings["infinite_loop"]  ? "gt3_headline_loop" : "").'">'.$word;
				$words[$first_index] = $word;
				$word = $words[$index_key].$shape.'</span>';
				$words[$index_key] = $word;
			}

		}
	}
	$title = implode(' ', $words);

}


if ( ! empty( $settings['link']['url'] ) ) {
	$widget->add_link_attributes( 'url', $settings['link'] );

	$title = sprintf( '<a %1$s>%2$s</a>', $widget->get_render_attribute_string( 'url' ), $title );
}

$title_html = sprintf( '<%1$s %2$s>%3$s</%1$s>', Utils::validate_html_tag( $settings['header_size'] ), $widget->get_render_attribute_string( 'title' ), $title );

$data = array();

if ($settings['infinite_loop']) {
	if ($settings['highlighter_duration']['size'] >= $settings['highlighter_interval']['size']) {
		$settings['highlighter_duration']['size'] = $settings['highlighter_interval']['size']/2;
	}
	$data = array(
		'highlighter_interval' => $settings['highlighter_interval']['size'],
		'highlighter_duration' => $settings['highlighter_duration']['size'],
		'infinite_loop' => $settings['infinite_loop'],
	);
}

?>
	<div <?php $widget->print_render_attribute_string('wrapper') ?>>
		<?php echo $title_html; ?>
	</div>
<?php

$widget->print_data_settings($data);




