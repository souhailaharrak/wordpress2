<?php

namespace WPDaddy\Dom;

use DOMNodeList;
use ArrayAccess;
use Countable;
use Iterator;

/**
 * @property-read int $length
 */
class NodeList implements Iterator, ArrayAccess, Countable {
	use LiveProperty;

	protected $list;
	protected $iteratorKey;

	public function __construct(DOMNodeList $domNodeList){
		$this->list = $domNodeList;
	}

	/**
	 * Returns the number of Nodes contained in this Collection. On the
	 * NodeList class this counts all types of Node object,
	 * not just Elements.
	 *
	 * @return int Number of Elements
	 */
	protected function prop_get_length(){
		return $this->list->length;
	}

	/**
	 * Gets the nth Element object in the internal DOMNodeList.
	 *
	 * @param int $index
	 *
	 * @return Element|null
	 */
	public function item($index){
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		$value = $this->list->item($index);

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

	/** @return Node|null */
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
		throw new \BadMethodCallException("NodeList's items are read only");
	}

	public function offsetUnset($offset){
		throw new \BadMethodCallException("NodeList's items are read only");
	}

// Countable -------------------------------------------------------------------
	public function count(){
		return $this->length;
	}
}
