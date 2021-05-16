<?php

namespace Gzhegow\Reflection\Tests;

use Gzhegow\Support\Php;
use Gzhegow\Support\Filter;
use Gzhegow\Reflection\Assert;
use Gzhegow\Reflection\Reflection;
use Gzhegow\Reflection\Domain\Reflector;
use Gzhegow\Reflection\ReflectionInterface;
use Gzhegow\Reflection\Tests\Services\MyClassA;
use Gzhegow\Reflection\Tests\Services\MyClassB;
use Gzhegow\Reflection\Domain\ReflectionTypeParser;
use Gzhegow\Reflection\Domain\ReflectionClassUseParser;
use Gzhegow\Reflection\Tests\Services\AbstractMyClassA;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;


class ReflectionTest extends AbstractTestCase
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

    protected function getAssert() : Assert
    {
        return new Assert(
            $this->getFilter()
        );
    }

    protected function getReflector() : Reflector
    {
        return new Reflector(
            $this->getFilter(),
            $this->getPhp(),
            $this->getAssert()
        );
    }

    protected function getReflectionClassUseParser() : ReflectionClassUseParser
    {
        return new ReflectionClassUseParser(
            $this->getReflector()
        );
    }

    protected function getReflectionTypeParser() : ReflectionTypeParser
    {
        return new ReflectionTypeParser();
    }

    protected function getReflection() : ReflectionInterface
    {
        return new Reflection(
            $this->getAssert(),
            $this->getReflector(),
            $this->getReflectionClassUseParser(),
            $this->getReflectionTypeParser()
        );
    }


    public function testReflectClass()
    {
        $reflector = $this->getReflector();

        $reflection = $reflector->reflectClass(\StdClass::class);

        $this->assertEquals('stdClass', $reflection->getName());

        $reflection = $reflector->reflectClass(MyClassA::class);
        $reflection2 = $reflector->reflectClass($reflection);

        $this->assertTrue($reflection === $reflection2);
    }

    public function testBadReflectClass()
    {
        $reflector = $this->getReflector();

        $this->assertException(InvalidArgumentException::class, function () use ($reflector) {
            $reflection = $reflector->reflectClass(null);
        });

        $this->assertException(InvalidArgumentException::class, function () use ($reflector) {
            $reflection = $reflector->reflectClass(1);
        });

        $this->assertException(InvalidArgumentException::class, function () use ($reflector) {
            $reflection = $reflector->reflectClass('');
        });

        $this->assertException(InvalidArgumentException::class, function () use ($reflector) {
            $reflection = $reflector->reflectClass('a');
        });

        $this->assertException(InvalidArgumentException::class, function () use ($reflector) {
            $reflection = $reflector->reflectClass([]);
        });

        $this->assertException(InvalidArgumentException::class, function () use ($reflector) {
            $reflection = $reflector->reflectClass(\ReflectionClass::class);
        });
    }


    public function testPropertyTags()
    {
        $reflector = $this->getReflector();

        $myB = new MyClassB();
        $myB->propertyDynamic = 1;

        $default = [
            'declared'  => false,
            'default'   => false,
            'present'   => false,
            'private'   => false,
            'protected' => false,
            'public'    => false,
            'static'    => false,
            'trait'     => false,
        ];

        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPublic'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyProtected'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPrivate'));

        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPublicStatic'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyProtectedStatic'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPrivateStatic'));

        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPublicTrait'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyProtectedTrait'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPrivateTrait'));

        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPublicStaticTrait'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyProtectedStaticTrait'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags(MyClassB::class, 'propertyPrivateStaticTrait'));


        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPublic'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyProtected'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPrivate'));

        $tags = [ 'public' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyDynamic'));

        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPublicStatic'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyProtectedStatic'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPrivateStatic'));

        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPublicTrait'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyProtectedTrait'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPrivateTrait'));

        $tags = [ 'default' => true, 'public' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPublicStaticTrait'));

        $tags = [ 'default' => true, 'protected' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyProtectedStaticTrait'));

        $tags = [ 'default' => true, 'private' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->propertyTags($myB, 'propertyPrivateStaticTrait'));
    }

    public function testMethodTags()
    {
        $reflector = $this->getReflector();

        $myA = new MyClassA();

        $default = [
            'abstract'  => false,
            'declared'  => false,
            'final'     => false,
            'present'   => false,
            'private'   => false,
            'protected' => false,
            'public'    => false,
            'static'    => false,
            'trait'     => false,
        ];

        $tags = [ 'public' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublic'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtected'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivate'));

        $tags = [ 'public' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublicStatic'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtectedStatic'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivateStatic'));

        $tags = [ 'public' => true, 'present' => true, 'declared' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublicFinal'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtectedFinal'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivateFinal'));

        $tags = [ 'public' => true, 'present' => true, 'declared' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublicStaticFinal'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtectedStaticFinal'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivateStaticFinal'));


        $tags = [ 'public' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublicTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtectedTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivateTrait'));

        $tags = [ 'public' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublicStaticTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtectedStaticTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivateStaticTrait'));

        $tags = [ 'public' => true, 'present' => true, 'trait' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublicFinalTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtectedFinalTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivateFinalTrait'));

        $tags = [ 'public' => true, 'present' => true, 'trait' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPublicStaticFinalTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodProtectedStaticFinalTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(MyClassA::class, 'methodPrivateStaticFinalTrait'));


        $tags = [ 'public' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublic'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtected'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivate'));

        $tags = [ 'public' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublicStatic'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtectedStatic'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivateStatic'));

        $tags = [ 'public' => true, 'present' => true, 'declared' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublicFinal'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtectedFinal'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivateFinal'));

        $tags = [ 'public' => true, 'present' => true, 'declared' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublicStaticFinal'));

        $tags = [ 'protected' => true, 'present' => true, 'declared' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtectedStaticFinal'));

        $tags = [ 'private' => true, 'present' => true, 'declared' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivateStaticFinal'));


        $tags = [ 'public' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublicTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtectedTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivateTrait'));

        $tags = [ 'public' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublicStaticTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtectedStaticTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivateStaticTrait'));

        $tags = [ 'public' => true, 'present' => true, 'trait' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublicFinalTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtectedFinalTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivateFinalTrait'));

        $tags = [ 'public' => true, 'present' => true, 'trait' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPublicStaticFinalTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'trait' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodProtectedStaticFinalTrait'));

        $tags = [ 'private' => true, 'present' => true, 'trait' => true, 'static' => true, 'final' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags($myA, 'methodPrivateStaticFinalTrait'));
    }

    public function testMethodTagsAbstract()
    {
        $reflector = $this->getReflector();

        $default = [
            'abstract'  => false,
            'declared'  => false,
            'final'     => false,
            'present'   => false,
            'private'   => false,
            'protected' => false,
            'public'    => false,
            'static'    => false,
            'trait'     => false,
        ];

        $tags = [ 'public' => true, 'present' => true, 'abstract' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPublicAbstract'));

        $tags = [ 'protected' => true, 'present' => true, 'abstract' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodProtectedAbstract'));

        $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'declared' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateAbstract'));

        $tags = [ 'public' => true, 'present' => true, 'abstract' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPublicStaticAbstract'));

        $tags = [ 'protected' => true, 'present' => true, 'abstract' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodProtectedStaticAbstract'));

        $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateStaticAbstract'));


        $tags = [ 'public' => true, 'present' => true, 'abstract' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPublicAbstractTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'abstract' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodProtectedAbstractTrait'));

        $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateAbstractTrait'));

        $tags = [ 'public' => true, 'present' => true, 'abstract' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPublicStaticAbstractTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'abstract' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodProtectedStaticAbstractTrait'));

        $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateStaticAbstractTrait'));
    }
}
