<?php namespace SimpleDb;

interface Populator {

	/**
	 * @param string $class The name of the class to populate.
	 *
	 * @return Model|Model[]
	 */
	function populate($class);

}