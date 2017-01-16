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
	private $_local;

	/** @var string */
	private $_foreign;

	/**
	 * A relationship that equates to SELECT FROM {model::table} WHERE {foreign} = {local}.
	 *
	 * @param string $model The model this relationship produces.
	 * @param string $local The local column used to reference the foreign data.
	 * @param string $foreign The column in the foreign table that should match the value in the local column.
	 */
	public function __construct($model, $local, $foreign = 'id') {
		parent::__construct($model);

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
		$row = $this->getForeignTable()->one([$this->_foreign => $model->getFieldValue($this->_local)]);
		return empty($row) ? null : $row->populate($this->model);
	}

}