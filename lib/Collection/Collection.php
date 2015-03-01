<?php

/**
 * Collection Class
 * ================
 * Contains static methods to query MongoDB
 * and instance methods to query sets of Documents
 */

namespace Phongorm\Collection;

use Phongomr\Phongorm;
use Phongorm\PhongormException as Exception;
use Phongorm\Document\Document;

use Phongorm\Collection\Collection_Query;

use \MongoCursor;
use \ArrayAccess;

class Collection extends Collection_Query implements ArrayAccess {

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
				"Unsafe use of Phongorm::updateOne() with no primary key."
			);
		}

		$data = self::validate($data);
		$id = Phongorm::Id($id);

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
		$start = $this->key();

		do {
			if ($offset == $this->key())
				return $this->current();

			$this->next();
		} while ($this->valid());

		$this->rewind();

		do {
			if ($offset == $this->key())
				return $this->current();

			$this->next();
		} while ($this->key() !== $start);
	}

	private function _exists ($offset) {
		$this->_get($offset);

		return $this->valid();
	}

	private function _set ($offset, $value) {
		throw new Exception(
			"Tried to set '" . $offset . "' on a " . self::collectionClassName() .
			" but probably meant to set on a " . self::documentClassName() . '.'
		);
	}

	private function _unset ($offset) {
		throw new Exception(
			"Tried to unset '" . $offset . "' on a " . self::collectionClassName() .
			" but probably meant to set on a " . self::documentClassName() . '.'
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

}
