<?php namespace SimpleDb;

use DateTime;

/**
 * Encapsulates functionality that deals with model fields.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Field {

	/**
	 * An integer value.
	 */
	const INT = 'int';

	/**
	 * A string value.
	 */
	const STRING = 'string';

	/**
	 * A DateTime value. Provides a {@link DateTime} instance when {@link Field::toRefined() refined} and a string
	 * formatted "Y-m-d H:i:s" when {@link Field::toPrimitive() made primitive}.
	 */
	const DATETIME = 'datetime';

	/**
	 * Takes a refined or primitive value and returns the storable, primitive version of that value.
	 *
	 * @param mixed $value The input value.
	 * @param string $type The type associated with the value.
	 *
	 * @return mixed
	 */
	public static function toPrimitive($value, $type) {
		return $value;
	}

	/**
	 * Takes primitive value (usually directly from MySQL) and returns a refined version if that value based on the type
	 * required by that value.
	 *
	 * @param mixed $value The input value.
	 * @param string $type The type associated with the value.
	 *
	 * @return mixed
	 */
	public static function toRefined($value, $type) {
		return $value;
	}

}