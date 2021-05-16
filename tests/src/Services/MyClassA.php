<?php

namespace Gzhegow\Reflection\Tests\Services;


class MyClassA
{
    use MyTraitA;


    public function methodPublic()
    {
    }

    public function methodProtected()
    {
    }

    public function methodPrivate()
    {
    }


    final public function methodPublicFinal()
    {
    }

    final public function methodProtectedFinal()
    {
    }

    final public function methodPrivateFinal()
    {
    }


    public static function methodPublicStatic()
    {
    }

    public static function methodProtectedStatic()
    {
    }

    public static function methodPrivateStatic()
    {
    }


    final public static function methodPublicStaticFinal()
    {
    }

    final public static function methodProtectedStaticFinal()
    {
    }

    final public static function methodPrivateStaticFinal()
    {
    }
}
