<?php namespace SimpleDb;

use Exception;

/**
 * A single row from a database query.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Row implements Populator {

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

	/**
	 * Populate a model with data from this row.
	 *
	 * @param string $class The name of the class to construct and populate.
	 *
	 * @return Model
	 *
	 * @throws Exception If the class does not exist.
	 * @throws Exception If the class does not inherit {@link Model}.
	 */
	public function populate($class) {
		if (class_exists($class)) {
			$model = new $class($this->_data);

			if ($model instanceof Model) {
				return $model;
			} else {
				throw new Exception('Class "' . $class . '" must inherit SimpleDb\Model.');
			}
		} else {
			throw new Exception('Class "' . $class . '" does not exist.');
		}
	}

}