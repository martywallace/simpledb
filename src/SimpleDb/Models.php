<?php namespace SimpleDb;

/**
 * A series of Models.
 *
 * @property-read Model[] $content The models contained in this series.
 *
 * @package SimpleDb
 * @author Marty Wallace
 */
class Models extends Series {

	/**
	 * @internal
	 *
	 * @return Model
	 */
	public function current() { return $this->content[$this->key()]; }

}