<?php

namespace Gzhegow\Reflection;

/**
 * ReflectionParameterInfo
 */
class ReflectionParameterInfo extends ReflectionInfo
{
    /**
     * @var string
     */
    public $parameter;

    /**
     * @var \ReflectionParameter
     */
    public $reflectionParameter;


    /**
     * @return array
     */
    public function toArray() : array
    {
        return array_merge(parent::toArray(), [
            'parameter'           => $this->parameter,
            'reflectionParameter' => $this->reflectionParameter,
        ]);
    }
}
