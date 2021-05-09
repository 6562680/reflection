<?php

namespace Gzhegow\Reflection\Tests;

use Gzhegow\Support\Php;
use Gzhegow\Support\Filter;
use PHPUnit\Framework\TestCase;
use Gzhegow\Reflection\Reflection;
use Gzhegow\Reflection\ReflectionInterface;


class ReflectionTest extends TestCase
{
    protected function getFilter() : Filter
    {
        return new Filter();
    }

    protected function getPhp() : Php
    {
        return new Php(
            $this->getFilter()
        );
    }



    protected function getReflection() : ReflectionInterface
    {
        return new Reflection(
            $this->getFilter(),
            $this->getPhp()
        );
    }


    public function testReflectClass()
    {
        $instance = $this->getReflection();

        $reflection = $instance->reflectClass(\StdClass::class);

        $this->assertEquals('stdClass', $reflection->getName());
    }
}
