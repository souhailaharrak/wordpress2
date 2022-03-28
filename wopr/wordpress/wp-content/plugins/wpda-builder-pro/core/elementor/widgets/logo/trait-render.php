<?php

namespace WPDaddy\Builder\Elementor\Widgets\Logo;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

trait Trait_Render {

	protected function render_widget(){
		$settings = array(
			'header_logo'        => array( 'url' => '', ),
			'logo_height_custom' => '',
			'logo_sticky'        => array( 'url' => '', ),
			'logo_custom_link' => '',
			'logo_custom_link_url' => array(
				'url'         => '#',
				'is_external' => false,
				'nofollow'    => false,
			),
		);

		$settings = wp_parse_args($this->get_settings(), $settings);

		/* Custom Logo URL */
		$link_url = esc_url(home_url('/'));
		if ($settings['logo_custom_link']) {
			if(empty($settings['logo_custom_link_url']['url'])) {
				$settings['logo_custom_link_url']['url'] = '#';
			}

			if($settings['logo_custom_link_url']['is_external']) {
				$this->add_render_attribute('href', 'target', '_blank');
			}
			if(!empty($settings['logo_custom_link_url']['nofollow'])) {
				$this->add_render_attribute('href', 'rel', 'nofollow');
			}

			$link_url =esc_url($settings['logo_custom_link_url']['url']);
		}
		$this->add_render_attribute('href', 'href',$link_url );

		/* Custom Logo URL End */


		$this->add_render_attribute(
			'wrapper', 'class', array(
			'wpda-builder-logo_container',
			($settings['header_logo']['url'] && $settings['logo_sticky']['url']) ? 'has_sticky_logo' : '',
		)
		);
		$enable_sticky = false;
		if(isset($settings['header_logo']['url']) && !empty($settings['header_logo']['url'])) {
			$header_logo = $settings['header_logo']['url'];

			if($header_logo) {
				$header_logo_array = wp_prepare_attachment_for_js($settings['header_logo']['id']);
				if (!is_null($header_logo_array)) {
					$header_logo   = '<img class="wpda-builder-logo" src="'.esc_url($header_logo).'" alt="'.esc_attr($header_logo_array['alt']).'" title="'.esc_attr($header_logo_array['title']).'"/>';
					$enable_sticky = true;
				}  else {
					$header_logo = '';
				}
			}
		} else {
			$header_logo = '<span class="wpda-builder-site_title">'.get_bloginfo('name').'</span>';
		}
		if(isset($settings['logo_sticky']['url']) && !empty($settings['logo_sticky']['url'])) {
			$logo_sticky = $settings['logo_sticky']['url'];
			if($logo_sticky) {
				$sticky_logo_array = wp_prepare_attachment_for_js($settings['logo_sticky']['id']);
				if (!is_null($sticky_logo_array)) {
					$logo_sticky = '<img class="wpda-builder-logo_sticky" src="'.esc_url($logo_sticky).'"  alt="'.esc_attr($sticky_logo_array['alt']).'" title="'.esc_attr($sticky_logo_array['title']).'" />';
				} else {
					$logo_sticky = '';
				}
			}
		} else {
			$logo_sticky = '';
		}
		if(!$enable_sticky) {
			$logo_sticky = '';
		}
		?>
		<div <?php $this->print_render_attribute_string('wrapper') ?>>
			<a <?php $this->print_render_attribute_string('href') ?>>
				<?php echo $header_logo.$logo_sticky; ?>
			</a>
		</div>
		<?php
	}
}

