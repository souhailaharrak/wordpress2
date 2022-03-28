<?php

namespace WPDaddy\Dom;

defined('ABSPATH') OR exit;

class Autoload {
	/**
	 * @param string $className
	 */
	public static function autoload($className){
		if(false === strpos($className, __NAMESPACE__.'\\')) {
			return;
		}

		$file = __DIR__.'/'.str_replace(__NAMESPACE__.'\\', '', $className).'.php';
		if(stream_resolve_include_path($file)) {
			require_once $file;
		}
	}
}

try {
	spl_autoload_register(array( Autoload::class, 'autoload' ));
} catch(\Exception $e) {
}
