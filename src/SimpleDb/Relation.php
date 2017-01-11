<?php namespace SimpleDb;

/**
 * A database relationship.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
abstract class Relation {

	/**
	 * Fetch related data.
	 *
	 * @param Model $model The model to fetch related data through.
	 *
	 * @return Row|Rows|Model|Models
	 */
	abstract public function fetch(Model $model);

}