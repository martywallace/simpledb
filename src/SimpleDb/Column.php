<?php namespace SimpleDb;

use JsonSerializable;

/**
 * A table column provided by {@link Table::describe()}.
 *
 * @property-read string $name The name of this column.
 * @property-read string $type The column type.
 * @property-read bool $null Whether or not the column accepts NULL values.
 * @property-read string $key The key type for this column.
 * @property-read bool $increments Whether or not this column auto-increments.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Column implements JsonSerializable {

	/** @var string A primary key. */
	const PRIMARY = 'PRI';

	/** @var string A unique key. */
	const UNIQUE = 'UNI';

	/** @var string An index key. */
	const INDEX = 'MUL';

	/** @var mixed[] */
	private $_def = [];

	public function __construct($def) {
		$this->_def = [
			'name' => $def['Field'],
			'type' => $def['Type'],
			'null' => strtolower($def['Null']) === 'yes',
			'key' => empty($def['Key']) ? null : $def['Key'],
			'default' => $def['Default'],
			'increments' => strpos(strtolower($def['Extra']), 'auto_increment') !== false
		];
	}

	public function __get($prop) {
		if (array_key_exists($prop, $this->_def)) return $this->_def[$prop];

		return null;
	}

	public function __isset($prop) {
		return array_key_exists($prop, $this->_def);
	}

	/**
	 * Determine whether this column is unique (has a PRIMARY or UNIQUE key).
	 *
	 * @return bool
	 */
	public function isUnique() {
		return $this->key === self::PRIMARY || $this->key === self::UNIQUE;
	}

	public function jsonSerialize() {
		return [
			'name' => $this->name,
			'type' => $this->type,
			'key' => $this->key,
			'increments' => $this->increments,
			'null' => $this->null,
			'default' => $this->_def['default']
		];
	}

}