<?php namespace SimpleDb;

use PDO;
use PDOStatement;
use Exception;


/**
 * Provides access to a database.
 *
 * @property-read PDO $pdo The PDO instance managing the connection internally.
 * @property-read string $lastInsertId The last ID inserted.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Database {

	/** @var PDO */
	private $_pdo = null;

	/** @var Table[] */
	private $_tables = [];

	/**
	 * Database constructor.
	 *
	 * @param string $connection The connection string formatted username:password?@host/database.
	 */
	public function __construct($connection) {
		$config = Utils::parseConnectionString($connection);
		$this->_pdo = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['database'], $config['username'], $config['password']);
	}

	public function __get($prop) {
		if ($prop === 'pdo') return $this->_pdo;
		if ($prop === 'lastInsertId') return $this->_pdo->lastInsertId();

		return null;
	}

	/**
	 * Prepares a PDOStatement.
	 *
	 * @param string $query The query to prepare.
	 *
	 * @return PDOStatement
	 */
	public function prepare($query) {
		return $this->_pdo->prepare($query);
	}

	/**
	 * Prepare and execute a query, returning the PDOStatement that is created when preparing the query.
	 *
	 * @param string $query The query to execute.
	 * @param array|null $params Optional parameters to bind to the query.
	 *
	 * @return PDOStatement
	 *
	 * @throws Exception If the PDOStatement returns any errors, they are thrown as an exception.
	 */
	public function query($query, array $params = null) {
		$stmt = $this->prepare($query);
		$stmt->execute($params);

		if ($stmt->errorCode() !== '00000') {
			$err = $stmt->errorInfo();
			throw new Exception($err[0] . ': ' . $err[2]);
		}

		return $stmt;
	}

	/**
	 * Returns the first row provided by executing a query.
	 *
	 * @param string $query The query to execute.
	 * @param array|null $params Parameters to bind to the query.
	 *
	 * @return Row
	 *
	 * @throws Exception If the internal PDOStatement returns any errors, they are thrown as an exception.
	 * @throws Exception If the provided class does not exist.
	 */
	public function one($query, array $params = null) {
		return $this->all($query, $params)->first;
	}

	/**
	 * Returns all rows provided by executing a query.
	 *
	 * @param string $query The query to execute.
	 * @param array|null $params Parameters to bind to the query.
	 *
	 * @return Rows
	 *
	 * @throws Exception If the internal PDOStatement returns any errors, they are thrown as an exception.
	 * @throws Exception If the provided class does not exist.
	 */
	public function all($query, array $params = null) {
		$stmt = $this->query($query, $params);
		$rows = $stmt->fetchAll(PDO::FETCH_CLASS, Row::class);

		return new Rows($rows);
	}

	/**
	 * Returns the first value in the first column returned from executing a query.
	 *
	 * @param string $query The query to execute.
	 * @param array|null $params Parameters to bind to the query.
	 * @param mixed $fallback A fallback value to use if no results were returned by the query.
	 *
	 * @return mixed
	 *
	 * @throws Exception If the internal PDOStatement returns any errors, they are thrown as an exception.
	 */
	public function prop($query, array $params = null, $fallback = null) {
		$result = $this->query($query, $params)->fetch(PDO::FETCH_NUM);

		if (!empty($result)) {
			return $result[0];
		}

		return $fallback;
	}

	/**
	 * Delete a row from a table.
	 *
	 * @param string $table The table to delete from.
	 * @param mixed $primary The primary key value.
	 * @param string $primaryColumn The name of the primary key column.
	 *
	 * @throws Exception If the internal PDOStatement returns any errors, they are thrown as an exception.
	 */
	public function delete($table, $primary, $primaryColumn = 'id') {
		$this->query('DELETE FROM ' . $table . ' WHERE ' . $primaryColumn . ' = ?', [$primary]);
	}

	/**
	 * Insert a new row into a target table.
	 *
	 * @param string $table The table to insert data into.
	 * @param array $data The data to insert.
	 *
	 * @throws Exception If the internal PDOStatement returns any errors, they are thrown as an exception.
	 */
	public function insert($table, array $data) {
		$columns = array_keys($data);
		$placeholders = array_fill(0, count($data), '?');

		$this->query('INSERT INTO ' . $table . '(' . implode(', ', $columns) . ') VALUES(' . implode(', ', $placeholders) . ')', array_values($data));
	}

	/**
	 * Return a Table instance for a specific table.
	 *
	 * @param string $name The name of the table.
	 *
	 * @return Table
	 */
	public function table($name) {
		if (!array_key_exists($name, $this->_tables)) {
			$this->_tables[$name] = new Table($this, $name);
		}

		return $this->_tables[$name];
	}
}