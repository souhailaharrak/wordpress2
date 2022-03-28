<?php

namespace GT3\PhotoVideoGalleryPro\Elementor\Widgets;
defined('ABSPATH') or exit;

use Elementor\Controls_Manager;
use GT3\PhotoVideoGalleryPro\Block\Carousel as Gallery;

class Carousel extends Basic {

	public function get_name(){
		return 'gt3pg-carousel';
	}

	public function get_title(){
		return esc_html__('Carousel', 'gt3pg_pro');
	}

	public function get_icon(){
		return 'gt3-elementor-editor-icon gt3-icon-carousel';
	}

	protected function render(){
		$this->WRAP = esc_html('#uid-'.$this->get_id().' ');
		$settings   = $this->_get_settings();

		/* @var \GT3\PhotoVideoGalleryPro\Block\Basic $gallery */
		$gallery = Gallery::instance();

		echo $gallery->render_block($settings);

	}

	protected function _controls(){
		$this->start_controls_section(
			'section_general',
			array(
				'label' => esc_html__('Images', 'gt3pg_pro'),
			)
		);

		$this->imagesControls();

		$this->end_controls_section();

		$this->start_controls_section(
			'settings', array(
				'label' => esc_html__('Settings', 'gt3pg_pro'),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);

		$this->add_control(
			'imageSize',
			array(
				'label'       => esc_html__('Select Image Size', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default'         => esc_html__('Default', 'gt3pg_pro'),
					'medium'          => esc_html__('Medium (300px)', 'gt3pg_pro'),
					'medium_large'    => esc_html__('Thumbnail (768px)', 'gt3pg_pro'),
					'large'           => esc_html__('Large (1024px)', 'gt3pg_pro'),
					'gt3pg_optimized' => esc_html__('Optimized', 'gt3pg_pro'),
					'full'            => esc_html__('Full Size', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'type',
			array(
				'label'       => esc_html__('Type', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default' => esc_html__('Default', 'gt3pg_pro'),
					'slider'  => esc_html__('Slider', 'gt3pg_pro'),
					'loop'    => esc_html__('Infinite Slider', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'centerMode',
			array(
				'label'       => esc_html__('Center Mode', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default' => esc_html__('Default', 'gt3pg_pro'),
					'1'       => esc_html__('Enabled', 'gt3pg_pro'),
					'0'       => esc_html__('Disabled', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'       => esc_html__('Speed', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::NUMBER,
				'min'         => 200,
				'default'     => 3000,
			)
		);

		$this->add_control(
			'fixedHeight',
			array(
				'label'       => esc_html__('Height (%)', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::NUMBER,
				'min'         => 20,
				'max'         => 100,
				'default'     => 56,
			)
		);

		$this->add_control(
			'perPage',
			array(
				'label'       => esc_html__('Show Slides', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default' => esc_html__('Default', 'gt3pg_pro'),
					'1'       => esc_html__('1', 'gt3pg_pro'),
					'2'       => esc_html__('2', 'gt3pg_pro'),
					'3'       => esc_html__('3', 'gt3pg_pro'),
					'4'       => esc_html__('4', 'gt3pg_pro'),
					'5'       => esc_html__('5', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'perMove',
			array(
				'label'       => esc_html__('Move Slides', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default' => esc_html__('Default', 'gt3pg_pro'),
					'1'       => esc_html__('1', 'gt3pg_pro'),
					'2'       => esc_html__('2', 'gt3pg_pro'),
					'3'       => esc_html__('3', 'gt3pg_pro'),
					'4'       => esc_html__('4', 'gt3pg_pro'),
					'5'       => esc_html__('5', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'start',
			array(
				'label'       => esc_html__('First Slide', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'0' => esc_html__('1', 'gt3pg_pro'),
					'1' => esc_html__('2', 'gt3pg_pro'),
					'2' => esc_html__('3', 'gt3pg_pro'),
					'3' => esc_html__('4', 'gt3pg_pro'),
					'4' => esc_html__('5', 'gt3pg_pro'),
				),
				'default'     => '0',
			)
		);

		$this->add_control(
			'gap',
			array(
				'label'       => esc_html__('Show Gap', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'default'     => 0,
			)
		);

		$this->add_control(
			'arrows',
			array(
				'label'       => esc_html__('Show Arrows', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default' => esc_html__('Default', 'gt3pg_pro'),
					'1'       => esc_html__('Enabled', 'gt3pg_pro'),
					'0'       => esc_html__('Disabled', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'dots',
			array(
				'label'       => esc_html__('Show Dots', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default' => esc_html__('Default', 'gt3pg_pro'),
					'1'       => esc_html__('Enabled', 'gt3pg_pro'),
					'0'       => esc_html__('Disabled', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'       => esc_html__('Autoplay', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'default' => esc_html__('Default', 'gt3pg_pro'),
					'1'       => esc_html__('Enabled', 'gt3pg_pro'),
					'0'       => esc_html__('Disabled', 'gt3pg_pro'),
				),
				'default'     => 'default',
			)
		);

		$this->add_control(
			'interval',
			array(
				'label'       => esc_html__('Autoplay Interval (ms)', 'gt3pg_pro'),
				'label_block' => true,
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 10000,
				'default'     => 2000,
			)
		);

		$this->end_controls_section();
	}
}

