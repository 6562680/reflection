<?php

namespace Gzhegow\Reflection;

/**
 * ReflectionMethodInfo
 */
class ReflectionMethodInfo extends ReflectionInfo
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var \ReflectionMethod
     */
    public $reflectionMethod;


    /**
     * @return array
     */
    public function toArray() : array
    {
        return array_merge(parent::toArray(), [
            'method'           => $this->method,
            'reflectionMethod' => $this->reflectionMethod,
        ]);
    }
}
