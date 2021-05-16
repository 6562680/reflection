<?php

namespace Gzhegow\Reflection\Tests\Services;


class MyClassB
{
    use MyTraitB;


    private $propertyPrivate;
    protected $propertyProtected;
    public $propertyPublic;

    public static $propertyPublicStatic;
    private static $propertyPrivateStatic;
    protected static $propertyProtectedStatic;
}
