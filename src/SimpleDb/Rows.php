<?php namespace SimpleDb;

use Iterator;

/**
 * A set of rows from a database query.
 *
 * @property-read Row $first The first row in this series.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Rows implements Iterator {

	/** @var int */
	private $_index = 0;

	/** @var Row[] */
	private $_rows = [];

	public function __construct($rows = []) {
		$this->_rows = $rows;
	}

	public function __get($prop) {
		if ($prop === 'first') return count($this->_rows) > 0 ? $this->_rows[0] : null;

		return null;
	}

	/** @internal */
	public function rewind() { $this->_index = 0; }

	/**
	 * @internal
	 *
	 * @return Row
	 */
	public function current() { return $this->_rows[$this->_index]; }

	/**
	 * @internal
	 *
	 * @return int
	 */
	public function key() { return $this->_index; }

	/** @internal */
	public function next() { $this->_index += 1; }

	/** @internal */
	public function valid() { return isset($this->_rows[$this->_index]); }

}