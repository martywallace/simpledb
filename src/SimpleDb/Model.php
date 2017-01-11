<?php namespace SimpleDb;

use Iterator;

class Model {

	/**
	 * @param Populator $populator
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