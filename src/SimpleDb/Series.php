<?php namespace SimpleDb;

use Iterator;
use ArrayAccess;

/**
 * A series of iterable data.
 *
 * @property-read mixed[] $content The contents of this series.
 * @property-read Row $first The first item in this series.
 * @property-read int $count The amount of items in this series.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
abstract class Series implements Iterator, ArrayAccess {

	/** @var int */
	private $_index = 0;

	/** @var mixed[] */
	private $_content = [];

	public function __construct(array $content = []) {
		$this->_content = $content;
	}

	public function __get($prop) {
		if ($prop === 'content') return $this->_content;
		if ($prop === 'first') return count($this->_content) > 0 ? $this->_content[0] : null;
		if ($prop === 'count') return count($this->_content);

		return null;
	}

	/**
	 * Execute a provided function for each item in this series.
	 *
	 * @param callable $function The function to execute. This function is provided the value as the first argument and
	 * the key as the second.
	 */
	public function each(callable $function) {
		foreach ($this->_content as $key => $value) {
			$function($value, $key);
		}
	}

	/** @internal */
	public function rewind() { $this->_index = 0; }

	/**
	 * @internal
	 *
	 * @return int
	 */
	public function key() { return $this->_index; }

	/** @internal */
	public function next() { $this->_index += 1; }

	/** @internal */
	public function valid() { return isset($this->_content[$this->_index]); }

	/** @internal */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) $this->_content[] = $value;
		else $this->_content[$offset] = $value;
	}

	/** @internal */
	public function offsetExists($offset) {
		return isset($this->_content[$offset]);
	}

	/** @internal */
	public function offsetUnset($offset) {
		unset($this->_content[$offset]);
	}

	/** @internal */
	public function offsetGet($offset) {
		return isset($this->_content[$offset]) ? $this->_content[$offset] : null;
	}

}