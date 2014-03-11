<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Generics;

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Loader extends Object
{

	/** @var Generics */
	private $generics;


	/**
	 * @param Generics $generics
	 */
	public function __construct(Generics $generics)
	{
		$this->generics = $generics;
	}


	public function register()
	{
		spl_autoload_register(array($this, 'load'));
	}


	public function load($class)
	{
		$types = explode($this->generics->separator, $class);
		$class = $types[0];
		unset($types[0]);
		$types = array_merge($types);

		if (!class_exists($class)) {
			return NULL;
		}

		$this->generics->prepareType($class, $types);
	}

}
