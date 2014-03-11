<?php

namespace VenneTests\Generics;

use Tester\Assert;
use Tester\TestCase;
use Venne\Generics\Template;
use Venne\Generics\TemplateHelpers;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TemplateHelpersTest extends TestCase
{


	public function testNormalizeClass()
	{
		Assert::same('Foo\Bar', TemplateHelpers::normalizeClass('Foo\Bar'));
		Assert::same('Foo\Bar', TemplateHelpers::normalizeClass('\Foo\Bar'));
	}


	public function testExpandClass()
	{
		Assert::same('C', TemplateHelpers::expandClass('C'));
		Assert::same('A\B\C', TemplateHelpers::expandClass('C', 'A\B'));
		Assert::same('Foo\Bar', TemplateHelpers::expandClass('C', 'A\B', array('C' => 'Foo\Bar')));
		Assert::same('\\AA\\BB', TemplateHelpers::expandClass('\\AA\BB', 'A\B', array('C' => 'Foo\Bar')));
	}



}

$testCase = new TemplateHelpersTest;
$testCase->run();
