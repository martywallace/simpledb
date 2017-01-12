<?php namespace SimpleDb;

use Exception;

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

		//return Database::get()->query(Query::select($this->_name)->where($data)->limit(1), $data);
	}

}