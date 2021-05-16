<?php

namespace Gzhegow\Reflection;

/**
 * ReflectionPropertyInfo
 */
class ReflectionPropertyInfo extends ReflectionInfo
{
    /**
     * @var string
     */
    public $property;

    /**
     * @var \ReflectionProperty
     */
    public $reflectionProperty;


    /**
     * @return array
     */
    public function toArray() : array
    {
        return array_merge(parent::toArray(), [
            'method'             => $this->property,
            'reflectionProperty' => $this->reflectionProperty,
        ]);
    }
}
