<?php

namespace Gzhegow\Reflection;

/**
 * ReflectionFunctionInfo
 */
class ReflectionFunctionInfo extends ReflectionInfo
{
    /**
     * @var string
     */
    public $function;

    /**
     * @var \ReflectionFunction
     */
    public $reflectionFunction;


    /**
     * @return array
     */
    public function toArray() : array
    {
        return array_merge(parent::toArray(), [
            'function'           => $this->function,
            'reflectionFunction' => $this->reflectionFunction,
        ]);
    }
}
