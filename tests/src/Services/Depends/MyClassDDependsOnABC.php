<?php

namespace Gzhegow\Reflection\Tests\Services\Depends;

use Gzhegow\Reflection\Tests\Services\MyClassB;
use Gzhegow\Reflection\Tests\Services\MyClassA;
use Gzhegow\Reflection\Tests\Services\MyClassC;


class MyClassDDependsOnABC
{
    /**
     * @var MyClassA
     */
    protected $a;
    /**
     * @var MyClassB
     */
    protected $b;
    /**
     * @var MyClassB
     */
    protected $c;


    /**
     * Constructor
     *
     * @param MyClassA $a
     * @param MyClassB $b
     * @param MyClassC $c
     */
    public function __construct(
        MyClassA $a,
        MyClassB $b,
        MyClassC $c
    )
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}
