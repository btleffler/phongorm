<?php

/**
 * Collection Class
 * ================
 * Manipulate sets of Documents
 * Contains static methods to query MongoDB
 */

class Collection extends MongoCursor implements ArrayAccess {

	public static $document = array( "_id" => "Id" );

	private static $config;
	private static $connection;
	private static $db;

	public static function config ($set = array()) {
		if (!count($set))
			return $config;

		foreach ($set as $key => $prop)
			self::$config[$key] = $prop;

		return $config;
	}

	protected static function connection () {
		if (self::$connection)
			return self::$connection;

		return self::$connection = new MongoClient(self::$config);
	}

	protected static function db () {
		if (self::$db)
			return self::$db;

		return self::$db = self::connection()->{self::$config["database"]};
	}

	public static function collectionName () {
		if (isset(static::$collectionName))
			return static::$collectionName;

		$class = get_called_class();
		return preg_replace("/Collection$/i", '', $class);
	}

	public static function documentClassName () {
		if (isset(static::$documentClass))
			return static::$documentClass;

		$class = get_called_class() . "Document";

		if (class_exists($class))
			return $class;

		return "Document";
	}

	public static function collectionClassName () {
		return get_called_class();
	}

}
