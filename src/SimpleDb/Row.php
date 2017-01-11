<?php namespace SimpleDb;

use Exception;

/**
 * A single row from a database query.
 *
 * @property-read string[] $columns The column names bound to this row.
 * @property-read mixed[] $data The row data as a simple PHP array.
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
			$model = new $class($this->data);

			if ($model instanceof Model) {
				return $model;
			} else {
				throw new Exception('Class "' . $class . '" must inherit SimpleDb\Model.');
			}
		} else {
			throw new Exception('Class "' . $class . '" does not exist.');
		}
	}

	public function __get($prop) {
		if ($prop === 'columns') return array_keys($this->_data);
		if ($prop === 'data') return $this->_data;

		return null;
	}

}