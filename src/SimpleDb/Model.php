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
	 * Statically get the {@link Model::table() table name} associated with this model.
	 *
	 * @return string
	 */
	public static function getTable() {
		return static::_getDefinition()->table();
	}

	/**
	 * Statically get the name of the column that {@link Model::increments() auto-increments}.
	 *
	 * @return string
	 */
	public static function getIncrements() {
		return static::_getDefinition()->increments();
	}

	/**
	 * Statically get the names of the columns {@link Model::unique() marked unique} for this model.
	 *
	 * @return string[]
	 */
	public static function getUnique() {
		return static::_getDefinition()->unique();
	}

	/**
	 * Statically get the {@link Model::fields() fields} associated with this model.
	 *
	 * @return string[]
	 */
	public static function getFields() {
		return static::_getDefinition()->fields();
	}

	/**
	 * Statically get the {@link Model::relations() relations} associated with this model.
	 *
	 * @return Relation[]
	 */
	public static function getRelations() {
		return static::_getDefinition()->relations();
	}

	/** @var string[] */
	private $_fields = [];

	/** @var mixed[] */
	private $_data = [];

	/** @var mixed[] */
	private $_unknown = [];

	/** @var Relation[] */
	private $_relations = [];

	/**
	 * The name of the table that this model belongs to.
	 *
	 * @return string
	 */
	abstract protected function table();

	/**
	 * The name of a column that auto-increments.
	 *
	 * @return string
	 */
	protected function increments() {
		return null;
	}

	/**
	 * An array containing the names of all unique columns for this model.
	 *
	 * @return string[]
	 */
	protected function unique() {
		return [];
	}

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
	 * @throws Exception If an {@link Model::increments() incrementing} field is not explicitly declared as
	 * {@link Model::unique() unique}.
	 */
	public function __construct(array $data = []) {
		$this->_fields = $this->fields();
		$this->_relations = $this->relations();

		foreach ($this->_relations as $name => $relation) {
			if (!($relation instanceof Relation)) {
				throw new Exception('Relation "' . $name . '" must inherit SimpleDb\Relation.');
			}

			if ($this->hasField($name)) {
				throw new Exception('Cannot declare relation "' . $name . '" on "' . get_class($this) . '" - a field with the same name already exists.');
			}
		}

		if (!empty($this->increments()) && !in_array($this->increments(), $this->unique())) {
			throw new Exception('Field "' . $this->increments() . '" is marked as incrementing but does not appear in the list of unique columns. Incrementing columns must be unique.');
		}

		foreach ($this->_fields as $field => $type) {
			$this->_data[$field] = null;
		}

		$this->fill($data);
	}

	public function __get($prop) {
		if (array_key_exists($prop, $this->_relations)) {
			return $this->getRelated($prop);
		}

		return $this->getFieldValue($prop);
	}

	public function __set($prop, $value) {
		$this->setFieldValue($prop, $value);
	}

	public function __isset($prop) {
		return array_key_exists($prop, $this->_data) || array_key_exists($prop, $this->_unknown) || array_key_exists($prop, $this->_relations);
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
		if (!is_a($model, get_class($this))) {
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

		if (!empty($this->increments()) && empty($this->getFieldValue($this->increments()))) {
			$this->setFieldValue($this->increments(), Database::get()->lastInsertId);
		}
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
		if ($this->hasField($field) && array_key_exists($field, $this->_data)) return Field::toRefined($this->_data[$field], $this->_fields[$field]);
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
		if ($this->hasField($field)) $this->_data[$field] = Field::toPrimitive($value, $this->_fields[$field]);
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
		return array_key_exists($name, $this->_fields);
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
			throw new Exception('Relation "' . $name . '" does not exist on model "' . get_class($this) . '".');
		}

		return $this->_relations[$name];
	}

	/**
	 * Determine whether this model has a relation in the list of {@link Model::relations() declared relations}.
	 *
	 * @param string $name The name of the relation.
	 *
	 * @return bool
	 */
	public function hasRelation($name) {
		return array_key_exists($name, $this->_relations);
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
	 * Get the {@link Field::toRefined() refined} data associated with declared fields in this model.
	 *
	 * @return mixed[]
	 */
	public function getRefinedData() {
		$data = [];

		foreach ($this->_fields as $field => $type) {
			$data[$field] = Field::toRefined($this->getFieldValue($field), $type);
		}

		return $data;
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
		if (array_key_exists($name, $this->_relations)) {
			return $this->_relations[$name]->fetch($this);
		} else {
			throw new Exception('Unknown relation "' . $name . '".');
		}
	}

	public function jsonSerialize() {
		return $this->getRefinedData();
	}

}