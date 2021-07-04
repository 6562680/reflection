<?php


namespace Gzhegow\Reflection\Models\ValueObjects;


/**
 * TypeValueVal
 */
abstract class AbstractTypeVal
{
    /**
     * @var string
     */
    protected $name;


    /**
     * @return array
     */
    public function toArray() : array
    {
        return get_object_vars($this);
    }


    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
