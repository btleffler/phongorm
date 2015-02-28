<?php

/**
 * Document Object
 * ===============
 * Model Object, if you need a delete method, it
 * should be implemented in the extended classes.
 */

namespace Phongorm\Document;

use Phongorm\PhongormException as Exception;
use \ArrayObject;

class Document extends ArrayObject {

	/**
	 * Create new Phongorm Documents
	 * @param array $data Data returned by a Mongo Query
	 */
	public function __construct ($data = array()) {
		parent::__construct(
			$data,
			ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS
		);
	}

	/**
	 * Get the Phongorm Collection class name related to the Phongorm Document class
	 * @return String The name of the Phongorm Collection class related to the
	 *                Phongorm Document class
	 */
	public static function collectionClassName () {
		if (isset(static::$collectionClassName))
			return static::$collectionClassName;

		$possibleClassName = preg_replace("/Document$/i", '', get_called_class());

		if (class_exists($possibleClassName))
			return $possibleClassName;

		return "Collection";
	}

}
