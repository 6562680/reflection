<?php

namespace Gzhegow\Reflection\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;


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
     * @param string        $type
     * @param string|null   $message
     * @param null|callable $tryFunc
     * @param null|callable $catchFunc
     * @param null|callable $finallyFunc
     */
    protected function assertException($type, $message = null, $tryFunc = null, $catchFunc = null, $finallyFunc = null)
    {
        $args = array_slice(func_get_args(), 1, 3);
        foreach ( $args as $arg ) {
            if (is_string($arg)) {
                $message = $arg;

            } elseif (is_callable($arg)) {
                if (! $tryFunc) {
                    $tryFunc = $arg;

                } elseif (! $catchFunc) {
                    $catchFunc = $arg;

                } elseif (! $finallyFunc) {
                    $finallyFunc = $arg;

                }
            }
        }

        $message = is_string($message) && $message
            ? $message
            : 'Failed exception was thrown: ' . $type;

        $catched = false;

        if (! $tryFunc) {
            $this->expectException($type);

        } else {
            $result = null;
            $exception = null;
            try {
                $result = call_user_func($tryFunc);
            }
            catch ( \Exception $exception ) {
                $catched = true;

                $this->assertThat($exception, new ExceptionConstraint($type));

                if ($catchFunc) {
                    $catchFunc($exception);
                }
            }
            finally {
                if ($finallyFunc) {
                    $finallyFunc($result, $exception);
                }
            }
        }

        $this->assertTrue($catched, $message);
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
