<?php namespace SimpleDb;

/**
 * A contract defining an object that can provide data to populate a {@link Model}.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
interface Populator {

	/**
	 * @param string $class The name of the class to populate.
	 *
	 * @return Model|Model[]
	 */
	function populate($class);

}