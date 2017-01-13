<?php namespace SimpleDb;

use DateTime;
use Exception;

/**
 * Encapsulates functionality that deals with model fields.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Field {

	/**
	 * An integer value. Provides the value of using {@link intval()} when {@link Field::toRefined() refined} and the
	 * string value when {@link Field::toPrimitive() made primitive}.
	 */
	const INT = 'int';

	/**
	 * A basic string value.
	 */
	const STRING = 'string';

	/**
	 * A DateTime value. Provides a {@link DateTime} instance when {@link Field::toRefined() refined} and a string
	 * formatted "Y-m-d H:i:s" when {@link Field::toPrimitive() made primitive}.
	 */
	const DATETIME = 'datetime';

	/**
	 * A JSON value. Provides {@link json_decode() decoded JSON} as an object when {@link Field::toRefined refined} and
	 * {@link json_encode() encoded JSON} when {@link Field::toPrimitive() made primitive}.
	 */
	const JSON = 'json';

	/**
	 * Takes a refined or primitive value and returns the storable, primitive version of that value.
	 *
	 * @param mixed $value The input value.
	 * @param string $type The type associated with the value.
	 *
	 * @return mixed
	 *
	 * @throws Exception If any errors were encountered attempting to convert the value.
	 */
	public static function toPrimitive($value, $type) {
		if (!is_string($value)) {
			if ($type === self::INT) {
				if (empty($value) && $value !== 0 && $value !== '0') return null;
				return strval($value);
			}

			if ($type === self::DATETIME) {
				if ($value instanceof DateTime) return $value->format('Y-m-d H:i:s');
			}

			if ($type === self::JSON) {
				if (Utils::isJsonSerializable($value)) return strval(json_encode($value));
				else if (empty($value)) return null;
				else throw new Exception('Could not convert refined value to JSON.');
			}
		}

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
	 *
	 * @throws Exception If any errors were encountered attempting to convert the value.
	 */
	public static function toRefined($value, $type) {
		if (is_string($value)) {
			if ($type === self::INT) {
				if (empty($value) && $value !== 0 && $value !== '0') return null;
				return intval($value);
			}

			if ($type === self::DATETIME) {
				if (empty($value)) return null;
				return new DateTime($value);
			}

			if ($type === self::JSON) {
				if (!empty($value)) {
					$base = json_decode($value);

					if (json_last_error() !== JSON_ERROR_NONE) {
						throw new Exception('Could not decode primitive JSON.');
					}

					return $base;
				}
			}
		}

		return $value;
	}

}