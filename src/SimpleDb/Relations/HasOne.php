<?php namespace SimpleDb\Relations;

use Exception;
use SimpleDb\Data\Model;

class HasOne extends Relation {

	/** @var string The local field pointing to the related data. */
	private $_local;

	/** @var string The foreign field matching the local field value. */
	private $_foreign;

	/**
	 * @internal Use {@link Relation::hasOne()}.
	 */
	public function __construct($model, $local, $foreign) {
		parent::__construct($model);

		$this->_local = $local;

		if (empty($foreign)) {
			$keys = $this->getForeignTable()->getPrimaryColumns();

			if (count($keys) > 0) {
				// Use the first primary key.
				$this->_foreign = $keys[0]->name;
			} else {
				throw new Exception('There are no primary keys to use by default.');
			}
		} else {
			$this->_foreign = $foreign;
		}
	}

	public function fetch(Model $model) {
		return $this->getForeignTable()->oneWhere([
			$this->_foreign => $model->{$this->_local}
		]);
	}

}