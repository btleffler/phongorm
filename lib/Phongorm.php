<?php
namespace Phongorm;

use Phongorm\Collection\Collection;
use Phongorm\Document\Document;
use Phongorm\PhongormException as Exception;

/**
 * Phongorm
 * =====================================
 * Colleciton of static helper functions
 *
 * Check Phongorm types and cast Mongo types
 */
class Phongorm {

	/**
	 * Is this a Phongorm Document?
	 * @param Mixed $var Something that might be a Phongorm Document
	 * @return Bool      True if $var is a Phongorm Document
	 */
	public static function isDocument ($var) {
		return $var instanceof Document || is_subclass_of($var, "Document");
	}

	/**
	 * Is this a Phongorm Collection?
	 * @param $var $var Something that might be a Phongorm Collection
	 * @return Bool     True if $var is a Phongorm Collection
	 */
	public static function isCollection ($var) {
		return $var instanceof Collection || is_subclass_of($var, "Collection");
	}

	/**
	 * Do the work of casting a variable to a Mongo type
	 * @param Mixed $var             Variable to be cast to a Mongo type
	 * @param String $type           Mongo type to be cast to
	 * @param Callable|false $format Callable formatting function used to prep
	 *                               $var for being cast to the Mongo type, or
	 *                               false for no formatting needed. Defaults to
	 *                               false.
	 */
	private static function castType ($var, $type, $format = false) {
		if (!class_exists($type))
			throw new Exception("Unknown Mongo Type: '" . $type . "'");

		if ($format !== false && !is_callable($format)) {
			throw new Exception(
				"Cannot format value using:\n" . print_r($format, true)
			);
		}

		if (self::isDocument($var))
			$var = $var->to_array();

		if (is_array($var)) {
			foreach($var as $key => $value)
				$var[$key] = self::castType($value, $type, $format);
		} else {
			if ($format)
				$var = call_user_func($format, $var);

			$var = $var instanceof $type ? $var : new $type($var);
		}

		return $var;
	}

	/**
	 * MongoId
	 * @param Mixed $var Variable to be cast to a MongoId. Defaults to NULL like
	 *                   the regular MongoId constructor.
	 * @return A new MongoId
	 */
	public static function Id ($var = null) {
		return self::castType($var, "MongoId");
	}

	/**
	 * MongoDate
	 * @param Mixed $var Variable to be cast to a MongoDate. Defaults to false for
	 *                   time(). Accepts anything that strtotime() can handle, and
	 *                   also accepts DateTime objects.
	 * @return A new MongoDate
	 */
	public static function Date ($var = false) {
		if (is_string($var))
			return self::castType($var, "MongoDate", "strtotime");

		if ($var instanceof DateTime)
			$var = $var->getTimestamp();

		if ($var === false)
			$var = time();

		return self::castType($var, "MongoDate");
	}

	/**
	 * MongoRegex
	 * @param Mixec $var Variable to be cast to a MongoRegex
	 * @return A new MongoRegex
	 */
	public static function Regex ($var) {
		return self::castType($var, "MongoRegex");
	}

	/*
	 These following type casters probably aren't used very much
	 */

	/**
	 * MongoCode
	 * @param Mixed $var Variable to be cast to MongoCode
	 * @return A new MongoCode
	 */
	public static function Code ($var) {
		return self::castType($var, "MongoCode");
	}

	/**
	 * MongoBinData
	 * @param Mixed $var Variable to be case to a MongoBinData
	 * @return A new MongoBinData
	 */
	public static function BinData ($var) {
		return self::castType($var, "MongoBinData");
	}

	/**
	 * MongoInt32
	 * @param Mixed $var Variable to be cast to a MongoInt32
	 * @return A new MongoInt32
	 */
	public static function Int32 ($var) {
		return self::castType($var, "MongoInt32", "intval");
	}

	/**
	 * MongoInt64
	 * @param Mixed $var Variable to cast to a MongoInt64
	 * @return A new MongoInt64
	 */
	public static function Int64 ($var) {
		return self::castType($var, "MongoInt64", "intval");
	}

	/**
	 * MongoDBRef
	 * @param Mixed $var Variable to be cast to a MongoDBRef
	 * @return A new MongoDBRef
	 */
	public static function DBRef ($var) {
		return self::castType($var, "MongoDBRef");
	}

	/**
	 * MongoMinKey
	 * @return A new MongoMinKey
	 */
	public static function MinKey () {
		return new MongoMinKey;
	}

	/**
	 * MongoMaxKey
	 * @return a new MongoMaxKey
	 */
	public static function MaxKey () {
		return new MongoMaxKey;
	}

}
