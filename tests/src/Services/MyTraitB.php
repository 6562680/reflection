<?php

namespace Gzhegow\Reflection\Tests\Services;


trait MyTraitB
{
    private $propertyPrivateTrait;
    protected $propertyProtectedTrait;
    public $propertyPublicTrait;

    public static $propertyPublicStaticTrait;
    private static $propertyPrivateStaticTrait;
    protected static $propertyProtectedStaticTrait;
}
