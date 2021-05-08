<?php

namespace Gzhegow\Reflection\Tests;

use Gzhegow\Support\Php;
use Gzhegow\Support\Type;
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

    protected function getType() : Type
    {
        return new Type(
            $this->getFilter()
        );
    }

    protected function getPhp() : Php
    {
        return new Php(
            $this->getFilter(),
            $this->getType(),
        );
    }



    protected function getReflection() : ReflectionInterface
    {
        return new Reflection(
            $this->getPhp(),
            $this->getType(),
        );
    }


    public function testReflectClass()
    {
        $instance = $this->getReflection();

        $reflection = $instance->reflectClass(\StdClass::class);

        $this->assertEquals('stdClass', $reflection->getName());
    }
}
