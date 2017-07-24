<?php namespace SimpleDb\Relations;

use SimpleDb\Data\Model;

class Many extends Relation {

	/** @var string The foreign field matching the local field value. */
	private $_foreign;

	/** @var string The local field pointing to the related data. */
	private $_local;

	/**
	 * @internal Use {@link Relation::many()}.
	 */
	public function __construct($model, $foreign, $local) {
		parent::__construct($model);

		$this->_foreign = $foreign;
		$this->_local = $local;
	}

	public function fetch(Model $model) {
		$rows = $this->getForeignTable()->allWhere([$this->_foreign => $model->{$this->_local}]);
		return call_user_func([$this->model, 'from'], $rows);
	}

}