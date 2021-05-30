<?php

namespace Gzhegow\Reflection\Tests\Services;


class MyClassA
{
    use MyTraitA;


    public function methodPublic()
    {
    }

    final public function methodPublicFinal()
    {
    }


    protected function methodProtected()
    {
    }

    final protected function methodProtectedFinal()
    {
    }


    private function methodPrivate()
    {
    }

    final private function methodPrivateFinal()
    {
    }


    public static function methodPublicStatic()
    {
    }

    final public static function methodPublicStaticFinal()
    {
    }


    protected static function methodProtectedStatic()
    {
    }

    final protected static function methodProtectedStaticFinal()
    {
    }


    private static function methodPrivateStatic()
    {
    }

    final private static function methodPrivateStaticFinal()
    {
    }
}
