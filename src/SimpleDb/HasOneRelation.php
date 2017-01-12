<?php namespace SimpleDb;

use Exception;

/**
 * A relationship of one external record.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class HasOneRelation extends Relation {

	/** @var string */
	private $_model;

	/** @var string */
	private $_local;

	/** @var string */
	private $_foreign;

	/**
	 * @param string $model The model this relationship produces.
	 * @param string $local The local column used to reference the foreign data.
	 * @param string $foreign The column in the foreign table that should match the value in the local column.
	 */
	public function __construct($model, $local, $foreign = 'id') {
		$this->_model = $model;
		$this->_local = $local;
		$this->_foreign = $foreign;
	}

	/**
	 * Fetch the related data.
	 *
	 * @param Model $model The model who the related data is attached to.
	 *
	 * @return Model
	 *
	 * @throws Exception If the table name of the foreign model could not be determined.
	 */
	public function fetch(Model $model) {
		if (method_exists($this->_model, 'getTable')) {
			$row = Database::get()->table(call_user_func(array($this->_model, 'getTable')))->one($model->getFieldValue($this->_local), $this->_foreign);
			return empty($row) ? null : $row->populate($this->_model);
		} else {
			throw new Exception('Could not determine the table name of the foreign model - ensure the provided model inherits SimpleDb\Model.');
		}
	}

}