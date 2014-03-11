<?php

namespace VenneTests\Generics\Data;

/**
 * Class Foo
 * @package VenneTests\Generics\Data
 * @template Entity
 */
class CarRepository extends Sub\Object {

	public function save(Entity $entity)
	{
		Entity::play();
		Borec\Konec::yeah();

		$a = new Entity;
		$b = new Borec\Konec;
	}

} 