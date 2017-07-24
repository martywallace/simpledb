<?php namespace SimpleDb\Util;

use Exception;
use JsonSerializable;
use stdClass;

/**
 * Utilities for this package.
 *
 * @package SimpleDb\Util;
 * @author Marty Wallace
 */
class Utils {

	/**
	 * Extract login information from a connection string formatted <code>username:password@host/database</code>. Returns
	 * an array with the keys host, username, password and database.
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

	/**
	 * Determine whether a value can be encoded as JSON.
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool
	 */
	public static function isJsonSerializable($value) {
		return is_array($value) || $value instanceof stdClass || $value instanceof JsonSerializable || is_numeric($value);
	}

}