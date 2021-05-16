<?php

namespace Gzhegow\Reflection\Tests\Services;


trait AbstractMyTraitA
{
    abstract public function methodPublicAbstractTrait();

    abstract public function methodProtectedAbstractTrait();

    abstract public function methodPrivateAbstractTrait();


    abstract public static function methodPublicStaticAbstractTrait();

    abstract public static function methodProtectedStaticAbstractTrait();

    abstract public static function methodPrivateStaticAbstractTrait();
}
