<?php namespace SimpleDb\Data;

use JsonSerializable;
use SimpleDb\Database;
use SimpleDb\Util\Query;

/**
 * A table within the database.
 *
 * @property-read string $name The name of this table.
 *
 * @package SimpleDb\Data
 * @author Marty Wallace
 */
class Table implements JsonSerializable {

	/** @var string */
	private $_name = null;

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

	public function __toString() {
		return $this->_name;
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
			}, Database::get()->all(Query::describe($this->_name)));
		}

		return $this->_columns;
	}

	/**
	 * Get a single {@link Column column} in this table by its name.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column
	 */
	public function getColumn($name) {
		foreach ($this->getColumns() as $column) {
			if ($column->name === $name) return $column;
		}

		return null;
	}

	/**
	 * Get all PRIMARY or UNIQUE columns.
	 *
	 * @return Column[]
	 */
	public function getUniqueColumns() {
		return array_values(array_filter($this->getColumns(), function(Column $column) { return $column->isUnique(); }));
	}

	/**
	 * Get all PRIMARY columns.
	 *
	 * @return Column[]
	 */
	public function getPrimaryColumns() {
		return array_values(array_filter($this->getColumns(), function(Column $column) { return $column->key === Column::PRIMARY; }));
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
		return array_values(array_filter($this->getColumns(), function(Column $column) { return !$column->isUnique(); }));
	}

	/**
	 * Get all columns that can be provided with NULL.
	 *
	 * @return Column[]
	 */
	public function getNullableColumns() {
		return array_values(array_filter($this->getColumns(), function(Column $column) { return $column->null; }));
	}

	/**
	 * Get all columns that cannot be provided with NULL.
	 *
	 * @return Column[]
	 */
	public function getNonNullableColumns() {
		return array_values(array_filter($this->getColumns(), function(Column $column) { return !$column->null; }));
	}

	/**
	 * Find a record using its primary key.
	 *
	 * @param string|string[]|int|int[] $primary The primary key. An array can be provided for multiple keys.
	 *
	 * @return Row
	 */
	public function find($primary) {
		if (!is_array($primary)) {
			$primary = [$primary];
		}

		return Database::get()->one(Query::select($this->_name)->where(array_map(function(Column $column) {
			return $column->name;
		}, $this->getPrimaryColumns()))->limit(1), $primary);
	}

	/**
	 * Return one row from this table using search criteria.
	 *
	 * @param array $criteria An array of fields mapped to values to search for.
	 *
	 * @return Row
	 */
	public function oneWhere(array $criteria) {
		return Database::get()->one(Query::select($this->_name)->where(array_keys($criteria))->limit(1), array_values($criteria));
	}

	/**
	 * Return all rows from this table.
	 *
	 * @return Row[]
	 */
	public function all() {
		return Database::get()->all(Query::select($this->_name));
	}

	/**
	 * Return all rows that match the WHERE criteria supplied.
	 *
	 * @param array $criteria The WHERE criteria.
	 *
	 * @return Row[]
	 */
	public function allWhere(array $criteria) {
		return Database::get()->all(Query::select($this->_name)->where(array_keys($criteria)), array_values($criteria));
	}

	/**
	 * Count the amount of rows in this table.
	 *
	 * @param array $criteria Optional WHERE criteria.
	 *
	 * @return int
	 */
	public function count(array $criteria = []) {
		return intval(Database::get()->prop(Query::select($this->_name, 'COUNT(*)')->where(array_keys($criteria)), array_values($criteria)));
	}

	/**
	 * Delete a row from this table.
	 *
	 * @param array $criteria Optional WHERE criteria.
	 */
	public function delete(array $criteria = []) {
		Database::get()->query(Query::delete($this->_name)->where(array_keys($criteria)), array_values($criteria));
	}

	/**
	 * Insert data into this table.
	 *
	 * @param array $data The data to insert.
	 * @param array $update If provided, create an ON DUPLICATE KEY UPDATE for these columns.
	 *
	 * @return int If this table has an auto-incrementing column, return the value of the last inserted value.
	 */
	public function insert(array $data, array $update = []) {
		$insert = [];

		foreach ($data as $key => $value) {
			$insert[':' . $key] = $value;
		}

		Database::get()->query(Query::insert($this->_name, array_keys($data), $update), $insert);

		$lastId = Database::get()->lastInsertId;

		return !empty($this->getIncrementingColumn()) ? $lastId : null;
	}

	public function jsonSerialize() {
		return [
			'name' => $this->_name,
			'columns' => $this->getColumns()
		];
	}

}