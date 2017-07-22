<?php namespace SimpleDb\Data;

use Exception;
use Carbon\Carbon;
use SimpleDb\Util\Utils;

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
	 * A float value. Provides the value of {@link floatval()} when {@link Field::toRefined() refined} and the string
	 * value when {@link Field::toPrimitive() made primitive}.
	 */
	const FLOAT = 'float';

	/**
	 * A basic string value.
	 */
	const STRING = 'string';

	/**
	 * A DateTime value. Provides a {@link Carbon} instance when {@link Field::toRefined() refined} and a string
	 * formatted "Y-m-d H:i:s" when {@link Field::toPrimitive() made primitive}.
	 */
	const DATETIME = 'datetime';

	/**
	 * A JSON value. Provides {@link json_decode() decoded JSON} as an object when {@link Field::toRefined refined} and
	 * {@link json_encode() encoded JSON} when {@link Field::toPrimitive() made primitive}.
	 */
	const JSON = 'json';

	/**
	 * A boolean value. Provides a true or false boolean when {@link Field::toRefined refined} and 0 or 1 when
	 * {@link Field::toPrimitive made primitive}.
	 */
	const BOOL = 'bool';

	/**
	 * Determine whether a value of a specified type should be converted to NULL.
	 *
	 * * {@link Field::INT} is considered empty if the value is {@link empty} but not 0 or "0".
	 * * {@link Field::STRING} is considered empty if it is NULL, FALSE or an empty array.
	 * * {@link Field::DateTime} is considered empty if it is {@link empty}.
	 * * {@link Field::JSON} is considered empty if it is {@link empty} but not an array with no items.
	 * * {@link FIeld::BOOL} is considered empty if it is NULL.
	 *
	 * @param string $value The value to test.
	 * @param string $type The data type for the value, which specified the rules for emptiness.
	 *
	 * @return bool
	 */
	public static function isNull($value, $type) {
		if ($type === self::INT || $type === self::FLOAT) {
			// Don't treat '0' and 0 as empty values, those are valid ints of value 0.
			return $value === null || $value === false || $value === '' || $value === [];
		} else if ($type === self::STRING) {
			// Don't consider empty strings, "0" or 0 as empty, we still want to store those.
			return $value === null || $value === false || $value === [];
		} else if ($type === self::DATETIME) {
			return empty($value);
		} else if ($type === self::JSON) {
			// Allow empty arrays to be treated as non-empty.
			return empty($value) && !is_array($value);
		} else if ($type === self::BOOL) {
			return $value === null;
		}

		return false;
	}

	/**
	 * Takes a refined or primitive value and returns the storable, primitive version of that value. Values considered
	 * {@link Field::isEmpty() empty} will return NULL.
	 *
	 * @param mixed $value The input value.
	 * @param string $type The type associated with the value.
	 *
	 * @return string|null
	 *
	 * @throws Exception If any errors were encountered attempting to convert the value.
	 */
	public static function toPrimitive($value, $type) {
		if (self::isNull($value, $type)) {
			// Use NULL for values considered empty.
			return null;
		}

		if ($type === self::INT || $type === self::FLOAT || $type === self::STRING) {
			return strval($value);
		}

		if ($type === self::DATETIME) {
			if ($value instanceof Carbon) return $value->format('Y-m-d H:i:s');
			else return (new Carbon($value))->format('Y-m-d H:i:s');
		}

		if ($type === self::JSON) {
			if (Utils::isJsonSerializable($value)) return strval(json_encode($value));
			else return null;
		}

		if ($type === self::BOOL) {
			return strval(intval($value));
		}

		return $value;
	}

	/**
	 * Takes primitive value (usually directly from MySQL) and returns a refined version if that value based on the type
	 * required by that value. Values considered {@link Field::isEmpty() empty} will return NULL.
	 *
	 * @param mixed $value The input value.
	 * @param string $type The type associated with the value.
	 *
	 * @return mixed
	 */
	public static function toRefined($value, $type) {
		if (self::isNull($value, $type)) {
			// Use NULL for values considered empty.
			return null;
		}

		if (is_string($value)) {
			if ($type === self::INT) {
				return intval($value);
			}

			if ($type === self::FLOAT) {
				return floatval($value);
			}

			if ($type === self::DATETIME) {
				return new Carbon($value);
			}

			if ($type === self::JSON) {
				$base = json_decode($value);

				if (json_last_error() !== JSON_ERROR_NONE) {
					return null;
				}

				return $base;
			}

			if ($type === self::BOOL) {
				return !!$value;
			}
		} else {
			if ($type === self::STRING) {
				return strval($value);
			}
		}

		return $value;
	}

}