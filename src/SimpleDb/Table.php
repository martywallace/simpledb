<?php namespace SimpleDb;

/**
 * A table within the database.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Table {

	/** @var Database */
	private $_db;

	/** @var string */
	private $_name;

	/**
	 * Table constructor.
	 *
	 * @param Database $db The database instance responsible for this table.
	 * @param string $name The table name.
	 */
	public function __construct(Database $db, $name) {
		$this->_db = $db;
		$this->_name = $name;
	}

	/**
	 * Return one row from this table using its primary key.
	 *
	 * @param string|number $primary The primary key value.
	 * @param string $primaryColumn The primary key column name.
	 *
	 * @return mixed
	 */
	public function one($primary, $primaryColumn = 'id') {
		return $this->_db->one('SELECT * FROM ' . $this->_name . ' WHERE ' . $primaryColumn . ' = ?', [$primary]);
	}

	/**
	 * Return all rows from this table.
	 *
	 * @return array
	 */
	public function all() {
		return $this->_db->all('SELECT * FROM ' . $this->_name);
	}

	/**
	 * Delete a row from this table.
	 *
	 * @param string|number $primary The primary key value.
	 * @param string $primaryColumn The primary key column name.
	 */
	public function delete($primary, $primaryColumn = 'id') {
		$this->_db->delete($this->_name, $primary, $primaryColumn);
	}

	/**
	 * Insert a row into this table.
	 *
	 * @param array $data The data to insert.
	 */
	public function insert(array $data) {
		$this->_db->insert($this->_name, $data);
	}

}