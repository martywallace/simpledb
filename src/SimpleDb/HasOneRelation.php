<?php namespace SimpleDb;

class HasOneRelation extends Relation {

	private $_localColumn;
	private $_externalTable;
	private $_externalColumn;
	private $_model;

	public function __construct($localColumn, $externalTable, $externalColumn = 'id', $model = null) {
		$this->_localColumn = $localColumn;
		$this->_externalTable = $externalTable;
		$this->_externalColumn = $externalColumn;
		$this->_model = $model;
	}
	
	public function fetch(Model $model) {
		$row = Database::get()->table($this->_externalTable)->one($model->get($this->_localColumn), $this->_externalColumn);

		return empty($row) ? null : (!empty($this->_model) ? $row->populate($this->_model) : $row);
	}

}