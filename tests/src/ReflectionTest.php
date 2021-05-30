<?php

namespace Gzhegow\Reflection\Tests;

use Gzhegow\Support\Php;
use Gzhegow\Support\Arr;
use Gzhegow\Support\Str;
use Gzhegow\Support\Filter;
use Gzhegow\Reflection\Assert;
use PHPUnit\Framework\TestCase;
use Gzhegow\VarDumper\VarDumper;
use Gzhegow\Reflection\Reflection;
use Gzhegow\Reflection\Domain\Reflector;
use Gzhegow\Support\Domain\SupportFactory;
use Gzhegow\Reflection\ReflectionInterface;
use Gzhegow\Reflection\Tests\Services\MyClassA;
use Gzhegow\Reflection\Tests\Services\MyClassB;
use Gzhegow\Support\Domain\Debug\TestCaseTrait;
use Gzhegow\Reflection\Tests\Services\MyClassC;
use Gzhegow\Reflection\Domain\ReflectionTypeParser;
use Gzhegow\Reflection\Domain\ReflectionClassUseParser;
use Gzhegow\Reflection\Tests\Services\AbstractMyClassA;
use Gzhegow\Reflection\Tests\Services\AbstractMyTraitA;


class ReflectionTest extends TestCase
{
    use TestCaseTrait;


    protected function getArr() : Arr
    {
        return ( new SupportFactory() )->newArr();
    }

    protected function getFilter() : Filter
    {
        return ( new SupportFactory() )->newFilter();
    }

    protected function getPhp() : Php
    {
        return ( new SupportFactory() )->newPhp();
    }

    protected function getStr() : Str
    {
        return ( new SupportFactory() )->newStr();
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
        return new ReflectionTypeParser(
            $this->getArr(),
            $this->getFilter(),
            $this->getStr(),

            $this->getAssert(),
            $this->getReflector()
        );
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

        $this->assertEquals(null, $reflector->reflectClass(null));
        $this->assertEquals(null, $reflector->reflectClass(1));
        $this->assertEquals(null, $reflector->reflectClass(''));
        $this->assertEquals(null, $reflector->reflectClass('a'));
        $this->assertEquals(null, $reflector->reflectClass(\ReflectionClass::class));

        $this->assertInstanceOf(\ReflectionClass::class, $reflection = $reflector->reflectClass(\StdClass::class));
        $this->assertEquals('stdClass', $reflection->getName());

        $this->assertInstanceOf(\ReflectionClass::class, $reflection = $reflector->reflectClass(MyClassA::class));
        $this->assertEquals(MyClassA::class, $reflection->getName());

        $this->assertInstanceOf(\ReflectionClass::class, $reflection = $reflector->reflectClass(AbstractMyClassA::class));
        $this->assertEquals(AbstractMyClassA::class, $reflection->getName());

        $this->assertInstanceOf(\ReflectionClass::class, $reflection = $reflector->reflectClass(AbstractMyTraitA::class));
        $this->assertEquals(AbstractMyTraitA::class, $reflection->getName());
    }

    public function testReflectReflectionClass()
    {
        $reflector = $this->getReflector();

        $reflection = $reflector->reflectClass(MyClassA::class);
        $reflection2 = $reflector->reflectClass($reflection);

        $this->assertTrue($reflection === $reflection2);
    }


    public function testReflectFunction()
    {
        $reflector = $this->getReflector();

        $func = function () { };

        $this->assertInstanceOf(\ReflectionFunction::class, $reflector->reflectFunction('is_string'));
        $this->assertInstanceOf(\ReflectionFunction::class, $reflector->reflectFunction($func));
    }

    public function testReflectReflectionFunction()
    {
        $reflector = $this->getReflector();

        $reflection = $reflector->reflectFunction('is_string');
        $reflection2 = $reflector->reflectFunction($reflection);

        $this->assertTrue($reflection === $reflection2);
    }


    public function testReflectMethod()
    {
        $reflector = $this->getReflector();

        $a = new MyClassA();

        $this->assertInstanceOf(\ReflectionMethod::class, $reflector->reflectMethod([ $a, 'methodPublic' ]));
        $this->assertInstanceOf(\ReflectionMethod::class, $reflector->reflectMethod([ MyClassA::class, 'methodPublicStatic' ]));
        $this->assertInstanceOf(\ReflectionMethod::class, $reflector->reflectMethod([ MyClassA::class, 'methodPublic' ]));
        $this->assertInstanceOf(\ReflectionMethod::class, $reflector->reflectMethod(MyClassA::class . '::methodPublicStatic'));
        $this->assertInstanceOf(\ReflectionMethod::class, $reflector->reflectMethod(MyClassA::class . '@methodPublic'));
    }

    public function testReflectReflectionMethod()
    {
        $reflector = $this->getReflector();

        $reflection = $reflector->reflectMethod([ MyClassA::class, 'methodPublic' ]);
        $reflection2 = $reflector->reflectMethod($reflection);

        $this->assertTrue($reflection === $reflection2);
    }


    public function testReflectProperty()
    {
        $reflector = $this->getReflector();

        $b = new MyClassB();

        $this->assertInstanceOf(\ReflectionProperty::class, $reflector->reflectProperty($b, 'propertyPublic'));
        $this->assertInstanceOf(\ReflectionProperty::class, $reflector->reflectProperty(MyClassB::class, 'propertyPublic'));
    }

