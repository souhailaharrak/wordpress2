<?php

namespace GT3\PhotoVideoGalleryPro\Block;

defined('ABSPATH') OR exit;

use GT3\PhotoVideoGalleryPro\Help\Types;
use GT3\PhotoVideoGalleryPro\Settings;
use GT3_Post_Type_Gallery;

class Loader {
	private static $instance = null;

	public static function instance(){
		if(!self::$instance instanceof self) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct(){
		$this->load();
	}


	public function load(){
		static $called = false;
		if($called) {
			$called = true;
			return;
		}


		$blocks = Settings::instance()->getBlocks();
		foreach($blocks as $block) {
			$block = __NAMESPACE__.'\\'.$block;
			if(class_exists($block)) {
				/** @var Basic $block */
				$block::instance();
			};
		}
	}
}
