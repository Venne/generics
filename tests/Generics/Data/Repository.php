<?php

namespace VenneTests\Generics\Data;

use Nette\Object;
use Nette\Object\Test as Bos, Kopes;

/**
 * Class Foo
 * @package VenneTests\Generics\Data
 * @template __TYPE__
 */
class Repository {

	public function save(__TYPE__ $entity)
	{
		echo 'fsdfsdfsd';
	}

} 