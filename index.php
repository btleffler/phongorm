<?php

namespace Btleffler;

spl_autoload_register(function ($class) {
	include getcwd() . "/lib/" . preg_replace("/^Phongorm\\\\/", '/', $class) .
		".php";
});

use Phongorm\Phongorm;
use Phongorm\Collection\Collection;
use Phongorm\Document\Document;

var_dump(Phongorm::isDocument(new \stdClass));
