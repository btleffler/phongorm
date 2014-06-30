<?php

/**
 * Document Object
 * ===============
 * Model Object, if you need a delete method, it
 * should be implemented in the extended classes.
 */

namespace btleffler\Phongorm;
use PhongormException as Exception;
use Collection;

class Document extends ArrayObject {

	public function __construct ($data = array()) {
		parent::__construct(
			$data,
			ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS
		);
	}

}
