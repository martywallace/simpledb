<?php namespace SimpleDb;

class HasManyRelation extends Relation {

	private $_localColumn;
	private $_externalTable;
	private $_externalColumn;
	private $_model;

	public function __construct($localColumn, $externalTable, $externalColumn, $model = null) {
		$this->_localColumn = $localColumn;
		$this->_externalTable = $externalTable;
		$this->_externalColumn = $externalColumn;
		$this->_model = $model;
	}

	public function fetch(Model $model) {
		$rows = Database::get()->all('SELECT * FROM ' . $this->_externalTable . ' WHERE ' . $this->_externalColumn . ' = ?', [$model->get($this->_localColumn)]);
		return !empty($this->_model) ? $rows->populate($this->_model) : $rows;
	}

}