    public function testReflectReflectionProperty()
    {
        $reflector = $this->getReflector();

        $reflection = $reflector->reflectProperty(MyClassB::class, 'propertyPublic');
        $reflection2 = $reflector->reflectProperty($reflection);

        $this->assertTrue($reflection === $reflection2);
    }


    public function testReflectParameter()
    {
        $reflector = $this->getReflector();

        $c = new MyClassC();

        $this->assertInstanceOf(\ReflectionParameter::class, $reflector->reflectParameter([ $c, 'method' ], 'a'));
        $this->assertInstanceOf(\ReflectionParameter::class, $reflector->reflectParameter([ $c, 'method' ], 1));
        $this->assertInstanceOf(\ReflectionParameter::class, $reflector->reflectParameter([ MyClassC::class, 'method' ], 'a'));
        $this->assertInstanceOf(\ReflectionParameter::class, $reflector->reflectParameter([ MyClassC::class, 'method' ], 1));
    }

    public function testReflectReflectionParameter()
    {
        $reflector = $this->getReflector();

        $reflection = $reflector->reflectParameter([ MyClassC::class, 'method' ], 'a');
        $reflection2 = $reflector->reflectParameter($reflection);

        $this->assertTrue($reflection === $reflection2);
    }


    /**
     * @noinspection PhpUndefinedFieldInspection
     */
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

        // $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'declared' => true ] + $default;
        // $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateAbstract'));

        $tags = [ 'public' => true, 'present' => true, 'abstract' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPublicStaticAbstract'));

        $tags = [ 'protected' => true, 'present' => true, 'abstract' => true, 'declared' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodProtectedStaticAbstract'));

        // $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'declared' => true, 'static' => true ] + $default;
        // $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateStaticAbstract'));


        $tags = [ 'public' => true, 'present' => true, 'abstract' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPublicAbstractTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'abstract' => true, 'trait' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodProtectedAbstractTrait'));

        // $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'trait' => true ] + $default;
        // $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateAbstractTrait'));

        $tags = [ 'public' => true, 'present' => true, 'abstract' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPublicStaticAbstractTrait'));

        $tags = [ 'protected' => true, 'present' => true, 'abstract' => true, 'trait' => true, 'static' => true ] + $default;
        $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodProtectedStaticAbstractTrait'));

        // $tags = [ 'private' => true, 'present' => true, 'abstract' => true, 'trait' => true, 'static' => true ] + $default;
        // $this->assertEquals($tags, $reflector->methodTags(AbstractMyClassA::class, 'methodPrivateStaticAbstractTrait'));
    }


    public function testParseParameterType()
    {
        $typeParser = $this->getReflectionTypeParser();

        $c = new MyClassC();

        dd([
            $typeParser->extractParameterType([ $c, 'method' ], 'dddd'),
        ]);

        $this->assertEquals(null, $typeParser->extractParameterType([ $c, 'method' ], 'a'));
    }


    public function testParsePropertyType()
    {
        $typeParser = $this->getReflectionTypeParser();

        $c = new MyClassC();

        dump([
            // $typeParser->extractType($c, 'property'),
            // $typeParser->extractPropertyType($c, 'propertyArray'),
            // $typeParser->extractType($c, 'propertyStrings'),
            // $typeParser->extractType($c, 'propertyStringsDeep'),
            // $typeParser->extractType($c, 'propertyObject'),
            // $typeParser->extractType($c, 'propertyObjects'),
            // $typeParser->extractType($c, 'propertyObjectsDeep'),
            // $typeParser->extractType($c, 'propertyUnion'),
            // $typeParser->extractType($c, 'propertyUnionObjects'),
            // $typeParser->extractType($c, 'propertyUnionObjectsDeep'),
            // $typeParser->extractType($c, 'propertyStringsUnion'),
            // $typeParser->extractType($c, 'propertyStringsUnionObjects'),
            // $typeParser->extractType($c, 'propertyStringsUnionObjectsDeep'),
            // $typeParser->extractType($c, 'propertyStringsDeepUnion'),
            // $typeParser->extractType($c, 'propertyStringsDeepUnionObjects'),
            // $typeParser->extractType($c, 'propertyStringsDeepUnionObjectsDeep'),
            // $typeParser->extractType($c, 'propertyCollectionObjects'),
            // $typeParser->extractType($c, 'propertyCollectionObjectKeys'),
            // $typeParser->extractType($c, 'propertyCollectionObjectKeysDeep'),
        ]);

        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'property'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyArray'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStrings'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStringsDeep'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyObject'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyObjects'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyObjectsDeep'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyUnion'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyUnionObjects'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyUnionObjectsDeep'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStringsUnion'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStringsUnionObjects'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStringsUnionObjectsDeep'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStringsDeepUnion'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStringsDeepUnionObjects'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyStringsDeepUnionObjectsDeep'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyCollectionObjects'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyCollectionObjectKeys'));
        $this->assertEquals(null, $typeParser->extractPropertyType($c, 'propertyCollectionObjectKeysDeep'));
    }



    protected static function boot() : void
    {
        VarDumper::getInstance()->nonInteractive(true);
    }
}
