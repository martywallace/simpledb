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
	 * A relationship of one foreign model.
	 *
	 * @param string $model The type of model this relationship produces.
	 * @param string $local The local field that points to the related model.
	 * @param string $foreign The foreign field that matches the value of the local field.
	 *
	 * @return One
	 */
	public static function one($model, $local, $foreign = 'id') {
		return new One($model, $local, $foreign);
	}

	/**
	 * A relationship of many foreign models.
	 *
	 * @param string $model The type of model this relationship produces.
	 * @param string $foreign The foreign field pointing to the local model.
	 * @param string $local The local field that matches the value of the foreign field.
	 *
	 * @return Many
	 */
	public static function many($model, $foreign, $local = 'id') {
		return new Many($model, $foreign, $local);
	}

	/**
	 * A relationship of many foreign models through a junction table.
	 *
	 * @param string $model The type of model this relationship produces.
	 * @param string $junctionTable The junction table.
	 * @param string $junctionLocal The column within the junction table pointing to the local model.
	 * @param string $junctionForeign The column within the junction table pointing to the foreign model.
	 * @param string $local The local column linked to the junction table.
	 * @param string $foreign The foreign column pointing to the junction table.
	 *
	 * @return Via
	 */
	public static function via($model, $junctionTable, $junctionLocal, $junctionForeign, $local = 'id', $foreign = 'id') {
		return new Via($model, $junctionTable, $junctionLocal, $junctionForeign, $local, $foreign);
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