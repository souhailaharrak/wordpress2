<?php

namespace GT3\PhotoVideoGalleryPro\Block;

defined('ABSPATH') OR exit;


class Instagram extends Isotope_Gallery {
	protected $name = 'instagram';

	protected function getDefaultsAttributes(){
		return array_merge(
			parent::getDefaultsAttributes(),
			array(
				'source'   => array(
					'type'    => 'string',
					'default' => 'user',
				),
				'userName' => array(
					'type'    => 'string',
					'default' => '',
				),
			/*	'userID'   => array(
					'type'    => 'string',
					'default' => '',
				),*/
				'tag'      => array(
					'type'    => 'string',
					'default' => 'default',
				),
				'gridType' => array(
					'type'    => 'string',
					'default' => 'default',
				),
				'linkTo'   => array(
					'type'    => 'string',
					'default' => 'default',
				),
			)
		);
	}

	protected function getCheckTypeSettings(){
		return array_merge(
			parent::getCheckTypeSettings(),
			array()
		);
	}

	protected function construct(){
		$this->add_script_depends('isotope');
		parent::construct();
	}

	protected function get_from_cache($key){
		$images = get_transient('gt3pg_istagram_'.$key);
		if(false !== $images) {
			$images = json_decode($images, true);
		}

		return $images;
	}

	protected function set_cache($key, $images){
		set_transient($key, json_encode($images), DAY_IN_SECONDS);
	}

	protected function get_images_by_user($username){
		if(empty($username)) {
			return array();
		}

		$images = $this->get_from_cache('user--'.$username);
		if(false !== $images) {
			return $images;
		}

		$remote_get = wp_remote_get("https://www.instagram.com/${username}/?__a=1");
		$code       = wp_remote_retrieve_response_code($remote_get);
		$images     = array();
		if(200 === $code) {
			$body = wp_remote_retrieve_body($remote_get);
			try {
				$_body = json_decode($body, true);
				if(json_last_error()) {
					$body = array();
				} else {
					$body = $_body;
					unset($_body);
				}
			} catch(\Exception $exception) {
				$body = array();
			}

			if(is_array($body) && count($body)) {
				$body = $this->get_key('graphql', $body);
				$body = $this->get_key('user', $body);
				$body = $this->get_key('edge_owner_to_timeline_media', $body);
				$body = $this->get_key('edges', $body);

				$images = $this->parse_images($body);
			}
		}
		$this->set_cache('user--'.$username, $images);

		return $images;
	}

	protected function get_images_by_tag($tag){
		if(empty($tag)) {
			return array();
		}

		$images = $this->get_from_cache('tag--'.$tag);
		if(false !== $images) {
			return $images;
		}

		$remote_get = wp_remote_get("https://www.instagram.com/graphql/query/?query_id=17882293912014529&tag_name=${tag}&first=4");
		$code       = wp_remote_retrieve_response_code($remote_get);
		$images     = array();
		if(200 === $code) {
			$body = wp_remote_retrieve_body($remote_get);
			try {
				$_body = json_decode($body, true);
				if(json_last_error()) {
					$body = array();
				} else {
					$body = $_body;
					unset($_body);
				}
			} catch(\Exception $exception) {
				$body = array();
			}

			if(is_array($body) && count($body)) {
				$body = $this->get_key('data', $body);
				$body = $this->get_key('hashtag', $body);

				if(null !== $body) {
					$body = $this->get_key('edge_hashtag_to_media', $body);
					$body = $this->get_key('edges', $body);
				}

				$images = $this->parse_images($body);
			}
		}
		$this->set_cache('tag--'.$tag, $images);

		return $images;
	}

	protected function parse_images($body) {
		$images = array();

		if(is_array($body) && count($body)) {
			foreach($body as $image) {
				if(!key_exists('node', $image)) {
					continue;
				}

				$image    = $image['node'];
				$image    = array(
					'url'       => $image['display_url'],
					'width'     => $image['dimensions']['width'],
					'height'    => $image['dimensions']['height'],
					'shortcode' => $image['shortcode'],
					'type'      => key_exists('__typename', $image) && $image['__typename'] || 'image',
				);
				$images[] = $image;
			};
		}

		return $images;
	}


	protected function get_key($key, $array){
		if(is_array($array) && key_exists($key, $array)) {
			$array = $array[$key];
		}

		return $array;
	}

	protected function render($settings){
		if ($this->is_editor) {
			echo '<h4>Instagram gallery is not available at the moment</h4>';
		}
		return;



		$this->add_render_attribute('_wrapper', 'class', 'gt3-photo-gallery-pro--isotope_gallery');
		$this->add_render_attribute('_wrapper', 'class', 'gt3-photo-gallery-pro--instagram_gallery');

		$settings['lightbox'] = false;
		$settings['lazyLoad'] = false;

		if($settings['rightClick']) {
			$this->add_render_attribute(
				'wrapper', array(
					'oncontextmenu' => 'return false',
					'onselectstart' => 'return false'
				)
			);
		}

		$this->add_render_attribute(
			'wrapper', 'class', array(
				'gt3pg-isotope-gallery',
				'columns-'.$settings['columns'],
				'gallery-'.$this->name,
				'gallery-grid',
			)
		);

		$this->add_render_attribute(
			'wrapper',
			array(
				'data-cols' => $settings['columns'],
			)
		);

		if($settings['loadMoreFirst'] > 12) {
			$settings['loadMoreFirst'] = 12;
		}

		if($settings['source'] === 'user') {
			$images = $this->get_images_by_user($settings['userName']);
		} else {
			$images = $this->get_images_by_tag($settings['tag']);
		}

		$images_ = array_splice($images, 0, $settings['loadMoreFirst']);

		$this->data_settings = array(
			'lightbox'  => $settings['lightbox'],
			'id'        => $this->render_index,
			'uid'       => $this->_id,
			'grid_type' => $settings['gridType'],
			'lazyLoad'  => $settings['lazyLoad'],
			'source'    => $settings['source'],
			'linkTo'    => $settings['linkTo'],
			'images'    => $images_,
		);

		$this->add_style(
			'.gt3pg-isotope-item', array(
				'padding-right: %spx'  => $settings['margin'],
				'padding-bottom: %spx' => $settings['margin'],
			)
		);
		$this->add_style(
			'.gallery-isotope-wrapper', array(
				'margin-right: -%spx'  => $settings['margin'],
				'margin-bottom: -%spx' => $settings['margin'],
			)
		);

		$this->add_render_attribute('wrapper', 'class', $settings['gridType']);

		?>
		<div <?php $this->print_render_attribute_string('wrapper'); ?>>
			<div class="gallery-isotope-wrapper">
			</div>
			<?php
			$this->getPreloader(true);
			?>
		</div>

		<?php
	}
}
