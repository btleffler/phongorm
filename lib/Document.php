<?php

/**
 * Document Object
 * ===============
 * Model Object, if you need a delete method, it
 * should be implemented in the extended classes.
 */

namespace btleffler\Phongorm;
use btleffler\Phongorm\PhongormException as Exception;
use btleffler\Phongorm\Collection;

class Document extends ArrayObject {

	public function __construct ($data = array()) {
		parent::__construct(
			$data,
			ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS
		);
	}

	public static function collectionClassName () {
		if (isset(static::$collectionClassName))
			return static::$collectionClassName;

		$possibleClassName = preg_replace("/Document$/i", '', get_called_class());

		if (class_exists($possibleClassName))
			return $possibleClassName;

		return "Collection";
	}

}
