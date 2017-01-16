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
	private $_foreign;

	/** @var string */
	private $_local;

	/**
	 * A relationship that equates to SELECT FROM {model::table} WHERE {foreign} = {local}.
	 *
	 * @param string $model The model this relationship produces.
	 * @param string $foreign The column in the foreign table used to reference the local data.
	 * @param string $local The local column that should match the value in the foreign column.
	 */
	public function __construct($model, $foreign, $local = 'id') {
		parent::__construct($model);

		$this->_foreign = $foreign;
		$this->_local = $local;
	}

	/**
	 * Fetch the related data.
	 *
	 * @param Model $model The model who the related data is attached to.
	 *
	 * @return Models
	 */
	public function fetch(Model $model) {
		return $this->getForeignTable()->allWhere([$this->_foreign => $model->getFieldValue($this->_local)])->populate($this->model);
	}

}