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

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Caching\Storages\PhpFileStorage;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Reflection\ClassType;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Generics extends Object
{

	const CACHE = 'Venne.Generics';

	/** @var IStorage */
	private $storage;

	/** @var array */
	private $loaded = array();

	/** @var string */
	private $separator = '_';


	/**
	 * @param IStorage $storage
	 */
	public function __construct(IStorage $storage = NULL)
	{
		$this->storage = $storage;
	}


	/**
	 * @param $separator
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setSeparator($separator)
	{
		if (!is_string($separator)) {
			throw new InvalidArgumentException("Separator must be string.");
		}

		$this->separator = $separator;
	}


	/**
	 * @return string
	 */
	public function getSeparator()
	{
		return $this->separator;
	}


	/**
	 * @param $class
	 * @param array $types
	 * @param array $arguments
	 * @return object
	 */
	public function createType($class, array $types = array(), array $arguments = array())
	{
		$class = '\\' . $this->prepareType($class, $types);
		$ref = new \ReflectionClass($class);
		return $ref->newInstanceArgs($arguments);
	}


	/**
	 * @param $class
	 * @param array $types
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	public function prepareType($class, array $types = array())
	{
		$class = trim($class, '\\');
		$key = serialize(array(
			'class' => $class,
			'types' => $types,
		));

		if (!isset($this->loaded[$key])) {

			$newClass = $this->prepareClassName($class, $types);
			if ($this->storage) {
				$cache = new Cache($this->storage, 'Venne.Generics');

				$data = $cache->load($key);
				if (!$data) {
					$data = $this->prepareClassTemplate($class, $newClass, $types);
					$cache->save($key, $data);
					$data = $cache->load($key);
				}

				if ($this->storage instanceof PhpFileStorage) {
					\Nette\Utils\LimitedScope::load($data['file']);
				} else {
					\Nette\Utils\LimitedScope::evaluate($data);
				}
			} else {
				$data = $this->prepareClassTemplate($class, $newClass, $types);
				\Nette\Utils\LimitedScope::evaluate($data);
			}

			$this->loaded[$key] = $newClass;
		}

		return $this->loaded[$key];
	}


	/**
	 * @param $class
	 * @param $newClass
	 * @param array $types
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function prepareClassTemplate($class, $newClass, array $types = array())
	{
		$ref = ClassType::from($class);

		if (!$ref->hasAnnotation('template')) {
			throw new InvalidArgumentException("Class '$class' is not template. Annotation @template is not defined");
		}

		$str = trim(file_get_contents($ref->getFileName()));
		$namespace = $ref->getNamespaceName();
		$templateTypes = explode(' ', preg_replace('!\s+!', ' ', str_replace(',', ' ', $ref->getAnnotation('template'))));

		if (count($templateTypes) !== count($types)) {
			throw new InvalidArgumentException();
		}

		$template = new Template($str);
		$template->replaceClassName($newClass);
		foreach ($templateTypes as $key => $templateType) {
			$template->replaceClassString($templateType, $types[$key]);
		}
		$template->makeAbsoluteClassNames($namespace);

		return (string)$template;
	}


	/**
	 * @param $class
	 * @param array $types
	 * @return string
	 */
	protected function prepareClassName($class, array $types = array())
	{
		foreach ($types as $type) {
			$type = trim($type, '\\');
			$class .= $this->separator . $type;
		}

		return $class;
	}

}
