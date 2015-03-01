<?php

/**
 * Collection_Config class
 * ==================================
 * Setup a Phongorm Collection class
 */

namespace Phongorm\Collection;

class Collection_Config {

	/**
	 * Default Document Descriptor
	 * @var array Define what the documents in the collection will look like.
	 *            Accepts "Key" => "Phongorm Type",
	 *            "Key" => array( "Phongorm Type" ),
	 *            "Key" => array( "Key" => "Phongorm Type | array(...)", ... )
	 */
	public static $document = array( "_id" => "Id" );

	/**
	 * Store the configuration for Phongorm
	 * @var Set via Phongorm::config()
	 */
	private static $config;

	/**
	 * Keep track of the MongoClient
	 * @var MongoClient
	 */
	private static $connection;

	/**
	 * Keep track of the MongoDB
	 * @var MongoDB
	 */
	private static $db;

	/**
	 * Set config
	 * @param	array	$set Array of config params. Same as what MongoClient accepts.
	 * @return array     The config
	 */
	public static function config ($set = array()) {
		foreach ($set as $key => $prop)
			self::$config[$key] = $prop;

		return $config;
	}

	/**
	 * Get the connection (MongoClient)
	 * @return MongoClient The MongoClient
	 */
	protected static function connection () {
		if (self::$connection)
			return self::$connection;

		return self::$connection = new \MongoClient(self::$config);
	}

	/**
	 * Get the DB (MongoDB)
	 * @return MongoDB The MongoDB
	 */
	protected static function db () {
		if (self::$db)
			return self::$db;

		return self::$db = self::connection()->{self::$config["database"]};
	}

	/**
	 * Get the collection name for the Phongorm Collection
	 * @return string The MongoDB collection name
	 */
	public static function collectionName () {
		if (isset(static::$collectionName))
			return static::$collectionName;

		$class = get_called_class();
		return preg_replace("/Collection$/i", '', $class);
	}

	/**
	 * Get the Phogorm Document class name
	 * @return string The Phongorm Document class name
	 */
	public static function documentClassName () {
		if (isset(static::$documentClass))
			return static::$documentClass;

		$class = get_called_class() . "Document";

		if (class_exists($class))
			return $class;

		return "Document";
	}

	/**
	 * Get the Phongorm Collection class name. Same as PHP's get_called_class()
	 * @return string The Phongorm Collection class name.
	 */
	public static function collectionClassName () {
		return get_called_class();
	}

	/**
	 * Get a MongoCollection instance. NOTE: Hopefully, you won't need to do this.
	 *                                   It's used internally, but Phongorm should
	 *                                   do the heavy lifting for you.
	 * @param string $collection The name of the MongoCollection to be returned
	 * @return MongoCollection   The MongoCollection instance
	 */
	public static function mongoCollection ($collection = false) {
		if (is_string($collection))
			return self::db()->$collection;

		return self::db()->{self::collectionName()};
	}

}
