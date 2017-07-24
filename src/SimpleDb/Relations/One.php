<?php namespace SimpleDb\Relations;

use SimpleDb\Data\Model;

class One extends Relation {

	/** @var string The local field pointing to the related data. */
	private $_local;

	/** @var string The foreign field matching the local field value. */
	private $_foreign;

	/**
	 * @internal Use {@link Relation::one()}.
	 */
	public function __construct($model, $local, $foreign) {
		parent::__construct($model);

		$this->_local = $local;
		$this->_foreign = $foreign;
	}

	public function fetch(Model $model) {
		$row = $this->getForeignTable()->oneWhere([$this->_foreign => $model->{$this->_local}]);
		return !empty($row) ? call_user_func([$this->model, 'from'], $row) : null;
	}

}