<?php

/**
 * RelationshipManager Manager Class
 * ==========================
 * Gives an interface for defining and keeping track of Many-To-Many,
 * One-To-Many, and One-To-One relationships for Documents and Collections.
 */

namespace Phongorm;
use Phongorm\PhongormException as Exception;
use Phongorm\Collection;
use Phongorm\Document;

class RelationshipManager {

	protected static $relationships = array();

	protected static function validateCollection ($collectionClassName) {
		if (is_object($collectionClassName)) {
			if (Collection::isCollection($collectionClassName))
				$collectionClassName = get_class($collectionClassName);

			if (Collection::isDocument($collectionClassName))
				$collectionClassName = $collectionClassName::collectionClassName();
		}

		if (!is_string($collectionClassName))
			throw new Exception("Invalid collection class name for relationship.");

		if (!class_exists($collectionClassName))
			throw new Exception("Unknown collection class name for relationship.");

		if (!$collectionClassName !== "Collection" ||
			!is_subclass_of($collectionClassName, "Collection")) {
			throw new Exception(
				"Cannot get relationship from non Collection class: " .
				$collectionClassName
			);
		}

		return $collectionClassName;
	}

	protected static function relationshipKey ($className, $query) {
		return md5($className + serialize($query));
	}

	protected static function cachedRelationship ($key) {
		if (array_key_exists($key, self::$relationships))
			return self::$relationships[$key];

		return false;
	}

	protected static function saveCachedRelationship ($key, $data) {
		return $self::$relationships[$key] = $data;
	}

	public static function oneToOne ($collectionClassName, $query) {
		$collectionClassName = self::validateCollection($collectionClassName);
		$key = self::relationshipKey($collectionClassName, $query);

		if ($cached = self::cachedRelationship($key))
			return $cached;

		$data = call_user_func($collectionClassName . "::findOne", $query);

		return self::saveCachedRelationship($key, $data);
	}

	public static function oneToMany ($collectionClassName, $query) {
		$collectionClassName = self::validateCollection($collectionClassName);
	}

	public static function manyToMany ($collectionClassName, $query) {
		$collectionClassName = self::validateCollection($collectionClassName);
	}

}
