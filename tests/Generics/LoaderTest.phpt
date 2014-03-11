<?php

namespace VenneTests\Generics;

use Tester\Assert;
use Tester\TestCase;
use Venne\Generics\Generics;
use Venne\Generics\Loader;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoaderTest extends TestCase
{

	/** @var Loader */
	private $loader;


	public function setUp()
	{
		$this->loader = new Loader(new Generics);
		$this->loader->register();
	}


	public function testCreateType()
	{
		$foo = new \VenneTests\Generics\Data\Repository_VenneTests\Generics\Data\PageEntity;

		$ref = new \ReflectionClass(get_class($foo));
		foreach($ref->getMethod('save')->getParameters() as $key => $parameter) {
			Assert::same('VenneTests\Generics\Data\PageEntity', $parameter->getClass()->name);
		}
	}


	public function testCreateTypeInheritance()
	{
		$foo = new \VenneTests\Generics\Data\CarRepository_VenneTests\Generics\Data\PageEntity;

		$ref = new \ReflectionClass(get_class($foo));
		foreach($ref->getMethod('save')->getParameters() as $key => $parameter) {
			Assert::same('VenneTests\Generics\Data\PageEntity', $parameter->getClass()->name);
		}
	}



}

$testCase = new LoaderTest;
$testCase->run();
