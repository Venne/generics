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
class TemplateHelpers extends Object
{


	/**
	 * @param $name
	 * @param $namespace
	 * @param array $uses
	 * @return string
	 */
	public static function expandClass($name, $namespace = '', array $uses = array())
	{
		if (substr($name, 0, 1) === '\\') {
			return $name;
		}

		$name = explode('\\', $name);

		if (isset($uses[$name[0]])) {
			$name[0] = $uses[$name[0]];
			return implode('\\', $name);
		}

		return ($namespace ? $namespace . '\\' : '') . implode('\\', $name);
	}


	/**
	 * @param $class
	 * @return string
	 */
	public static function normalizeClass($class)
	{
		return trim($class, '\\');
	}

}
