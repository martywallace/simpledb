<?php namespace SimpleDb;

use PDO;
use PDOStatement;
use Exception;


/**
 * Provides access to a database.
 *
 * @property-read PDO $pdo The PDO instance managing the connection internally.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Database {

	/** @var PDO */
	private $_pdo = null;

	/**
	 * Database constructor.
	 *
	 * @param string $connection The connection string formatted user:password?@host/database.
	 */
	public function __construct($connection) {
		$config = Utils::parseConnectionString($connection);
		$this->_pdo = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['database'], $config['username'], $config['password']);
	}

	public function __get($prop) {
		if ($prop === 'pdo') return $this->_pdo;

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
	 * @param string|null $class The name of a class to optionally create and inject the returned values into.
	 *
	 * @return mixed
	 *
	 * @throws Exception If the internal PDOStatement returns any errors, they are thrown as an exception.
	 * @throws Exception If the provided class does not exist.
	 */
	public function one($query, array $params = null, $class = null) {
		$all = $this->all($query, $params, $class);

		if (!empty($all)) {
			return $all[0];
		}

		return null;
	}

	/**
	 * Returns all rows provided by executing a query.
	 *
	 * @param string $query The query to execute.
	 * @param array|null $params Parameters to bind to the query.
	 * @param string|null $class The name of a class to optionally create and inject the returned values into.
	 *
	 * @return array
	 *
	 * @throws Exception If the internal PDOStatement returns any errors, they are thrown as an exception.
	 * @throws Exception If the provided class does not exist.
	 */
	public function all($query, array $params = null, $class = null) {
		$stmt = $this->query($query, $params);

		if (!empty($class)) {
			$class = '\\' . ltrim($class, '\\');

			if (class_exists($class)) {
				return $stmt->fetchAll(PDO::FETCH_CLASS, $class);
			} else {
				throw new Exception('Class "' . $class . '" does not exist.');
			}
		} else {
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}
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
}