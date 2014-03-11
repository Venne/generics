<?php

namespace VenneTests\Generics;

use Tester\Assert;
use Tester\TestCase;
use Venne\Generics\Generics;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class GenericsTest extends TestCase
{

	/** @var Generics */
	private $generics;


	public function setUp()
	{
		$this->generics = new Generics;
	}


	public function testCreateType()
	{
		$types = array('VenneTests\Generics\Data\PageEntity');
		$foo = $this->generics->createType('VenneTests\Generics\Data\Repository', $types);

		$ref = new \ReflectionClass($foo);
		foreach ($ref->getMethod('save')->getParameters() as $key => $parameter) {
			Assert::same($types[$key], $parameter->getClass()->name);
		}
	}


	public function testCreateEmptyType()
	{
		$types = array(NULL);
		$foo = $this->generics->createType('VenneTests\Generics\Data\Repository', $types);

		$ref = new \ReflectionClass($foo);
		foreach ($ref->getMethod('save')->getParameters() as $key => $parameter) {
			Assert::null($parameter->getClass());
		}
	}


	public function testCreateTypeInheritance()
	{
		$types = array('VenneTests\Generics\Data\PageEntity');
		$foo = $this->generics->createType('VenneTests\Generics\Data\CarRepository', $types);

		$ref = new \ReflectionClass($foo);
		foreach ($ref->getMethod('save')->getParameters() as $key => $parameter) {
			Assert::same($types[$key], $parameter->getClass()->name);
		}
	}

}

$testCase = new GenericsTest;
$testCase->run();
