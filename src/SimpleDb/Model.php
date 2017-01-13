<?php namespace SimpleDb;

use Exception;
use JsonSerializable;

/**
 * A model can be populated by raw data from rows returned from a query.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
abstract class Model implements JsonSerializable {

	/** @var Model[] */
	private static $_definitions = [];

	/** @return Model */
	private static function _getDefinition() {
		if (!array_key_exists(static::class, self::$_definitions)) {
			self::$_definitions[static::class] = new static();
		}

		return self::$_definitions[static::class];
	}

	/**
	 * Get the {@link Table} associated with this model using its {@link Model::table() table name}.
	 *
	 * @return Table
	 */
	public static function getTable() {
		return Database::get()->table(static::_getDefinition()->table());
	}

	/**
	 * Get the {@link Model::fields() fields} associated with this model.
	 *
	 * @return string[]
	 */
	public static function getFields() {
		return static::_getDefinition()->fields();
	}

	/**
	 * Get the primary fields.
	 *
	 * @return string[]
	 */
	public static function getPrimaryFields() {
		return array_map(function(Column $column) { return $column->name; }, static::getTable()->getPrimaryColumns());
	}

	/**
	 * Statically get the {@link Model::relations() relations} associated with this model.
	 *
	 * @return Relation[]
	 */
	public static function getRelations() {
		return static::_getDefinition()->relations();
	}

	/** @var mixed[] */
	private $_data = [];

	/** @var mixed[] */
	private $_unknown = [];

	/**
	 * The name of the table that this model belongs to.
	 *
	 * @return string
	 */
	abstract protected function table();

	/**
	 * Return an array of fields that this model should handle. The keys of the returned array should be the names of
	 * the fields, and the values should be the types for those fields.
	 *
	 * @see Field::INT
	 * @see Field::STRING
	 * @see Field::DATETIME
	 * @see Field::JSON
	 *
	 * @return string[]
	 */
	abstract protected function fields();

	/**
	 * Returns an array of relations that this model has to other models.
	 *
	 * @return Relation[]
	 */
	protected function relations() {
		return [];
	}

	/**
	 * @param Populator $populator The populator (usually a {@link Row} or {@link Rows} instance) that will provide data
	 * necessary to populate the newly created model.
	 *
	 * @return static|Models
	 */
	public static function from(Populator $populator) {
		return $populator->populate(static::class);
	}

	/**
	 * @param array $data Optional initial data to fill this model with.
	 *
	 * @throws Exception If a relation conflicts with a field.
	 */
	public function __construct(array $data = []) {
		foreach ($this->relations() as $name => $relation) {
			if (!($relation instanceof Relation)) {
				throw new Exception('Relation "' . $name . '" must inherit SimpleDb\Relation.');
			}

			if ($this->hasField($name)) {
				throw new Exception('Cannot declare relation "' . $name . '" on "' . static::class . '" - a field with the same name already exists.');
			}
		}

		foreach ($this->fields() as $field => $type) {
			$this->_data[$field] = null;
		}

		$this->fill($data);
	}

	public function __get($prop) {
		if (array_key_exists($prop, $this->relations())) {
			return $this->getRelated($prop);
		}

		return $this->getFieldValue($prop);
	}

	public function __set($prop, $value) {
		$this->setFieldValue($prop, $value);
	}

	public function __isset($prop) {
		return array_key_exists($prop, $this->_data) || array_key_exists($prop, $this->_unknown) || array_key_exists($prop, $this->relations());
	}

	/**
	 * Fill this model with data from a source array. The source array keys are the field names and their values the
	 * values to fill those fields with.
	 *
	 * @param array $data
	 */
	public function fill(array $data) {
		foreach ($data as $field => $value) {
			$this->setFieldValue($field, $value);
		}
	}

	/**
	 * Determine whether the values associated with {@link Model::fields() declared fields} in this model are equal to
	 * those of the values in another model. The model types must also match.
	 *
	 * @param Model $model The model to compare against.
	 *
	 * @return bool
	 */
	public function equalTo(Model $model) {
		if (!is_a($model, static::class)) {
			return false;
		}

		$compareData = $model->getPrimitiveData();

		foreach ($this->getPrimitiveData() as $field => $value) {
			if ($compareData[$field] !== $value) return false;
		}

		return true;
	}

	/**
	 * Save this model in the database. If an {@link Model::increments() incrementing} column is defined, the value of
	 * {@link Database::lastInsertId} will be assigned to the relevant field in this model after a successful save if
	 * the current value for that field on this model is empty.
	 */
	public function save() {
		Database::get()->table($this->table())->insert($this->getPrimitiveData());

		/*
		if (!empty($this->increments()) && empty($this->getFieldValue($this->increments()))) {
			$this->setFieldValue($this->increments(), Database::get()->lastInsertId);
		}
		*/
	}

	/**
	 * Gets a value from this model. If the value is associated with the {@link Model::fields() declared fields} for
	 * this model, the value is {@link Field::toRefined() refined} first.
	 *
	 * @param string $field The name of the field to get a value from.
	 *
	 * @return mixed
	 */
	public function getFieldValue($field) {
		if ($this->hasField($field) && array_key_exists($field, $this->_data)) return Field::toRefined($this->_data[$field], $this->fields()[$field]);
		else if (array_key_exists($field, $this->_unknown)) return $this->_unknown[$field];

		return null;
	}

	/**
	 * Sets the value of a field on this model. If the field is a {@link Model::fields() listed field}, the value of
	 * that field is {@link Field::toPrimitive() made primitive} first so that it is able to be stored in MySQL.
	 *
	 * @param string $field The field to set.
	 * @param mixed $value The value to allocate to the field.
	 */
	public function setFieldValue($field, $value) {
		if ($this->hasField($field)) $this->_data[$field] = Field::toPrimitive($value, $this->fields()[$field]);
		else $this->_unknown[$field] = $value;
	}

	/**
	 * Determine whether this model has a field in the list of {@link Model::fields() declared fields}.
	 *
	 * @param string $name The name of the field.
	 *
	 * @return bool
	 */
	public function hasField($name) {
		return array_key_exists($name, $this->fields());
	}

	/**
	 * Gets a {@link Model::relations() declared relationship}.
	 *
	 * @param string $name The name of the relation.
	 *
	 * @return Relation
	 *
	 * @throws Exception If the relationship does not exist.
	 */
	public function getRelation($name) {
		if (!$this->hasRelation($name)) {
			throw new Exception('Relation "' . $name . '" does not exist on model "' . static::class . '".');
		}

		return $this->relations()[$name];
	}

	/**
	 * Determine whether this model has a relation in the list of {@link Model::relations() declared relations}.
	 *
	 * @param string $name The name of the relation.
	 *
	 * @return bool
	 */
	public function hasRelation($name) {
		return array_key_exists($name, $this->relations());
	}

	/**
	 * Get the {@link Field::toPrimitive() primitive} data associated with declared fields in this model.
	 *
	 * @return mixed[]
	 */
	public function getPrimitiveData() {
		return $this->_data;
	}

	/**
	 * Get {@link Field::toPrimitive() primitive} data for the unique fields in this model.
	 *
	 * @return string[]
	 */
	public function getUniquePrimitiveData() {
		return array_filter($this->getPrimitiveData(), function($key) { return in_array($key, $this->unique()); }, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Get {@link Field::toPrimitive() primitive} data for the non-unique fields in this model.
	 *
	 * @return string[]
	 */
	public function getNonUniquePrimitiveData() {
		return array_filter($this->getPrimitiveData(), function($key) { return !in_array($key, $this->unique()); }, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Get the {@link Field::toRefined() refined} data associated with declared fields in this model.
	 *
	 * @return mixed[]
	 */
	public function getRefinedData() {
		$data = [];

		foreach ($this->fields() as $field => $type) {
			$data[$field] = Field::toRefined($this->getFieldValue($field), $type);
		}

		return $data;
	}

	/**
	 * Get {@link Field::toRefined() refined} data for the unique fields in this model.
	 *
	 * @return mixed[]
	 */
	public function getUniqueRefinedData() {
		return array_filter($this->getRefinedData(), function($key) { return in_array($key, $this->unique()); }, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Get {@link Field::toRefined() refined} data for the non-unique fields in this model.
	 *
	 * @return mixed[]
	 */
	public function getNonUniqueRefinedData() {
		return array_filter($this->getRefinedData(), function($key) { return !in_array($key, $this->unique()); }, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Fetch a related model.
	 *
	 * @param string $name The relation name {@link Model::relations() specified}.
	 *
	 * @returns Model|Models
	 *
	 * @throws Exception If the relation name is unknown.
	 */
	public function getRelated($name) {
		if (array_key_exists($name, $this->relations())) {
			return $this->relations()[$name]->fetch($this);
		} else {
			throw new Exception('Unknown relation "' . $name . '".');
		}
	}

	public function jsonSerialize() {
		return $this->getRefinedData();
	}

}