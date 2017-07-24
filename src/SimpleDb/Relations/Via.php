<?php namespace SimpleDb\Relations;

use SimpleDb\Data\Model;
use SimpleDb\Database;

class Via extends Relation {

	private $_junctionTable;
	private $_junctionLocal;
	private $_junctionForeign;
	private $_local;
	private $_foreign;

	/**
	 * @internal Use {@link Relation::via()}.
	 */
	public function __construct($model, $junctionTable, $junctionLocal, $junctionForeign, $local, $foreign) {
		parent::__construct($model);

		$this->_junctionTable = $junctionTable;
		$this->_junctionLocal = $junctionLocal;
		$this->_junctionForeign = $junctionForeign;
		$this->_local = $local;
		$this->_foreign = $foreign;
	}

	public function fetch(Model $model) {
		$query = 'SELECT ' . $this->getForeignTable() . '.* FROM ' . $this->_junctionTable . '
			INNER JOIN ' . $this->getForeignTable() . ' ON ' . $this->getForeignTable() . '.' . $this->_foreign . ' = ' . $this->_junctionTable . '.' . $this->_junctionForeign . '
			WHERE ' . $this->_junctionTable . '.' . $this->_junctionLocal . ' = ?';

		$rows = Database::get()->all($query, [$model->{$this->_local}]);

		return call_user_func([$this->model, 'from'], $rows);
	}

}