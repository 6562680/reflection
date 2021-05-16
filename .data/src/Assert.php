<?php

namespace Gzhegow\Reflection;

use Gzhegow\Support\Php;
use Gzhegow\Support\Str;
use Gzhegow\Support\Filter;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;
use Gzhegow\Reflection\Exceptions\Runtime\ReflectionRuntimeException;


/**
 * Assert
 */
class Assert implements ReflectionInterface
{
    /**
     * @var Filter
     */
    protected $filter;


    /**
     * Constructor
     *
     * @param Filter $filter
     * @param Str    $str
     * @param Php    $php
     */
    public function __construct(
        Filter $filter
    )
    {
        $this->filter = $filter;
    }


    /**
     * @param \ReflectionClass         $reflection
     * @param null|ReflectionClassInfo $reflectionInfo
     *
     * @return bool
     */
    public function isReflectionClass($reflection, ReflectionClassInfo &$reflectionInfo = null) : bool
    {
        return null !== $this->filterReflectionClass($reflection, $reflectionInfo);
    }


    /**
     * @param \ReflectionClass          $reflection
     * @param null|ReflectionMethodInfo $reflectionInfo
     *
     * @return bool
     */
    public function isReflectionMethod($reflection, ReflectionMethodInfo &$reflectionInfo = null) : bool
    {
        return null !== $this->filterReflectionMethod($reflection, $reflectionInfo);
    }

    /**
     * @param \ReflectionClass            $reflection
     * @param null|ReflectionFunctionInfo $reflectionInfo
     *
     * @return bool
     */
    public function isReflectionFunction($reflection, ReflectionFunctionInfo &$reflectionInfo = null) : bool
    {
        return null !== $this->filterReflectionFunction($reflection, $reflectionInfo);
    }


    /**
     * @param \ReflectionClass            $reflection
     * @param null|ReflectionPropertyInfo $reflectionInfo
     *
     * @return bool
     */
    public function isReflectionProperty($reflection, ReflectionPropertyInfo &$reflectionInfo = null) : bool
    {
        return null !== $this->filterReflectionProperty($reflection, $reflectionInfo);
    }

    /**
     * @param \ReflectionClass             $reflection
     * @param null|ReflectionParameterInfo $reflectionInfo
     *
     * @return bool
     */
    public function isReflectionParameter($reflection, ReflectionParameterInfo &$reflectionInfo = null) : bool
    {
        return null !== $this->filterReflectionParameter($reflection, $reflectionInfo);
    }



    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectable($reflectable) : bool
    {
        if (0
            || $this->isReflectableObject($reflectable)
            || $this->isReflectableClass($reflectable)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableObject($reflectable) : bool
    {
        if (0
            || $this->isReflectionClass($reflectable)
            || $this->isReflectableClassInstance($reflectable)
        ) {
            return true;
        }

        return false;
    }


    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableClass($reflectable) : bool
    {
        if (! is_string($reflectable)) {
            return false;
        }

        if ($isReflectionClassItself = ( $reflectable === \ReflectionClass::class )) {
            return false;
        }

        if (! class_exists($reflectable)) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableClassInstance($reflectable) : bool
    {
        if (! is_object($reflectable)) {
            return false;
        }

        if ($isReflectionClassItself = ( $reflectable instanceof \ReflectionClass )) {
            return false;
        }

        return true;
    }


    /**
     * @param \ReflectionClass         $reflection
     * @param null|ReflectionClassInfo $info
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionClass($reflection, ReflectionClassInfo &$info = null) : ?\ReflectionClass
    {
        $info = $info ?? new ReflectionClassInfo();

        if (is_object($reflection) && is_a($reflection, \ReflectionClass::class)
        ) {
            $info->class = $reflection->getName();
            $info->reflectionClass = $reflection;

            return $info->reflectionClass;
        }

        return null;
    }


    /**
     * @param \ReflectionClass          $reflection
     * @param null|ReflectionMethodInfo $info
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionMethod($reflection, ReflectionMethodInfo &$info = null) : ?\ReflectionMethod
    {
        $info = $info ?? new ReflectionMethodInfo();

        if (is_object($reflection) && is_a($reflection, \ReflectionMethod::class)) {
            $info->class = $reflection->getDeclaringClass()->getName();
            $info->reflectionClass = $reflection->getDeclaringClass();
            $info->method = $reflection->getName();
            $info->reflectionMethod = $reflection;

            return $info->reflectionMethod;
        }

        return null;
    }

    /**
     * @param \ReflectionClass            $reflection
     * @param null|ReflectionFunctionInfo $info
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionFunction($reflection, ReflectionFunctionInfo &$info = null) : ?\ReflectionFunction
    {
        $info = $info ?? new ReflectionFunctionInfo();

        if (is_object($reflection) && is_a($reflection, \ReflectionFunction::class)) {
            $info->function = $reflection->getName();
            $info->reflectionFunction = $reflection;

            return $info->reflectionFunction;
        }

        return null;
    }


    /**
     * @param \ReflectionClass            $reflection
     * @param null|ReflectionPropertyInfo $info
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionProperty($reflection, ReflectionPropertyInfo &$info = null) : ?\ReflectionProperty
    {
        $info = $info ?? new ReflectionPropertyInfo();

        if (is_object($reflection) && is_a($reflection, \ReflectionProperty::class)
        ) {
            $info->class = $reflection->getDeclaringClass()->getName();
            $info->reflectionClass = $reflection->getDeclaringClass();
            $info->property = $reflection->getName();
            $info->reflectionProperty = $reflection;

            return $info->reflectionClass;
        }

        return null;
    }

    /**
     * @param \ReflectionClass             $reflection
     * @param null|ReflectionParameterInfo $info
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionParameter($reflection, ReflectionParameterInfo &$info = null) : ?\ReflectionParameter
    {
        $info = $info ?? new ReflectionParameterInfo();

        if (is_object($reflection) && is_a($reflection, \ReflectionParameter::class)) {
            $info->class = $reflection->getDeclaringClass()->getName();
            $info->reflectionClass = $reflection;
            $info->parameter = $reflection->getName();
            $info->reflectionParameter = $reflection;

            return $info->reflectionClass;
        }

        return null;
    }
}
