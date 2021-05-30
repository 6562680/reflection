<?php

namespace Gzhegow\Reflection\Tests\Services;


abstract class AbstractMyClassA
{
    use AbstractMyTraitA;


    abstract public function methodPublicAbstract();

    abstract protected function methodProtectedAbstract();


    abstract public static function methodPublicStaticAbstract();

    abstract protected static function methodProtectedStaticAbstract();
}
