<?php

namespace WPDaddy\Builder\Elementor\Widgets\Menu;

if(!defined('ABSPATH')) {
	exit;
}

use Elementor\Utils;

trait Trait_Render {
	protected function render_widget(){
		$settings = array(
			'menu_select' => '',
		);

		$settings = wp_parse_args($this->get_settings(), $settings);

		$this->add_render_attribute(
			'wrapper', 'class',
			array(
				'wpda-builder-menu',
			)
		);
		?>
		<div class="wpda-mobile-navigation-toggle">
			<div class="wpda-toggle-box">
				<div class="wpda-toggle-inner"></div>
			</div>
		</div>
		<div class="wpda-navbar-collapse">
			<nav <?php $this->print_render_attribute_string('wrapper') ?>>
				<?php
				ob_start();
				if(!empty($settings['menu_select'])) {
					wp_nav_menu(
						array(
							'menu'            => $settings['menu_select'],
							'container'       => '',
							'container_class' => '',
							'after'           => '',
							'menu_class'      => 'wpda-menu',
							'link_before'     => '',
							'link_after'      => '',
							'walker'          => apply_filters( 'wpda_walker_menu', '' ),
						)
					);
				}
				$menu = ob_get_clean();
				if(!empty($menu)) {
					echo $menu;
				}
				?>
			</nav>
		</div>
		<?php
	}
}

