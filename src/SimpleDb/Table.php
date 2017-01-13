<?php namespace SimpleDb;

use Exception;

/**
 * A table within the database.
 *
 * @property-read string $name The name of this table.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Table {

	/** @var string */
	private $_name;

	/** @var Column[] */
	private $_columns = [];

	/**
	 * Table constructor.
	 *
	 * @param string $name The table name.
	 */
	public function __construct($name) {
		$this->_name = $name;
	}

	public function __get($prop) {
		if ($prop === 'name') return $this->_name;

		return null;
	}

	public function __isset($prop) {
		return $this->{$prop} !== null;
	}

	/**
	 * Perform a DESCRIBE on this table and return the described {@link Column columns}.
	 * 
	 * @return Column[]
	 */
	public function getColumns() {
		if (empty($this->_columns)) {
			$this->_columns = array_map(function(Row $row) {
				return new Column($row->getData());
			}, Database::get()->all(Query::describe($this->_name))->content());
		}

		return $this->_columns;
	}

	/**
	 * Get all PRIMARY or UNIQUE columns.
	 *
	 * @return Column[]
	 */
	public function getUniqueColumns() {
		return array_filter($this->getColumns(), function(Column $column) { return $column->isUnique(); });
	}

	/**
	 * Get all PRIMARY columns.
	 *
	 * @return Column[]
	 */
	public function getPrimaryColumns() {
		return array_filter($this->getColumns(), function(Column $column) { return $column->key === Column::PRIMARY; });
	}

	/**
	 * Get the column in this table marked to AUTO_INCREMENT.
	 *
	 * @return Column
	 */
	public function getIncrementingColumn() {
		foreach ($this->getColumns() as $column) {
			if ($column->increments) return $column;
		}

		return null;
	}

	/**
	 * Get all non-primary and non-unique columns.
	 *
	 * @return Column[]
	 */
	public function getNonUniqueColumns() {
		return array_filter($this->getColumns(), function(Column $column) { return !$column->isUnique(); });
	}

	/**
	 * Return one row from this table using its primary key.
	 *
	 * @param array $criteria An array of fields mapped to values to search for.
	 *
	 * @return Row
	 */
	public function one(array $criteria) {
		return Database::get()->one(Query::select($this->_name)->where($criteria)->limit(1), array_values($criteria));
	}

	/**
	 * Return all rows from this table.
	 *
	 * @return Rows
	 */
	public function all() {
		return Database::get()->all(Query::select($this->_name));
	}

	/**
	 * Count the amount of rows in this table.
	 *
	 * @param array $criteria Optional WHERE criteria.
	 *
	 * @return int
	 */
	public function count(array $criteria = []) {
		return intval(Database::get()->prop(Query::select($this->_name, 'COUNT(*)')->where($criteria)));
	}

	/**
	 * Delete a row from this table.
	 *
	 * @param array $criteria Optional WHERE criteria.
	 */
	public function delete(array $criteria = []) {
		Database::get()->query(Query::delete($this->_name)->where($criteria), array_values($criteria));
	}

	/**
	 * Insert data into this table and return a Row representing that data if the insertion was successful.
	 *
	 * @param array $data The data to insert.
	 *
	 * @return Row
	 */
	public function insert(array $data) {
		$insert = [];

		foreach ($data as $key => $value) {
			$insert[':' . $key] = $value;
		}

		Database::get()->query(Query::insert($this->_name, $data), $insert);
	}

}