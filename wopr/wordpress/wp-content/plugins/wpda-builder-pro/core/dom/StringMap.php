<?php

namespace WPDaddy\Dom;

use ArrayAccess;

class StringMap implements ArrayAccess {
	/** @var Element */
	protected $ownerElement;
	/** @var array */
	protected $properties;

	/**
	 * @param Attr[] $attributes
	 */
	public function __construct(
		$ownerElement,
		$attributes,
		$prefix = "data-"
	){
		$this->ownerElement = $ownerElement;
		$this->properties   = [];

		foreach($attributes as $attr) {
			if(strpos($attr->name, $prefix) !== 0) {
				continue;
			}

			$propName                    = $this->getPropertyName($attr);
			$this->properties[$propName] = $attr->value;
		}
	}

	public function __isset($name){
		return isset($this->properties[$name]);
	}

	public function __unset($name){
		unset($this->properties[$name]);
		$this->updateOwnerElement();
	}

	public function __get(string $name){

		$value = $this->properties[$name];

		return $value ? $value : null;
	}

	public function __set($name, $value){
		$this->properties[$name] = $value;
		$this->updateOwnerElement();
	}

	protected function updateOwnerElement(){
		foreach($this->properties as $key => $value) {
			$this->ownerElement->setAttribute(
				$this->getAttributeName($key),
				$value
			);
		}
	}

	protected function getPropertyName($attr){
		$name      = "";
		$nameParts = explode("-", $attr->name);

		foreach($nameParts as $i => $part) {
			if($i === 0) {
				continue;
			}

			if($i > 1) {
				$part = ucfirst($part);
			}

			$name .= $part;
		}

		return $name;
	}

	protected function getAttributeName($propName){
		$nameParts = preg_split(
			"/(?=[A-Z])/",
			$propName
		);
		array_unshift($nameParts, "data");
		$nameParts = array_map("strtolower", $nameParts);

		return implode("-", $nameParts);
	}

	/**
	 * @link https://php.net/manual/en/arrayaccess.offsetexists.php
	 */
	public function offsetExists($offset){
		return $this->__isset($offset);
	}

	/**
	 * @link https://php.net/manual/en/arrayaccess.offsetget.php
	 */
	public function offsetGet($offset){
		return $this->__get($offset);
	}

	/**
	 * @link https://php.net/manual/en/arrayaccess.offsetset.php
	 */
	public function offsetSet($offset, $value){
		$this->__set($offset, $value);
	}

	/**
	 * @link https://php.net/manual/en/arrayaccess.offsetunset.php
	 */
	public function offsetUnset($offset){
		$this->__unset($offset);
	}
}
