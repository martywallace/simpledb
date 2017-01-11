<?php namespace SimpleDb;

/**
 * A table within the database.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Table {

	/** @var string */
	private $_name;

	/**
	 * Table constructor.
	 *
	 * @param string $name The table name.
	 */
	public function __construct($name) {
		$this->_name = $name;
	}

	/**
	 * Return one row from this table using its primary key.
	 *
	 * @param string|number $value The column value.
	 * @param string $column The column name.
	 *
	 * @return Row
	 */
	public function one($value, $column = 'id') {
		return Database::get()->one('SELECT * FROM ' . $this->_name . ' WHERE ' . $column . ' = ?', [$value]);
	}

	/**
	 * Return all rows from this table.
	 *
	 * @return Rows
	 */
	public function all() {
		return Database::get()->all('SELECT * FROM ' . $this->_name);
	}

	/**
	 * Delete a row from this table.
	 *
	 * @param string|number $value The column value.
	 * @param string $column The column name.
	 */
	public function delete($value, $column = 'id') {
		Database::get()->delete($this->_name, $value, $column);
	}

	/**
	 * Insert a row into this table.
	 *
	 * @param array $data The data to insert.
	 */
	public function insert(array $data) {
		Database::get()->insert($this->_name, $data);
	}

}