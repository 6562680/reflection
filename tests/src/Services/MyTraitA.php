<?php

namespace Gzhegow\Reflection\Tests\Services;


trait MyTraitA
{
    public function methodPublicTrait()
    {
    }

    final public function methodPublicFinalTrait()
    {
    }


    protected function methodProtectedTrait()
    {
    }

    final protected function methodProtectedFinalTrait()
    {
    }


    private function methodPrivateTrait()
    {
    }

    final private function methodPrivateFinalTrait()
    {
    }


    public static function methodPublicStaticTrait()
    {
    }

    final public static function methodPublicStaticFinalTrait()
    {
    }


    protected static function methodProtectedStaticTrait()
    {
    }

    final protected static function methodProtectedStaticFinalTrait()
    {
    }


    private static function methodPrivateStaticTrait()
    {
    }

    final private static function methodPrivateStaticFinalTrait()
    {
    }
}
