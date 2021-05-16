<?php

namespace Gzhegow\Reflection;

/**
 * ReflectionInfo
 */
class ReflectionInfo
{
    /**
     * @var object
     */
    public $object;

    /**
     * @var string
     */
    public $class;

    /**
     * @var ReflectionClass
     */
    public $reflectionClass;


    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
            'object' => $this->object,
            'class'  => $this->class,

            'reflectionClass' => $this->reflectionClass,
        ];
    }
}
