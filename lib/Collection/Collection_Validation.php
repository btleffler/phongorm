<?php

namespace Phongorm\Collection;

use Phongrom\Phongorm;
use Phongorm\PhongormException as Exception;

class Collection_Validation {

	/**
	 * Validates data agains the definitions provided.
	 * @param array $data        Document data, that may or may not be ready to go
	 *                           to MongoDB.
	 * @param array $definitions Document definition from a Phongorm Collection
	 *                           classes' $document property.
	 * @return array A copy of $data with all of the proper Mongo types
	 */
	private static function validateAgainst ($data, $definitions) {
		foreach($data as $key => &$value) {
			if (Phongorm::isDocument($value))
				$value = $value->toArray();

			// Array of subdocuments
			$type = $definitions;

			if (array_key_exists($key, $definitions))
				$type = $definitions[$key];

			// Subdocument
			if (is_array($type))
				$data[$key] = self::validateAgainst($value, $type);
			else { // Everything else
				$type = preg_replace("/^Mongo/i", '', $type);

				if (!method_exists("Collection", $type))
					throw new Exception("Unknown Type: '" . $type . "'");

				$value = Phongorm::$type($value);
			}
		}

		return $data;
	}

	/**
	 * Validate a Phongorm Document
	 * @param  array $data Data to be validated
	 * @return array       A copy of $data with all of the proper Mongo types
	 */
	protected static function validate ($data) {
		if (!isset(static::$document))
			return $data;

		return self::validateAgainst($data, static::$document);
	}

}
