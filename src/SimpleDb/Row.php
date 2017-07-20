<?php namespace SimpleDb;

use Exception;
use JsonSerializable;

/**
 * A single row from a database query.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Row implements JsonSerializable {

	/** @var array */
	private $_data = [];

	public function __construct() {
		foreach ($this as $column => $value) {
			if (!in_array($column, ['_data'])) {
				$this->_data[$column] = $value;
			}
		}
	}

	/**
	 * Get the column names attached to this row.
	 *
	 * @return string[]
	 */
	public function getColumns() {
		return array_keys($this->_data);
	}

	/**
	 * Get the data attached to this row.
	 *
	 * @return string[]
	 */
	public function getData() {
		return $this->_data;
	}

	public function jsonSerialize() {
		return $this->_data;
	}

}