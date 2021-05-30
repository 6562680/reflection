<?php

namespace Gzhegow\Reflection\Domain;

use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;


/**
 * ReflectorInfo
 */
class ReflectorInfo
{
    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;
    /**
     * @var string
     */
    protected $class;

    /**
     * @var null|object
     */
    protected $object;


    /**
     * @return \ReflectionClass
     */
    public function getReflectionClass() : \ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * @return string
     */
    public function getClass() : string
    {
        return $this->class;
    }


    /**
     * @return object
     */
    public function getObject() // : ?object
    {
        return $this->object;
    }


    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return static
     */
    public function setReflectionClass(\ReflectionClass $reflectionClass)
    {
        $this->reflectionClass = $reflectionClass;

        return $this;
    }

    /**
     * @param string $class
     *
     * @return static
     */
    public function setClass(string $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param null|object $object
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function setObject($object)
    {
        if (! is_object($object)) {
            throw new InvalidArgumentException('Invalid object passed', func_get_args());
        }

        $this->object = $object;

        return $this;
    }


    /**
     * @param ReflectorInfo $info
     *
     * @return static
     */
    public function sync(self $info)
    {
        $this->reflectionClass = $info->reflectionClass;
        $this->class = $info->class;
        $this->object = $info->object;

        return $this;
    }
}
