<?php

namespace Gzhegow\Reflection\Tests\Services;


trait AbstractMyTraitA
{
    abstract public function methodPublicAbstractTrait();

    abstract protected function methodProtectedAbstractTrait();


    abstract public static function methodPublicStaticAbstractTrait();

    abstract protected static function methodProtectedStaticAbstractTrait();
}
