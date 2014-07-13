<?php

/**
 * Collection Class
 * ================
 * Manipulate sets of Documents
 * Contains static methods to query MongoDB
 */

namespace btleffler\Phongorm;
use PhongormException as Exception;
use Document;

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

	public static function mongoCollection ($collection = false) {
		if (is_string($collection))
			return self::db()->$collection;

		return self::db()->{self::collectionName()};
	}

	protected static function returnCollection ($query = array(), $fields = array()) {
		$config = self::config();
		$collection = self::collectionClassName();

		return new $collection(
			self::connection(),
			$config["dbName"] . self::collectionName(),
			self::validate($query),
			$fields
		);
	}

	protected static function returnDocument ($data) {
		$className = self::documentClassName();

		return new $className($data);
	}

	private static function validateAgainst ($data, $definitions) {
		foreach($data as $key => &$value) {
			if (Collection::isDocument($value))
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
					throw new Exception("Unknown Type: " . $type);

				$value = Collection::$type($value);
			}
		}

		return $data;
	}

	protected static function validate ($data) {
		if (!isset(static::$document))
			return $data;

		return self::validateAgainst($data, static::$document);
	}

	// Document Manipulation Methods
	public static function find ($data = array(), $fields = array()) {
		return self::returnCollection($data, $fields);
	}

	public static function findOne ($data = array(), $fields = array()) {
		return self::find($data, $fields)->first();
	}

	public static function update ($data = array(), $criteria = array(), $force = false) {
		if (!count($data))
			return;

		if (!count($criteria) && !$force) {
			throw new Exception(
				"Unsafe update of every " . self::collectionName() . " document."
			);
		}

		$data = self::validate($data);
		$criteria = self::validate($criteria);
		$criteria["multi"] = true;

		$result = self::mongoCollection()->update(
			$criteria,
			array( '$set' => $data ),
			array( 'w' => true )
		);

		if ($result["err"] || !$result["ok"]) {
			throw new Exception(
				"Unable to update " . self::collectionName() . " documents."
			);
		}

		unset($criteria["multi"]);

		return self::find(array_replace_recursive($criteria, $data));
	}

	public static function updateOne ($data = array(), $id = null) {
		if (!count($data))
			return self::find();

		$data_has_id = array_key_exists("_id", $data);

		if (is_null($id) && $data_has_id)
			$id = $data["_id"];

		if ($data_has_id)
			unset($data["_id"]);

		if (is_null($id)) {
			throw new Exception(
				"Unsafe use of Collection::updateOne() with no primary key."
			);
		}

		$data = self::validate($data);
		$id = Collection::Id($id);

		$result = self::mongoCollection()->update(
			array( "_id" => $id ),
			array( '$set' => $data ),
			array( 'w' => true )
		);

		if ($result["err"] || !$result["ok"]) {
			throw new Exception(
				"Unable to update " . self::collectionName() . " document."
			);
		}

		return self::findOne(array_merge($data, array( "_id" => $id )));
	}

	// Instance methods
	public function current () {
		$document = parent::current();

		if (is_array($document))
			return static::returnDocument($document);

		return $document;
	}

	public function data () {
		$data = iterator_to_array($this);

		foreach($data as $key => $doc)
			$data[$key] = self::returnDocument($doc);

		return $data;
	}

	public function has () {
		return (bool)$this->count();
	}

	public function first () {
		$this->rewind();
		return $this->current();
	}

	public function last () {
		$elem = null;

		do {
			$elem = $this->current();
			$this->next();
		} while ($this->valid());

		return $elem;
	}

	public function single () {
		return $this->current();
	}

	private function _get ($offset) {
		$this->rewind();

		do {
			if ($offset == $this->key())
				return $this->current();

			$this->next();
		} while ($this->valid());
	}

	private function _exists ($offset) {
		$this->_get($offset);

		return $this->valid();
	}

	private function _set ($offset, $value) {
		throw new Exception(
			"Tried to set " . $offset . " on " . self::collectionClassName() .
			" but probably meant to set on " . self::documentClassName() . '.'
		);
	}

	private function _unset ($offset) {
		throw new Exception(
			"Tried to unset " . $offset . " on " . self::collectionClassName() .
			" but probably meant to unset on " . self::documentClassName() . '.'
		);
	}

	public function offsetExists ($offset) {
		return $this->_exists($offset);
	}

	public function offsetGet ($offset) {
		return $this->_get($offset);
	}

	public function offsetSet ($offset, $value) {
		return $this->_set($offset, $value);
	}

	public function offsetUnset ($offset) {
		return $this->_unset($offset);
	}

	public function __isset ($name) {
		return $this->_exists($name);
	}

	public function __get ($name) {
		return $this->_get($name);
	}

	public function __set ($name, $value) {
		return $this->_set($name, $value);
	}

	public function __unset ($name) {
		return $this->_unset($name);
	}

	// Helper functions
	public static function isDocument ($var) {
		return $var instanceof Document || is_subclass_of($var, "Document");
	}

	public static function isCollection ($var) {
		return $var instanceof Collection || is_subclass_of($var, "Collection");
	}

	private static function castType ($var, $type, $format = false) {
		if (!class_exists($type))
			throw new Exception("Unknown Mongo Type: " . $type);

		if ($format !== false && !is_callable($format)) {
			throw new Exception(
				"Cannot format value using: " . print_r($format, true)
			);
		}

		if (Collection::isDocument($var))
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

	public static function Id ($var = null) {
		return self::castType($var, "MongoId");
	}

	public static function Code ($var) {
		return self::castType($var, "MongoCode");
	}

	public static function Date ($var = false) {
		if (is_string($var))
			return self::castType($var, "MongoDate", "strtotime");

		if ($var instanceof DateTime)
			$var = $var->getTimestamp();

		if ($var === false)
			$var = time();

		return self::castType($var, "MongoDate");
	}

	public static function Regex ($var) {
		return self::castType($var, "MongoRegex");
	}

	public static function BinData ($var) {
		return self::castType($var, "MongoBinData");
	}

	public static function Int32 ($var) {
		return self::castType($var, "MongoInt32", "intval");
	}

	public static function Int64 ($var) {
		return self::castType($var, "MongoInt64", "intval");
	}

	public static function DBRef ($var) {
		return self::castType($var, "MongoDBRef");
	}

	public static function MinKey () {
		return new MongoMinKey;
	}

	public static function MaxKey () {
		return new MongoMaxKey;
	}

}
