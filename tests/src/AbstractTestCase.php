<?php

namespace Gzhegow\Reflection\Tests;

use PHPUnit\Framework\TestCase;


/**
 * AbstractTestCase
 */
abstract class AbstractTestCase extends TestCase
{
	/**
	 * @return void
	 */
	protected function setUp() : void
	{
		if (! static::$boot) {
			static::boot();

			static::$boot = true;
		}
	}


	/**
	 * @return void
	 */
	protected static function boot() : void
	{
	}


	/**
	 * @var bool
	 */
	protected static $boot = false;
}
