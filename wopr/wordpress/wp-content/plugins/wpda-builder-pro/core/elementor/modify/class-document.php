<?php

namespace WPDaddy\Builder\Elementor\Modify;

use Elementor\Controls_Manager;
use Elementor\Element_Section;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Plugin;
use Elementor\Shapes;
use Elementor\Core\Base\Document as Elementor_Document;
use WPDaddy\Builder\Elementor;
use WPDaddy\Builder\Library\Basic as Basic_Library;
use WPDaddy\Builder\Library\Header as Header_Library;

class Document {
	const type = 'section';

	private static $instance = null;

	public static function instance(){
		if(!self::$instance instanceof self) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct(){
		add_action('elementor/documents/register_controls', array( $this, 'extend_controls' ), 0);
	}


	/** @param Elementor_Document $document */
	public function extend_controls($document){
		if(!($document instanceof Header_Library)) {
			return;
		}
		/** @var Element_Section $section */

		$document->start_controls_section(
			'wpda_settings',
			[
				'label' => __('WPDaddy Settings', 'wpda-builder'),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			]
		);
		
		$document->add_responsive_control(
			'header_over_bg',
			array(
				'label' => __('Header Over Bg', 'wpda-builder'),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$document->end_controls_section();
	}
}
