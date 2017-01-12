<?php namespace SimpleDb;

use Exception;

/**
 * A relationship of many external records.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class HasManyRelation extends Relation {

	/** @var string */
	private $_model;

	/** @var string */
	private $_foreign;

	/** @var string */
	private $_local;

	/**
	 * @param string $model The model this relationship produces.
	 * @param string $foreign The column in the foreign table used to reference the local data.
	 * @param string $local The local column that should match the value in the foreign column.
	 */
	public function __construct($model, $foreign, $local = 'id') {
		$this->_model = $model;
		$this->_foreign = $foreign;
		$this->_local = $local;
	}

	/**
	 * Fetch the related data.
	 *
	 * @param Model $model The model who the related data is attached to.
	 *
	 * @return Models
	 *
	 * @throws Exception If the table name of the foreign model could not be determined.
	 */
	public function fetch(Model $model) {
		if (method_exists($this->_model, 'getTable')) {
			$rows = Database::get()->all('SELECT * FROM ' . call_user_func(array($this->_model, 'getTable')) . ' WHERE ' . $this->_foreign . ' = ?', [$model->get($this->_local)]);
			return $rows->populate($this->_model);
		} else {
			throw new Exception('Could not determine the table name of the foreign model - ensure the provided model inherits SimpleDb\Model.');
		}
	}

}