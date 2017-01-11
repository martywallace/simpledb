<?php namespace SimpleDb;

/**
 * A series of rows.
 *
 * @property-read Row[] $content The rows contained within this series.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Rows extends Series implements Populator {

	/**
	 * Populate and return a set of models using data from the rows contained in this set.
	 *
	 * @param string $class The name of the model class to construct and populate.
	 *
	 * @return Models
	 */
	public function populate($class) {
		$models = [];

		foreach ($this as $row) $models[] = $row->populate($class);

		return new Models($models);
	}

	/**
	 * @internal
	 *
	 * @return Row
	 */
	public function current() { return $this->content[$this->key()]; }

}