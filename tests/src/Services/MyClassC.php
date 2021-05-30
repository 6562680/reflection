<?php

namespace Gzhegow\Reflection\Tests\Services;


class MyClassC
{
    use MyTraitC;


    /**
     * @var string
     */
    public $property;
    /**
     * @var array
     */
    public $propertyArray;
    /**
     * @var string[]
     */
    public $propertyStrings;
    /**
     * @var string[][]
     */
    public $propertyStringsDeep;

    /**
     * @var MyClassC
     */
    public $propertyObject;
    /**
     * @var MyClassC[]
     */
    public $propertyObjects;
    /**
     * @var MyClassC[][]
     */
    public $propertyObjectsDeep;

    /**
     * @var int|MyClassC
     */
    public $propertyUnion;
    /**
     * @var string|MyClassC[]
     */
    public $propertyUnionObjects;
    /**
     * @var MyClassC|MyClassC[][]
     */
    public $propertyUnionObjectsDeep;

    /**
     * @var int[]|MyClassC
     */
    public $propertyStringsUnion;
    /**
     * @var string[]|MyClassC[]
     */
    public $propertyStringsUnionObjects;
    /**
     * @var MyClassC[]|MyClassC[][]
     */
    public $propertyStringsUnionObjectsDeep;

    /**
     * @var int[][]|MyClassC
     */
    public $propertyStringsDeepUnion;
    /**
     * @var string[][]|MyClassC[]
     */
    public $propertyStringsDeepUnionObjects;
    /**
     * @var MyClassC[][]|MyClassC[][]
     */
    public $propertyStringsDeepUnionObjectsDeep;

    /**
     * @var MyCollection<int,MyClassC>
     */
    public $propertyCollectionObjects;
    /**
     * @var MyCollection<MyClassC,MyClassC>
     */
    public $propertyCollectionObjectKeys;
    /**
     * @var MyCollection<MyCollection<string,MyClassC>,MyCollection<MyClassC,MyClassC>>
     */
    public $propertyCollectionObjectKeysDeep;


    /**
     * @param int                                                                          $a
     * @param string                                                                       $b
     * @param MyClassC                                                                     $c
     *
     * @param int|array                                                                    $d
     * @param string[]                                                                     $dd
     * @param string[][]                                                                   $ddd
     * @param MyCollection<MyCollection<string,MyClassC>, MyCollection<MyClassC,MyClassC>> $dddd
     *
     * @return void
     */
    public function method(int $a, string $b, MyClassC $c,
        $d,
        $dd,
        $ddd,
        $dddd
    )
    {
    }
}
