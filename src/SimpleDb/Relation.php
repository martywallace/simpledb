<?php namespace SimpleDb;

use Exception;

/**
 * A database relationship.
 *
 * @property-read string $model The type of model that this relationship produces.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
abstract class Relation {

	/** @var string */
	private $_model = null;

	public function __construct($model) {
		$this->_model = $model;
	}

	public function __get($prop) {
		if ($prop === 'model') return $this->_model;

		return null;
	}

	/**
	 * Fetch related data.
	 *
	 * @param Model $model The model to fetch related data through.
	 *
	 * @return Model|Models
	 */
	abstract public function fetch(Model $model);

	/**
	 * Get the table associated with the model that this relationship produces.
	 *
	 * @return Table
	 *
	 * @throws Exception The table could not be determined.
	 */
	public function getForeignTable() {
		if (method_exists($this->_model, 'getTable')) {
			$table = call_user_func(array($this->_model, 'getTable'));

			if ($table instanceof Table) {
				return $table;
			} else {
				throw new Exception('Calling getTable() on "' . $this->_model . '" did not provide a Table instance.');
			}
		} else {
			throw new Exception('Could not determine the table name of the foreign model - ensure the provided model inherits SimpleDb\Model.');
		}
	}

}