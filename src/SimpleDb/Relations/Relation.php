<?php namespace SimpleDb\Relations;

use Exception;
use SimpleDb\Data\Table;
use SimpleDb\Data\Model;

/**
 * A database relationship.
 *
 * @property-read string $model The type of model that this relationship produces.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
abstract class Relation {

	/**
	 * A relationship of one foreign record being referenced by a local key.
	 *
	 * @param string $model The type of model this relationship generates.
	 * @param string $local The local field that points to the related model.
	 * @param string $foreign The foreign field that matches the value of the local field. Defaults to the first
	 * {@link Model::getPrimaryFields() primary field} of the related model.
	 *
	 * @return HasOne
	 */
	public static function hasOne($model, $local, $foreign = null) {
		return new HasOne($model, $local, $foreign);
	}

	/**
	 * A relationship of one foreign record referencing the local record.
	 *
	 * @param $model
	 */
	public function belongsTo($model) {
		//
	}

	public function hasMany($model) {
		//
	}

	public function hasManyVia($model) {
		//
	}

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
	 * @return Model|Model[]
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