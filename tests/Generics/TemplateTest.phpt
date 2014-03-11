<?php

namespace VenneTests\Generics;

use Tester\Assert;
use Tester\TestCase;
use Venne\Generics\Template;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TemplateTest extends TestCase
{

	public function dataUses()
	{
		return array(
			array(new Template('<?php use Foo; class A {}'), array('Foo' => 'Foo')),
			array(new Template('<?php use Foo\Bar; class A {}'), array('Bar' => 'Foo\Bar')),
			array(new Template('<?php use Foo\Bar, A\B; class A {}'), array('Bar' => 'Foo\Bar', 'B' => 'A\B')),
		);
	}


	/**
	 * @dataProvider dataUses
	 */
	public function testUses($a, $b)
	{
		Assert::equal($b, $a->getUses());
	}


	public function testReplaceNamespace()
	{
		$template = new Template('<?php namespace Foo\Bar; class A {}');
		$template->replaceNamespace('Foo\Bar');
		Assert::same('<?php namespace Foo\Bar; class A {}', (string)$template);
	}

	public function testReplaceClassName()
	{
		$template = new Template('<?php namespace Foo\Bar; class A {}');
		$template->replaceClassName('B');
		Assert::same('<?php namespace Foo\Bar; class B {}', (string)$template);

		$template->replaceClassName('AA\BB\CC');
		Assert::same('<?php namespace AA\BB; class CC {}', (string)$template);
	}


	public function testReplaceClassString()
	{
		$template = new Template('<?php namespace Foo\Bar; class A extends B {}');
		$template->replaceClassString('B', 'C');
		Assert::same('<?php namespace Foo\Bar; class A extends \\C {}', (string)$template);

		$template = new Template('<?php namespace Foo\Bar; class A implements B {}');
		$template->replaceClassString('B', 'C');
		Assert::same('<?php namespace Foo\Bar; class A implements \\C {}', (string)$template);

		$template = new Template('<?php namespace Foo\Bar; class A implements B, C, D {}');
		$template->replaceClassString('B', 'M');
		$template->replaceClassString('C', 'N');
		$template->replaceClassString('D', 'O');
		Assert::same('<?php namespace Foo\Bar; class A implements \\M, \\N, \\O {}', (string)$template);

		$template = new Template('<?php namespace Foo\Bar; class A extends B { public function run(Foo $a) {} }');
		$template->replaceClassString('Foo', 'Bar');
		Assert::same('<?php namespace Foo\Bar; class A extends B { public function run(\\Bar $a) {} }', (string)$template);

		$template = new Template('<?php namespace Foo\Bar; class A extends B { public function run(Foo $a) {} }');
		$template->replaceClassString('Foo', '');
		Assert::same('<?php namespace Foo\Bar; class A extends B { public function run($a) {} }', (string)$template);

		$template = new Template('<?php namespace Foo\Bar; class A extends B { public function run(Foo $a, Car $b, Dog $d) {} }');
		$template->replaceClassString('Foo', 'Bar');
		$template->replaceClassString('Car', 'Bus');
		Assert::same('<?php namespace Foo\Bar; class A extends B { public function run(\\Bar $a, \\Bus $b, Dog $d) {} }', (string)$template);

		$template = new Template('<?php class A extends B { public function run() {$t = new Foo; $u = new Car();} }');
		$template->replaceClassString('Foo', 'Bar');
		$template->replaceClassString('Car', 'Bus');
		Assert::same('<?php class A extends B { public function run() {$t = new \\Bar; $u = new \\Bus();} }', (string)$template);

		$template = new Template('<?php class A extends B { public function run() {echo Foo::$aa; echo A::$bb;} }');
		$template->replaceClassString('Foo', 'Bar');
		Assert::same('<?php class A extends B { public function run() {echo \\Bar::$aa; echo A::$bb;} }', (string)$template);

	}

	public function testMakeAbsoluteClassNames()
	{
		$template = new Template('<?php namespace AA\BB; use Foo\Bar; class A { public function run() { Bar::test(); } }');
		$template->makeAbsoluteClassNames('AA\BB');
		Assert::same('<?php namespace AA\BB; use Foo\Bar; class A { public function run() { \Foo\Bar::test(); } }', (string)$template);

		$template = new Template('<?php namespace AA\BB; use Foo\Bar; class A { public function run() { \Bar::test(); } }');
		$template->makeAbsoluteClassNames('AA\BB');
		Assert::same('<?php namespace AA\BB; use Foo\Bar; class A { public function run() { \Bar::test(); } }', (string)$template);
	}

}

$testCase = new TemplateTest;
$testCase->run();
