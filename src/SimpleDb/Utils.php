<?php namespace SimpleDb;

use Exception;

class Utils {

	/**
	 * Extract login information from a connection string formatted <code>user:password@host/database</code>. Returns
	 * an array with the keys host, user, password and database.
	 *
	 * @param string $value The connection string.
	 *
	 * @return string[]
	 *
	 * @throws Exception If the connection string is not valid.
	 */
	public static function parseConnectionString($value) {
		$value = trim($value);

		preg_match('/^(?<username>[^:@]+):?(?<password>.*)?@(?<host>[^\/]+)\/(?<database>.+)$/', $value, $matches);

		if (!empty($matches)) return $matches;
		else throw new Exception('The supplied connection string is invalid.');
	}

}