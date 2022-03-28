<?php

namespace WPDaddy\Dom;

use DOMNodeList;

/**
 * Represents a Node list that can only contain Element nodes.
 *
 * @property-read int $length Number of Element nodes in this collection
 */
class HTMLCollection extends NodeList {
	use LiveProperty;

	/** @var Element[] */
	protected $list;
	protected $iteratorKey;

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct($domNodeList){
		$this->list = [];
		$this->rewind();

		for($i = 0, $n = $domNodeList->length; $i < $n; $i++) {
			$item = $domNodeList->item($i);

			if(!$item instanceof Element) {
				continue;
			}

			$this->list [] = $item;
		}
	}

	/**
	 * Returns the number of Elements contained in this Collection.
	 * Exposed as the $length property.
	 *
	 * @return int Number of Elements
	 */
	protected function prop_get_length(){
		return count($this->list);
	}

	/**
	 * @param string $name Returns the specific Node whose ID or, as a fallback,
	 *                     name matches the string specified by $name. Matching by name is only done
	 *                     as a last resort, and only if the referenced element supports the name
	 *                     attribute.
	 */
	public function namedItem($name){
		$namedElement = null;

// TODO: Use an XPath query here -- it's got to be less costly than iterating.
		foreach($this as $element) {
			if($element->getAttribute("id") === $name) {
				return $element;
			}

			if(is_null($namedElement)
			   && $element->getAttribute("name") === $name) {
				$namedElement = $element;
			}
		}

		return $namedElement;
	}

	/**
	 * Gets the nth Element object in the internal DOMNodeList.
	 *
	 * @param int $index
	 *
	 * @return Element|null
	 */
	public function item($index){
		$value = key_exists($index, $this->list) ? $this->list[$index] : null;

		return $value ? $value : null;
	}

// Iterator --------------------------------------------------------------------

	public function rewind(){
		$this->iteratorKey = 0;
	}

	public function key(){
		return $this->iteratorKey;
	}

	public function valid(){
		return isset($this->list[$this->key()]);
	}

	public function next(){
		$this->iteratorKey++;
	}

	public function current(){
		$value = $this->list[$this->key()];

		return $value ? $value : null;
	}

// ArrayAccess -----------------------------------------------------------------
	public function offsetExists($offset){
		return isset($offset, $this->list);
	}

	public function offsetGet($offset){
		return $this->item($offset);
	}

	public function offsetSet($offset, $value){
		throw new \BadMethodCallException("HTMLCollection's items are read only");
	}

	public function offsetUnset($offset){
		throw new \BadMethodCallException("HTMLCollection's items are read only");
	}

// Countable -------------------------------------------------------------------
	public function count(){
		return $this->length;
	}
}
