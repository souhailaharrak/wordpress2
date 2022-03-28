<?php

namespace GT3\PhotoVideoGalleryPro\Block;

defined('ABSPATH') or exit;

use GT3\PhotoVideoGalleryPro\Help\Types;
use GT3_Post_Type_Gallery;

class Carousel extends Basic {
	protected function getDefaultsAttributes(){
		return array_merge(
			parent::getDefaultsAttributes(),
			array(
				'type'        => array(
					'type'    => 'string',
					'default' => 'slider',
				),     //
				'centerMode'  => array(
					'type'    => 'bool',
					'default' => false,
				),       //
				'speed'       => array(
					'type'    => 'number',
					'default' => 300,
				),        //
				'fixedHeight' => array(
					'type'    => 'number',
					'default' => 300,
				),        //
				'heightRatio' => array(
					'type'    => 'floor',
					'default' => 70,
				),      //
				'perPage'     => array(
					'type'    => 'number',
					'default' => 1,
				),        //
				'perMove'     => array(
					'type'    => 'number',
					'default' => 1,
				),        //
				'start'       => array(
					'type'    => 'number',
					'default' => 0,
				),        //
				'gap'         => array(
					'type'    => 'number',
					'default' => 0,
				),        //
				'arrows'      => array(
					'type'    => 'bool',
					'default' => true,
				),       //
				'dots'        => array(
					'type'    => 'bool',
					'default' => true,
				),       //
				'autoplay'    => array(
					'type'    => 'bool',
					'default' => false,
				),       //
				'interval'    => array(
					'type'    => 'number',
					'default' => 3000,
				),        //
			)
		);
	}

	protected $name = 'carousel';

	protected function construct(){
	}

	protected function getCheckTypeSettings(){
		return array_merge(
			parent::getCheckTypeSettings(),
			array(
				'type'        => Types::TYPE_STRING,     //
				'centerMode'  => Types::TYPE_BOOL,       //
				'speed'       => Types::TYPE_INT,        //
				'fixedHeight' => Types::TYPE_INT,        //
				'heightRatio' => Types::TYPE_FLOAT,      //
				'perPage'     => Types::TYPE_INT,        //
				'perMove'     => Types::TYPE_INT,        //
				'start'       => Types::TYPE_INT,        //
				'gap'         => Types::TYPE_INT,        //
				'arrows'      => Types::TYPE_BOOL,       //
				'dots'        => Types::TYPE_BOOL,       //
				'autoplay'    => Types::TYPE_BOOL,       //
				'interval'    => Types::TYPE_INT,        //
			)
		);
	}

	protected function renderItem($image, &$settings){
		$render                  = '';
		$this->active_image_size = $settings['imageSize'];

//		 data-splide-youtube
//		$video = $this->get_video_type_from_description();

		$video_url_global = get_post_meta($image['id'], 'gt3_video_url', true);
		$video_url_global = (is_string($video_url_global) && !empty(trim($video_url_global))) ? $video_url_global : '';

		$local_video = key_exists('videoLink', $image) ? $image['videoLink'] : array();

		$video = $this->has_local_video($local_video);

		if(!$video) {
			$video = $this->has_global_video($video_url_global);
		}
		if($video) {
			switch($video['type']) {
				case  'youtube':
					$video = ' data-splide-youtube="https://www.youtube.com/watch?v='.$video['url'].'"';
					break;
				case  'vimeo':
					$video = ' data-splide-vimeo="https://vimeo.com/'.$video['url'].'"';
					break;
			}
		} else {
			$video = '';
		}

		$render .= '<span class="gt3_pro-carousel__slide '.$image['item_class'].'" '.$video.'>';
		$render .= $this->wp_get_attachment_image($image['id'], $settings['imageSize']);
		$render .= '</span>';

		return $render;
	}

	protected function render($settings){
		$this->checkImagesNoEmpty($settings);
		if(!count($settings['ids'])) {
			return;
		}

		if($settings['imageSize'] === 'thumbnail') {
			$settings['imageSize'] = 'medium_large';
		}

		if(!isset($GLOBALS['gt3pg']) || !is_array($GLOBALS['gt3pg']) ||
		   !isset($GLOBALS['gt3pg']['extension']) || !is_array($GLOBALS['gt3pg']['extension']) ||
		   !isset($GLOBALS['gt3pg']['extension']['pro_optimized'])
		) {
			if($settings['imageSize'] === 'gt3pg_optimized') {
				$settings['imageSize'] = 'large';
			}
		}

		if($settings['rightClick']) {
			$this->add_render_attribute(
				'wrapper', array(
					'oncontextmenu' => 'return false',
					'onselectstart' => 'return false',
				)
			);
		}

		$this->add_render_attribute(
			'wrapper', 'class', array(
				'gallery-'.$this->name,
			)
		);
		$ROOT            = 'gt3_pro-carousel';
		$ELEMENT_CLASSES = array(
			'root'       => $ROOT,
			'slider'     => $ROOT."__slider",
			'track'      => $ROOT."__track",
			'list'       => $ROOT."__list",
			'slide'      => $ROOT."__slide",
			'container'  => $ROOT."__slide__container",
			'arrows'     => $ROOT."__arrows",
			'arrow'      => $ROOT."__arrow",
			'prev'       => $ROOT."__arrow--prev",
			'next'       => $ROOT."__arrow--next",
			'pagination' => $ROOT."__pagination",
			'page'       => $ROOT."__pagination__page",
			'clone'      => $ROOT."__slide--clone",
			'progress'   => $ROOT."__progress",
			'bar'        => $ROOT."__progress__bar",
			'autoplay'   => $ROOT."__autoplay",
			'play'       => $ROOT."__play",
			'pause'      => $ROOT."__pause",
			'spinner'    => $ROOT."__spinner",
			'sr'         => $ROOT."__sr"
		);

		$this->data_settings = array(
			'id'              => $this->render_index,
			'uid'             => $this->_id,
			'carouselOptions' => array(
				'type'        => $settings['type'],
				'focus'       => $settings['centerMode'] ? 'center' : 0,
				'speed'       => $settings['speed'],
				'fixedHeight' => $settings['fixedHeight'].'%',
				'perPage'     => $settings['perPage'],
				'perMove'     => $settings['perMove'],
				'start'       => $settings['start'],
				'gap'         => $settings['gap'],
				'arrows'      => $settings['arrows'],
				'dots'        => $settings['dots'],
				'autoplay'    => $settings['autoplay'],
				'interval'    => $settings['interval'],
				'classes'     => $ELEMENT_CLASSES,
			)
		);

		$items      = '';
		$foreachIds = $settings['ids'];

		foreach($foreachIds as $id) {
			$items .= $this->renderItem($id, $settings);
		}

		$this->add_render_attribute('wrapper', 'class', 'gt3_pro-carousel__track')
		?>
		<div class="gt3_pro-carousel">
			<div <?php $this->print_render_attribute_string('wrapper'); ?>>
				<div class="gt3_pro-carousel__list">
					<?php
					echo $items; // XSS Ok
					?>
				</div>
				<?php
				//				$this->getPreloader();
				?>
			</div>
		</div>
		<?php
	}
}
