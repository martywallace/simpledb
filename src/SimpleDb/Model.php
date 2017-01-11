<?php namespace SimpleDb;

/**
 * A model can be populated by raw data from rows returned from a query.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
abstract class Model {

	/**
	 * @param Populator $populator The populator (usually a {@link Row} or {@link Rows} instance) that will provide data
	 * necessary to populate the newly created model.
	 *
	 * @return static|static[]
	 */
	public static function from(Populator $populator) {
		return $populator->populate(static::class);
	}

	public function __construct(array $data = []) {
		foreach ($data as $property => $value) {
			$this->{$property} = $value;
		}
	}

}