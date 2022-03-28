<?php

namespace WPDaddy\Builder;
defined('ABSPATH') OR exit;

use DOMDocument;
use DOMXPath;
use Elementor\Plugin as Elementor_Plugin;
use WPDaddy\Builder\Library\Basic;
use WPDaddy\Builder\Library\Footer;
use WPDaddy\Builder\Library\Header;
use WPDaddy\Dom\HTMLDocument;

class Buffer {
	private static $instance = null;

	/** @return self */
	public static function instance() {
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct() {
		if ($this->is_work()) {
			ob_start(array($this, 'ob_finish'));
			add_action('wp_enqueue_scripts', array($this, 'wp_head'), 0);
			add_action('shutdown', array($this, 'ob_finish'));
			add_filter('cache_enabler_before_store', array($this, 'ob_finish'));
		}
	}

	private function is_work() {
		static $answer = null;
		if (!is_null($answer)) {
			return $answer;
		}

		if (is_admin() || defined('WPDA_PANEL_ENABLED') || defined('DOING_AJAX')) {
			return ($answer = false);
		}

		$answer = true;

		return $answer;
	}

	public function wp_head() {
		global $post;
		$header    = false;
		$footer    = false;
		$_header   = false;
		$_footer   = false;
		$elementor = Elementor_Plugin::instance();

		$settings = Settings::instance()->get_settings();

		if (!$this->is_work()) {
			return;
		}

		if($elementor->preview->is_preview() || is_singular()) {
			$post_id      = $elementor->preview->is_preview() ? $elementor->preview->get_post_id() : (is_singular() ? $post->ID : 0);
			$preview      = $elementor->documents->get($post_id);
			$print_assets = ($preview instanceof Basic);

			if($print_assets) {
				Assets::enqueue_style('wpda-elementor-core-frontend', 'frontend/frontend.css');
				Assets::enqueue_script('wpda-elementor-core-frontend', 'frontend/frontend.js');
				do_action('wpda-builder/enqueue_assets');

				return;
			}
		}


		if (is_singular()) {
			$_header = get_post_meta(get_the_ID(), '_wpda-builder-header', true);
			if (!empty($_header)) {
				$_header = get_post($_header);
				if ($_header && $_header->post_status === 'publish') {
					$header = $elementor->frontend->get_builder_content_for_display($_header->ID);
//					if(empty($header)) {
//						$header = $_header = false;
//					}
				} else {
					$header = $_header = false;
				}
			} else {
				$header = $_header = false;
			}

			$_footer = get_post_meta(get_the_ID(), '_wpda-builder-footer', true);
			if (!empty($_footer)) {
				$_footer = get_post($_footer);
				if ($_footer && $_footer->post_status === 'publish') {
					$footer = $elementor->frontend->get_builder_content_for_display($_footer->ID);
//					if(empty($footer)) {
//						$footer = $_footer = false;
//					}
				} else {
					$footer = $_footer = false;
				}
			} else {
				$footer = $_footer = false;
			}
		}

		if (false === $header) {
			$headers            = new \WP_Query(
				array(
					'post_type'      => 'elementor_library',
					'posts_per_page' => '-1',
					'meta_query'     => array_merge(
						array(
							'relation' => 'AND',
						),
						array(
							array(
								'key'   => '_elementor_template_type',
								'value' => 'wpda-header',
							),
							array(
								'key'   => '_wpda-builder-active',
								'value' => true,
							)
						)
					),
					'fields'         => 'ids',
					'no_found_rows'  => true
				)
			);
			$default_conditions = array(
				array(
					'type'  => 'include',
					'key'   => 'none',
					'value' => [],
				)
			);
			if ($headers->have_posts()) {
				$all_headers  = array();
				$is_condition = false;
				foreach ($headers->posts as $_header_id) {
					$conditions = get_post_meta($_header_id, '_wpda-builder-conditions', true);
					try {
						$conditions = json_decode($conditions, true);
						if (json_last_error() || !is_array($conditions)) {
							$conditions = $default_conditions;
						}
					} catch (\Exception $ex) {
						$conditions = $default_conditions;
					}

					foreach ($conditions as $condition) {
						$condition = $condition['key'];

						if ($condition === 'all') {
							$all_headers[] = $_header_id;
							break;
						}
						$is_condition = ($condition !== 'none');
						if ($is_condition) {
							$is_condition = (
								($condition === 'all') || (function_exists($condition)
								                           && !!call_user_func($condition)));
							if ($is_condition) {
								$_header = $_header_id;
								$header  = $elementor->frontend->get_builder_content_for_display($_header);
								break;
							}
						}
					}
					if (false !== $header) {
						break;
					}
				}
				if (false === $header && count($all_headers)) {
					foreach ($all_headers as $header_id) {
						$header = $elementor->frontend->get_builder_content_for_display($header_id);

						$_header = $header_id;
						if (!empty($header)) {
							break;
						}
					}
				}
			}
		}

		if (false === $footer) {
			$footers            = new \WP_Query(
				array(
					'post_type'      => 'elementor_library',
					'posts_per_page' => '-1',
					'meta_query'     => array_merge(
						array(
							'relation' => 'AND',
						),
						array(
							array(
								'key'   => '_elementor_template_type',
								'value' => 'wpda-footer',
							),
							array(
								'key'   => '_wpda-builder-active',
								'value' => true,
							)
						)
					),
					'fields'         => 'ids',
					'no_found_rows'  => true
				)
			);
			$default_conditions = array(
				array(
					'type'  => 'include',
					'key'   => 'none',
					'value' => [],
				)
			);
			if ($footers->have_posts()) {
				$all_footers  = array();
				$is_condition = false;
				foreach ($footers->posts as $_footer_id) {
					$conditions = get_post_meta($_footer_id, '_wpda-builder-conditions', true);
					try {
						$conditions = json_decode($conditions, true);
						if (json_last_error() || !is_array($conditions)) {
							$conditions = $default_conditions;
						}
					} catch (\Exception $ex) {
						$conditions = $default_conditions;
					}

					foreach ($conditions as $condition) {
						$condition = $condition['key'];

						if ($condition === 'all') {
							$all_footers[] = $_footer_id;
							break;
						}
						$is_condition = ($condition !== 'none');
						if ($is_condition) {
							$is_condition = (
								($condition === 'all') || (function_exists($condition)
								                           && !!call_user_func($condition)));
							if ($is_condition) {
								$_footer = $_footer_id;
								$footer  = $elementor->frontend->get_builder_content_for_display($_footer);
								break;
							}
						}
					}
					if (false !== $footer) {
						break;
					}
				}
				if (false === $footer && count($all_footers)) {
					foreach ($all_footers as $footer_id) {
						$footer = $elementor->frontend->get_builder_content_for_display($footer_id);

						$_footer = $footer_id;
						if (!empty($footer)) {
							break;
						}
					}
				}
			}
		}

		wp_cache_delete('render_header', 'wpda_builder');
		wp_cache_delete('render_footer', 'wpda_builder');
		if (!empty($_header) || !empty($_footer)) {
			Assets::enqueue_style('wpda-elementor-core-frontend', 'frontend/frontend.css');
			Assets::enqueue_script('wpda-elementor-core-frontend', 'frontend/frontend.js', array('jquery'));

			do_action('wpda-builder/enqueue_assets');

			add_action('wp_footer', function() use ($elementor) {
				if (!did_action('elementor/frontend/before_enqueue_styles')) {
					$elementor->frontend->enqueue_styles();
					$elementor->frontend->enqueue_scripts();
				}
			}, 30);



			wp_cache_set('render_header', $header, 'wpda_builder', 30);
			wp_cache_set('render_footer', $footer, 'wpda_builder', 30);

			add_filter('theme/print_header', array($this, 'print_header'));
			add_filter('theme/print_footer', array($this, 'print_footer'));

			add_filter( 'body_class', function($classes) {
				foreach($classes as $class) {
					if (strpos($class, 'elementor-page') !== false) {
						$classes[] = 'elementor-page';
						break;
					}
				}
				return $classes;
			} );
		}

	}

	public function print_header() {
		$content = wp_cache_get('render_header', 'wpda_builder');
		wp_cache_delete('render_header', 'wpda_builder');
		if (false !== $content) {
			echo $content;

			return true;
		}


		return false;
	}

	public function print_footer() {
		$content = wp_cache_get('render_footer', 'wpda_builder');
		wp_cache_delete('render_footer', 'wpda_builder');
		if (false !== $content) {
			echo $content;

			return true;
		}


		return false;
	}

	public function ob_finish($buffer) {
		$header = wp_cache_get('render_header', 'wpda_builder');
		$footer = wp_cache_get('render_footer', 'wpda_builder');
		wp_cache_delete('render_header', 'wpda_builder');
		wp_cache_delete('render_footer', 'wpda_builder');

		if (!$this->is_work() || (false === $header && false === $footer)) {
			return false;
		}
		$func = function_exists('mb_strpos') ? 'mb_strpos' : 'strpos';
		if (call_user_func($func, $buffer, '<html') === false) {
			return false;
		}

		$settings = Settings::instance()->get_settings();
		if (empty($settings['header_area']) && empty($settings['footer_area'])) {
			return false;
		}
		$changed = false;

		if (call_user_func($func, $buffer, '<noscript') !== false) {
			$buffer = preg_replace('#<noscript>(.*?)</noscript>#', '', $buffer);
		}

		$document = new HTMLDocument($buffer);
		$oldNode  = $document->querySelector($settings['header_area']);

		if (null !== $oldNode) {
			$content = $header;
			if (false !== $content) {
				$changed = true;
				$replacement = $document->createDocumentFragment();
				$replacement->appendHTML($content);
				$oldNode->wpda_replaceWith($replacement);
			}
		}

		$oldNode = $document->querySelector($settings['footer_area']);
		if (null !== $oldNode) {
			$content = $footer;
			if (false !== $content) {
				$changed = true;
				$replacement = $document->createDocumentFragment();
				$replacement->appendHTML($content);
				$oldNode->wpda_replaceWith($replacement);
			}
		}

		return $changed ? $document : $buffer;
	}

}
