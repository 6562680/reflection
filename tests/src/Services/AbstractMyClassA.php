<?php

namespace Gzhegow\Reflection\Tests\Services;


abstract class AbstractMyClassA
{
    use AbstractMyTraitA;


    abstract public function methodPublicAbstract();

    abstract public function methodProtectedAbstract();

    abstract public function methodPrivateAbstract();


    abstract public static function methodPublicStaticAbstract();

    abstract public static function methodProtectedStaticAbstract();

    abstract public static function methodPrivateStaticAbstract();
}
