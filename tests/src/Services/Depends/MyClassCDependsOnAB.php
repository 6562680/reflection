<?php

namespace Gzhegow\Reflection\Tests\Services\Depends;

use Gzhegow\Reflection\Tests\Services\MyClassB;
use Gzhegow\Reflection\Tests\Services\MyClassA;

class MyClassCDependsOnAB
{
    /**
     * @var MyClassA
     */
    protected $a;
    /**
     * @var MyClassB
     */
    protected $b;


    public function __construct(
        MyClassA $a,
        MyClassB $b
    )
    {
        $this->a = $a;
        $this->b = $b;
    }
}
