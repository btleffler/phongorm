Phongorm - Work In Progress
============================
###PHP-Mongo-Orm

My ideal MongoDB modeling system for PHP.
_I'd love some suggestions. Feel free to open an issue with pointers or ideas._

Collection mostly implemented.
   *   Still need to do many to many relationships via classes extending Collection.
   *   Implementing Documents will probably come first.

Absolutely no warranty.

Typical [MIT licence](http://opensource.org/licenses/MIT), etc.

###Simple Usage - WORK IN PROGRESS

```php
class Foos extends Collection {
	public static $collectionName = "foos_collection";

	public static $document = array(
		"_id" => "Id", // Typically the primary key
		"created" => "Date", // Single MongoDate or array of MongoDates
		"bar_ids" => "Id" // Single MongoId or array of MongoIds
	);

	public static $relationships = array(
		"bars" => array(
			"Bars._id" => "this.other_ids"
		)
	);
}

class Bars extends Collection {
	public static $collectionName = "bars_collection";

	public static $document = array(
		"_id" => "Id",
		"foo_ids" => "Id"
	);

	public static $relationships = array(
		"foos" => array(
			/**
			 * Mongo:
			 * db.foos_collection.find({
			 * 	'$or': [
			 * 		{ "_id": (bars_collection.thing_ids[0]) },
			 *			...	,
			 *		{ "_id": (bars_collection.thing_ids[N]) }
			 * 	]
			 * })
			 */
			"Foos._id" => "this.foo_ids"
		)
	);
}

/**
 * Document classes aren't needed unless you want to
 * implement business logic or check types or something.
 */
class FoosDocument extends Document {
	public function doWork () {
		echo "Hello World!, my _id is " $this->_id;
	}
}

class BarsDocument extends Document { /* Bars Business Logic */ }

// Foos Collection containing all FoosDocuments
$foos = Foos::find(/* Can query using regular syntax too */);

// FoosDocument object if the class exists. Otherwise just a Document object.
$firstFoo = $foos->first();

// Bars Collection containing BarsDocuments related to the FoosDocuments in $foos.
$barsRelatedToFoos = $foos->bars();

// Echos "Hello World!, my _id is [string version of MongoId _id]"
$firstFoo->doWork();
```
