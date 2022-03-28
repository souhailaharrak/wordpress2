<?php

	namespace ElementorModal\Widgets;

	use Elementor\Group_Control_Image_Size;
	use Elementor\Plugin;
	use Elementor\Repeater;
	use Elementor\Widget_Base;
	use Elementor\Controls_Manager;
	use Elementor\Group_Control_Typography;
	use Elementor\Scheme_Typography;
	use Elementor\Scheme_Color;
	use Elementor\Group_Control_Border;
	use Elementor\Group_Control_Background;
	use Elementor\Group_Control_Box_Shadow;
	use Elementor\Frontend;
	use WP_Query;
	use Elementor\GT3_Core_Elementor_Control_Query;


	if(!defined('ABSPATH')) {
		exit;
	} // Exit if accessed directly

	class GT3_UnlimitedCharts_Chart extends Widget_Base {

		public function get_title(){
			return esc_html__('GT3 Chart', 'gt3_unlimited_chart');
		}

		public function get_icon(){
			return 'gt3-core-elementor-icon dashicons dashicons-chart-line';
		}

		public function get_name(){
			return 'unlimited-charts';
		}

		public function get_categories(){
			return array( 'gt3-core-elements' );
		}

		public function __construct(array $data = [], $args = null){
			parent::__construct($data, $args);
			$this->actions();
			$this->add_script_depends('elementor-waypoints');
			$this->add_script_depends('gt3-unlimited-chart');
			$this->add_script_depends('gt3-unlimited-chart-frontend');
		}

		private function actions(){
			add_action('elementor/widgets/widgets_registered', function($widgets_manager){
				/* @var \Elementor\Widgets_Manager $widgets_manager */
				$widgets_manager->register_widget_type($this);
			});
		}

		protected function get_repeater_fields() {
			$repeater = new Repeater();

			$repeater = new Repeater();
			$repeater->add_control(
				'item_label',
				array(
					'label' => esc_html__('Label', 'gt3_unlimited_chart'),
					'type'  => Controls_Manager::TEXT,
				)
			);

			$repeater->add_control(
				'item_data',
				array(
					'label'   	=> esc_html__('Data', 'gt3_unlimited_chart'),
					'blank'		=> '',
					'default'	=> json_encode( array('25','45') ),
					'type'    	=> 'gt3-elementor-core-repeatable-text',
				)
			);

			$repeater->add_control(
				'bg_color',
				array(
					'label'   	=> esc_html__('Background Color', 'gt3_unlimited_chart'),
					'type'    	=> 'gt3-elementor-core-repeatable-text',
					'blank'		=> '#fe6384',
					'default'	=> json_encode( array('#fe6283','#36a2eb') ),
					'color_mode' => true,
				)
			);

			$repeater->add_control(
				'border_width',
				array(
					'label'   => esc_html__('Border Width', 'gt3_unlimited_chart'),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'0' => '0',
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'6' => '6',
						'7' => '7',
						'8' => '8',
						'9' => '9',
						'10' => '10',
					),
					'default' => '1',
				)
			);

			$repeater->add_control(
				'border_color',
				array(
					'label'   	=> esc_html__('Border Color', 'gt3_unlimited_chart'),
					'type'    	=> 'gt3-elementor-core-repeatable-text',
					'blank'		=> '#fe6384',
					'default'	=> json_encode( array('#fe6283','#36a2eb') ),
					'color_mode' => true,
					'condition' => array(
						'border_width!' => '0',
					)
				)
			);

			return $repeater->get_controls();
		}


		//////////////////////////

		protected function _register_controls(){


			$this->start_controls_section(
				'basic_section',
				array(
					'label' => esc_html__('Basic', 'gt3_unlimited_chart'),
				)
			);

			$this->add_control(
				'type',
				array(
					'label'   => esc_html__('Chart Type', 'gt3_unlimited_chart'),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'bar'  => esc_html__('Bar', 'gt3_unlimited_chart'),
						'horizontalBar' => esc_html__('Horizontal Bar', 'gt3_unlimited_chart'),
						'line'    => esc_html__('Line', 'gt3_unlimited_chart'),
						'pie' => esc_html__('Pie', 'gt3_unlimited_chart'),
						'doughnut' => esc_html__('Doughnut', 'gt3_unlimited_chart'),
						'polarArea' => esc_html__('PolarArea', 'gt3_unlimited_chart'),
						'radar' => esc_html__('Radar', 'gt3_unlimited_chart'),
					),
					'default' => 'bar',
				)
			);

			$this->add_control(
				'cutoutPercentage',
				array(
					'label'      => esc_html__('Cutout Percentage', 'gt3_unlimited_chart'),
					'type'       => Controls_Manager::SLIDER,
					'default'    => array(
						'size' => 60,
					),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 95,
							'step' => 1,
						),
					),
					'size_units'  => array( '' ),
					'condition'  => array(
						'type' => 'doughnut'
					),
				)
			);

			$this->add_control(
				'rotation',
				array(
					'label'      => esc_html__('Starting angle to draw arcs from.', 'gt3_unlimited_chart'),
					'type'       => Controls_Manager::SLIDER,
					'default'    => array(
						'size' => 0,
					),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 360,
							'step' => 1,
						),
					),
					'size_units'  => array( '' ),
					'condition'  => array(
						'type' => array('doughnut','pie'),
					),
				)
			);

			$this->add_control(
				'fill',
				array(
					'label'       => esc_html__('Fill Line?', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::SWITCHER,
					'default'     => '',
					'condition' => array(
						'type' => 'line',
					)
				)
			);

			$this->add_control(
				'beginAtZero',
				array(
					'label'       => esc_html__('Begin at Zero', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::SWITCHER,
					'default'     => 'yes',
				)
			);


			$this->add_control(
				'lineTension',
				array(
					'label'      => esc_html__('Bezier curve tension of the line. ', 'gt3_unlimited_chart'),
					'type'       => Controls_Manager::SLIDER,
					'default'    => array(
						'size' => 0.5,
					),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 1,
							'step' => 0.1,
						),
					),
					'size_units'  => array( '' ),
					'condition'  => array(
						'type' => array('line'),
					),
				)
			);



			$this->add_control(
				'tooltip',
				array(
					'label'       => esc_html__('Show Tooltips?', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::SWITCHER,
					'default'     => 'yes',
				)
			);

			$this->add_control(
				'labels',
				array(
					'label'   	=> esc_html__('Labels', 'gt3_unlimited_chart'),
					'blank'		=> '',
					'default'	=> json_encode( array('March','April','May') ),
					'type'    	=> 'gt3-elementor-core-repeatable-text',
				)
			);



			$this->add_control(
				'items',
				array(
					'label'       => esc_html__('Datasets', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::REPEATER,
					'default'     => array(
						array(
							'item_label'   => esc_html__('Dataset #1', 'gt3_unlimited_chart'),
							'item_data' => json_encode(array('25','45')),
							'bg_color' => json_encode( array('#fe6283','#36a2eb') ),
							'border_color' => json_encode( array('#fe6283','#36a2eb') ),
						),
					),
					'fields'      => array_values($this->get_repeater_fields()),
					'title_field' => '{{{ item_label }}}',
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'legend_section',
				array(
					'label' => esc_html__('Lagend', 'gt3_unlimited_chart'),
				)
			);

			$this->add_control(
				'legend',
				array(
					'label'       => esc_html__('Show Lagend?', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::SWITCHER,
					'default'     => 'yes',
				)
			);

			$this->add_control(
				'legend_position',
				array(
					'label'   => esc_html__('Legend position', 'gt3_unlimited_chart'),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'top'  		=> esc_html__('top', 'gt3_unlimited_chart'),
						'left'  	=> esc_html__('left', 'gt3_unlimited_chart'),
						'bottom' 	=> esc_html__('bottom', 'gt3_unlimited_chart'),
						'right'  	=> esc_html__('right', 'gt3_unlimited_chart'),
					),
					'default' => 'top',
					'condition' => array(
						'legend' => 'yes',
					)
				)
			);

			$this->add_control(
				'usePointStyle',
				array(
					'label'       => esc_html__('Use Point Style?', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::SWITCHER,
					'default'     => '',
					'condition' => array(
						'legend' => 'yes',
					)
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'scales_section',
				array(
					'label' => esc_html__('Scales', 'gt3_unlimited_chart'),
					'condition' => array(
						'type' => array('bar','horizontalBar','line'),
					)
				)
			);
			$this->add_control(
				'x_grid',
				array(
					'label'       => esc_html__('Show X Grid', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::SWITCHER,
					'default'     => 'yes',
				)
			);

			$this->add_control(
				'x_grid_color',
				array(
					'label'       => esc_html__('X Grid Color', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::COLOR,
					'label_block' => true,
					'default'	  => '#e7e7e7',
				)
			);

			$this->add_control(
				'x_grid_label',
				array(
					'label'     => esc_html__('X Grid Label', 'gt3_unlimited_chart'),
					'type'      => Controls_Manager::TEXT,
					'default'   => '',
				)
			);




			$this->add_control(
				'x_grid_label_font_size',
				array(
					'label'     => esc_html__('X Label font-size', 'gt3_unlimited_chart'),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 14,
					'min'       => '0',
					'step'      => 1,
				)
			);


			$this->add_control(
				'x_grid_label_color',
				array(
					'label'       => esc_html__('X Label Color', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::COLOR,
					'label_block' => true,
					'default'	  => '#666666',
				)
			);

			$this->add_control(
				'x_grid_ticks_font_size',
				array(
					'label'     => esc_html__('X Ticks font-size', 'gt3_unlimited_chart'),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 12,
					'min'       => '0',
					'step'      => 1,
				)
			);
			$this->add_control(
				'x_grid_ticks_color',
				array(
					'label'       => esc_html__('X Ticks Color', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::COLOR,
					'label_block' => true,
					'default'	  => '#666666',
				)
			);





			$this->add_control(
				'y_grid',
				array(
					'label'       => esc_html__('Show Y Grid', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::SWITCHER,
					'default'     => 'yes',
				)
			);
			$this->add_control(
				'y_grid_color',
				array(
					'label'       => esc_html__('Y Grid Color', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::COLOR,
					'label_block' => true,
					'default'	  => '#e7e7e7',
				)
			);
			$this->add_control(
				'y_grid_label',
				array(
					'label'     => esc_html__('Y Grid Label', 'gt3_unlimited_chart'),
					'type'      => Controls_Manager::TEXT,
					'default'   => '',
				)
			);

			$this->add_control(
				'y_grid_label_font_size',
				array(
					'label'     => esc_html__('Y Label font-size', 'gt3_unlimited_chart'),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 14,
					'min'       => '0',
					'step'      => 1,
				)
			);
			$this->add_control(
				'y_grid_label_color',
				array(
					'label'       => esc_html__('Y Label Color', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::COLOR,
					'label_block' => true,
					'default'	  => '#666666',
				)
			);
			$this->add_control(
				'y_grid_ticks_font_size',
				array(
					'label'     => esc_html__('Y Ticks font-size', 'gt3_unlimited_chart'),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 12,
					'min'       => '0',
					'step'      => 1,
				)
			);
			$this->add_control(
				'y_grid_ticks_color',
				array(
					'label'       => esc_html__('Y Ticks Color', 'gt3_unlimited_chart'),
					'type'        => Controls_Manager::COLOR,
					'label_block' => true,
					'default'	  => '#666666',
				)
			);
			$this->end_controls_section();

		}

		private function HexToRGB($hex = "#ffffff"){
	        $color = array();

	        if (strlen($hex) < 1) {
	            $hex = "#ffffff";
	        }elseif(strlen($hex) < 7){
	        	return $hex;
	        }elseif(strlen($hex) > 7){
	        	return $hex;
	        }

	        $color['r'] = hexdec(substr($hex, 1, 2));
	        $color['g'] = hexdec(substr($hex, 3, 2));
	        $color['b'] = hexdec(substr($hex, 5, 2));

	        return 'rgb('.$color['r'] . "," . $color['g'] . "," . $color['b'].')';
	    }

		// php
		protected function render(){
			$settings = array(
				'label'			=> '',
				'beginAtZero'	=> true,
				'labels'		=> '',
				'type'			=> '',
				'fill'			=> '',
				'lineTension'	=> 0.5,
				'labels'			=> '',
				'legend'			=> '',
				'legend_position'			=> '',
				'usePointStyle'			=> '',
				'tooltip'			=> '',
				'x_grid'			=> '',
				'x_grid_color'			=> '',
				'x_grid_label'			=> '',
				'x_grid_label_color'			=> '',
				'x_grid_label_font_size'			=> '',
				'x_grid_ticks_font_size'			=> '',
				'x_grid_ticks_color'			=> '',
				'y_grid'			=> '',
				'y_grid_color'			=> '',
				'y_grid_label'			=> '',
				'y_grid_label_color'			=> '',
				'y_grid_label_font_size'			=> '',
				'y_grid_ticks_font_size'			=> '',
				'y_grid_ticks_color'			=> '',
				'items'			=> array(),
				'from_elementor' => true,
			);

			$settings                 = wp_parse_args($this->get_settings(), $settings);

			$settings['items'];

			$this->add_render_attribute('wrapper', 'class', array(
				'gt3_unlimited_cart_wrapper',
			));

			$data = array (
				'labels' 	=> array(),
				'datasets' 	=> array()
			);

			$options = array();

			if (isset($settings['items']) && is_array($settings['items'])) {
				foreach ($settings['items'] as $index => $item) {
					if (!empty($item['item_data'])) {
						if (is_string($item['item_data'])) {
							$item['item_data'] = json_decode( $item['item_data'] , true);
						}
						$data['datasets'][$index]['data'] = $item['item_data'];
					}
					if (!empty($item['item_label'])) {
						$data['datasets'][$index]['label'] = $item['item_label'];
					}
					if (!empty($item['bg_color'])) {

						if (!empty($item['bg_color']) && is_string($item['bg_color'])) {
							$item['bg_color'] = json_decode( $item['bg_color'] , true);
							if ($item['bg_color'] && is_array($item['bg_color'])) {
								if (
									(!empty($settings['type']) && $settings['type'] == 'line') &&
									(!empty($settings['fill']) && $settings['fill'] == 'yes')
								){
									$item['bg_color'] = array($this->HexToRGB($item['bg_color'][0]));
								}else{
									foreach ($item['bg_color'] as $key => $value) {
										$item['bg_color'][$key] = $this->HexToRGB($value);
									}
								}

							}
						}

						if (is_array($item['bg_color']) && count($item['bg_color']) == 1 ) {
							$item['bg_color'] = $item['bg_color'][0];
						}

						$data['datasets'][$index]['backgroundColor'] = $item['bg_color'];
					}
					if (!empty($item['border_color'])) {

						if (!empty($item['border_color']) && is_string($item['border_color'])) {
							$item['border_color'] = json_decode( $item['border_color'] , true);
							if ($item['border_color'] && is_array($item['border_color'])) {
								foreach ($item['border_color'] as $key => $value) {
									$item['border_color'][$key] = $this->HexToRGB($value);
								}
							}
						}

						if (is_array($item['border_color']) && count($item['border_color']) == 1 ) {
							$item['border_color'] = $item['border_color'][0];
						}

						$data['datasets'][$index]['borderColor'] = $item['border_color'];
					}
					$data['datasets'][$index]['borderWidth'] = (int)$item['border_width'];

					if ((int)$item['border_width'] == 0) {
						$data['datasets'][$index]['showLine'] = false;
					}

					if (!empty($settings['fill'])) {
						$data['datasets'][$index]['fill'] = $settings['fill'] == 'yes' ? 'origin' : false;
					}else{
						$data['datasets'][$index]['fill'] = false;
					}

					if (isset($settings['lineTension']['size']) && !empty($settings['type']) && $settings['type'] == 'line' ) {
						$data['datasets'][$index]['lineTension'] = $settings['lineTension']['size'];
					}


					$data['datasets'][$index]['borderJoinStyle'] = array(12,15);

				}

			}


			if (!empty($settings['labels']) && is_string($settings['labels'])) {
				$data['labels'] = json_decode( $settings['labels'] , true);
			}

			$options['responsive'] = true;
			$options['maintainAspectRatio'] = false;

			if ($settings['type'] == 'doughnut') {
				$options['cutoutPercentage'] = (isset($settings['cutoutPercentage']['size'])) ? $settings['cutoutPercentage']['size'] : 60;
			}

			if (($settings['type'] == 'doughnut' || $settings['type'] == 'pie') && isset($settings['rotation']['size']) ) {
				$options['rotation'] = deg2rad ((int)$settings['rotation']['size'] + 270) ;
			}


			$options['animation'] = array(
				'duration' => 1500,
			);


			$options['layout'] = array(
				'padding' => array(
					'top' => $settings['type'] == 'polarArea' ? 8 : 0,
				)
			);

			$options['legend'] = array(
				'display' 	=> isset($settings['legend']) && $settings['legend'] == '' ? false : true,
				'position' 	=> isset($settings['legend_position']) ? $settings['legend_position'] : 'top',
				'labels'	=> array(
					'usePointStyle' => isset($settings['usePointStyle']) && $settings['usePointStyle'] == '' ? false : true,
				)
			);

			$options['tooltips'] = array(
				'enabled' => isset($settings['tooltip']) && $settings['tooltip'] == '' ? false : true,
			);

			if ($settings['type'] == 'radar' || $settings['type'] == 'pie' || $settings['type'] == 'polarArea' || $settings['type'] == 'doughnut') {
				# code...
			}else{
				$options['scales'] = array(
					'xAxes'	=> array(array(
						/*'barPercentage' => 0.9,*/
						'gridLines' => array(
							'display' 	=> isset($settings['x_grid']) && $settings['x_grid'] == '' ? false : true,
							'color'		=> !empty($settings['x_grid_color']) ? $settings['x_grid_color'] : '#e7e7e7',
						),
						'scaleLabel' => array(
							'display' => !empty($settings['x_grid_label']) ? true : false,
							'labelString' => !empty($settings['x_grid_label']) ? $settings['x_grid_label'] : '',
							'fontColor' => !empty($settings['x_grid_label_color']) ? $settings['x_grid_label_color'] : '',
							'fontSize' => !empty($settings['x_grid_label_font_size']) ? $settings['x_grid_label_font_size'] : '12'
						),
						'ticks' => array(
							'fontSize' => !empty($settings['x_grid_ticks_font_size']) ? $settings['x_grid_ticks_font_size'] : '12',
	                        'fontColor' => !empty($settings['x_grid_ticks_color']) ? $settings['x_grid_ticks_color'] : '',
						)
					)),
					'yAxes'	=> array(array(
						'gridLines' => array(
							'display' 	=> isset($settings['y_grid']) && $settings['y_grid'] == '' ? false : true,
							'color'		=> !empty($settings['y_grid_color']) ? $settings['y_grid_color'] : '#e7e7e7',
						),
						'scaleLabel' => array(
							'display' => !empty($settings['y_grid_label']) ? true : false,
							'labelString' => !empty($settings['y_grid_label']) ? $settings['y_grid_label'] : '',
							'fontColor' => !empty($settings['y_grid_label_color']) ? $settings['y_grid_label_color'] : '',
							'fontSize' => !empty($settings['y_grid_label_font_size']) ? $settings['y_grid_label_font_size'] : '12',
						),
						'ticks' => array(
							'beginAtZero' => isset($settings['beginAtZero']) && $settings['beginAtZero'] == '' ? false : true,
							'suggestedMax' => 1,
							'fontSize' => !empty($settings['y_grid_ticks_font_size']) ? $settings['y_grid_ticks_font_size'] : '12',
	                        'fontColor' => !empty($settings['y_grid_ticks_color']) ? $settings['y_grid_ticks_color'] : '',
						)
					)),
				);
			}


			$data = json_encode( $data );
			$options = json_encode( $options );

			$random = rand(1, 10000);
			?>
			<div class="gt3_unlimited_chart_wrapper" data-chart-type='<?php echo $settings['type']; ?>' data-chart-data='<?php echo $data; ?>' data-chart-options='<?php echo $options; ?>'>
				<canvas class="gt3_unlimited_chart gt3_chart_<?php echo (int)$random ?>" width="400" height="400"></canvas>
			</div><?php
		}

		// js
		protected function _content_template(){

		}

	}


