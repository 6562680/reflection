<?php

namespace Gzhegow\Reflection;

use Gzhegow\Support\Filter;


/**
 * Assert
 */
class Assert
{
    /**
     * @var Filter
     */
    protected $filter;


    /**
     * Constructor
     *
     * @param Filter $filter
     */
    public function __construct(
        Filter $filter
    )
    {
        $this->filter = $filter;
    }


    /**
     * @param mixed $reflectionClass
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionClass($reflectionClass) : ?\ReflectionClass
    {
        if (is_object($reflectionClass)
            && is_a($reflectionClass, \ReflectionClass::class)
        ) {
            return $reflectionClass;
        }

        return null;
    }


    /**
     * @param mixed $reflectionFunction
     *
     * @return null|\ReflectionFunction
     */
    public function filterReflectionFunction($reflectionFunction) : ?\ReflectionFunction
    {
        if (is_object($reflectionFunction)
            && is_a($reflectionFunction, \ReflectionFunction::class)
        ) {
            return $reflectionFunction;
        }

        return null;
    }

    /**
     * @param mixed $reflectionMethod
     *
     * @return null|\ReflectionMethod
     */
    public function filterReflectionMethod($reflectionMethod) : ?\ReflectionMethod
    {
        if (is_object($reflectionMethod)
            && is_a($reflectionMethod, \ReflectionMethod::class)
        ) {
            return $reflectionMethod;
        }

        return null;
    }


    /**
     * @param mixed $reflectionProperty
     *
     * @return null|\ReflectionProperty
     */
    public function filterReflectionProperty($reflectionProperty) : ?\ReflectionProperty
    {
        if (is_object($reflectionProperty)
            && is_a($reflectionProperty, \ReflectionProperty::class)
        ) {
            return $reflectionProperty;
        }

        return null;
    }

    /**
     * @param mixed $reflectionParameter
     *
     * @return null|\ReflectionParameter
     */
    public function filterReflectionParameter($reflectionParameter) : ?\ReflectionParameter
    {
        if (is_object($reflectionParameter)
            && is_a($reflectionParameter, \ReflectionParameter::class)
        ) {
            return $reflectionParameter;
        }

        return null;
    }


    /**
     * @param mixed $reflectionType
     *
     * @return null|\ReflectionType
     */
    public function filterReflectionType($reflectionType) : ?\ReflectionType
    {
        if (is_object($reflectionType)
            && is_a($reflectionType, \ReflectionType::class)
        ) {
            return $reflectionType;
        }

        return null;
    }

    /**
     * @param mixed $reflectionType
     *
     * @return null|\ReflectionUnionType
     */
    public function filterReflectionUnionType($reflectionType) // : ?\ReflectionUnionType
    {
        if (is_object($reflectionType)
            && class_exists('ReflectionUnionType')
            && is_a($reflectionType, 'ReflectionUnionType')
        ) {
            return $reflectionType;
        }

        return null;
    }

    /**
     * @param mixed $reflectionType
     *
     * @return null|\ReflectionNamedType
     */
    public function filterReflectionNamedType($reflectionType) : ?\ReflectionNamedType
    {
        if (is_object($reflectionType)
            && is_a($reflectionType, \ReflectionNamedType::class)
        ) {
            return $reflectionType;
        }

        return null;
    }


    /**
     * @param mixed $reflectable
     *
     * @return null|string|object|\ReflectionClass
     */
    public function filterReflectable($reflectable) //: ?string|object|\ReflectionClass
    {
        $reflectable = null
            ?? $this->filterReflectableObject($reflectable)
            ?? $this->filterReflectableString($reflectable);

        return $reflectable;
    }


    /**
     * @param mixed $reflectable
     *
     * @return null|object|\ReflectionClass
     */
    public function filterReflectableObject($reflectable) //: ?object|\ReflectionClass
    {
        $reflectable = null
            ?? $this->filterReflectionClass($reflectable)
            ?? $this->filterReflectableInstance($reflectable);

        return $reflectable;
    }

    /**
     * @param mixed $reflectable
     *
     * @return null|object
     */
    public function filterReflectableInstance($reflectable) //: ?object
    {
        if (! is_object($reflectable)) {
            return null;
        }

        if ($isReflector = is_a($reflectable, \Reflector::class)) {
            return null;
        }

        return $reflectable;
    }


    /**
     * @param mixed $reflectable
     *
     * @return null|string|object|\ReflectionClass
     */
    public function filterReflectableString($reflectable) //: ?string|object|\ReflectionClass
    {
        $reflectable = null
            ?? $this->filterReflectableClass($reflectable)
            ?? $this->filterReflectableInterface($reflectable)
            ?? $this->filterReflectableTrait($reflectable);

        return $reflectable;
    }


    /**
     * @param mixed $reflectable
     *
     * @return null|string
     */
    public function filterReflectableClass($reflectable) : ?string
    {
        if (! is_string($reflectable)) {
            return null;
        }

        if ($isReflector = is_a($reflectable, \Reflector::class, true)) {
            return null;
        }

        if (! class_exists($reflectable)) {
            return null;
        }

        return $reflectable;
    }

    /**
     * @param mixed $reflectable
     *
     * @return null|string
     */
    public function filterReflectableInterface($reflectable) : ?string
    {
        if (! is_string($reflectable)) {
            return null;
        }

        if (! interface_exists($reflectable)) {
            return null;
        }

        return $reflectable;
    }

    /**
     * @param mixed $reflectable
     *
     * @return null|string
     */
    public function filterReflectableTrait($reflectable) : ?string
    {
        if (! is_string($reflectable)) {
            return null;
        }

        if (! trait_exists($reflectable)) {
            return null;
        }

        return $reflectable;
    }


    /**
     * @param mixed $reflectableInvokable
     *
     * @return null|string|array|\Closure|\ReflectionFunction|\ReflectionMethod
     */
    public function filterReflectableInvokable($reflectableInvokable) //: ?string|array|\Closure|\ReflectionFunction|\ReflectionMethod
    {
        $reflectableInvokable = null
            ?? $this->filterReflectionFunction($reflectableInvokable)
            ?? $this->filterReflectionMethod($reflectableInvokable)
            ?? $this->filter->filterClosure($reflectableInvokable)
            ?? $this->filter->filterCallableArray($reflectableInvokable)
            ?? $this->filter->filterCallableString($reflectableInvokable)
            ?? $this->filter->filterHandler($reflectableInvokable);

        return $reflectableInvokable;
    }

    /**
     * @param mixed $reflectableCallable
     *
     * @return null|string|array|\Closure|\ReflectionFunction|\ReflectionMethod
     */
    public function filterReflectableCallable($reflectableCallable) //: ?string|array|\Closure|\ReflectionFunction|\ReflectionMethod
    {
        $reflectableCallable = null
            ?? $this->filterReflectionFunction($reflectableCallable)
            ?? $this->filterReflectionMethod($reflectableCallable)
            ?? $this->filter->filterClosure($reflectableCallable)
            ?? $this->filter->filterCallableArray($reflectableCallable)
            ?? $this->filter->filterCallableStringFunction($reflectableCallable)
            ?? $this->filter->filterCallableStringStatic($reflectableCallable)//
            // ?? $this->filter->filterHandler($reflectableCallable)
        ;

        return $reflectableCallable;
    }


    /**
     * @param mixed $reflectableFunction
     *
     * @return null|string|\Closure|\ReflectionFunction
     */
    public function filterReflectableFunction($reflectableFunction) //: ?string|\Closure|\ReflectionFunction
    {
        $reflectableFunction = null
            ?? $this->filterReflectionFunction($reflectableFunction)
            ?? $this->filter->filterClosure($reflectableFunction)
            ?? $this->filter->filterCallableStringFunction($reflectableFunction);

        return $reflectableFunction;
    }

    /**
     * @param mixed $reflectableMethod
     *
     * @return null|string|array|\ReflectionMethod
     */
    public function filterReflectableMethod($reflectableMethod) //: ?string|array|\ReflectionMethod
    {
        $reflectableMethod = null
            ?? $this->filterReflectionMethod($reflectableMethod)
            ?? $this->filter->filterMethodArray($reflectableMethod)
            ?? $this->filter->filterCallableStringStatic($reflectableMethod)
            ?? $this->filter->filterHandler($reflectableMethod);

        return $reflectableMethod;
    }


    /**
     * @param mixed $reflectableMethodStatic
     *
     * @return null|string|array|\ReflectionMethod
     */
    public function filterReflectableMethodStatic($reflectableMethodStatic) //: ?string|array|\ReflectionMethod
    {
        $reflectableMethodStatic = null
            ?? $this->filterReflectionMethod($reflectableMethodStatic)
            ?? $this->filter->filterCallableArrayStatic($reflectableMethodStatic)
            ?? $this->filter->filterCallableStringStatic($reflectableMethodStatic);

        return $reflectableMethodStatic;
    }

    /**
     * @param mixed $reflectableMethodPublic
     *
     * @return null|string|array|\ReflectionMethod
     */
    public function filterReflectableMethodPublic($reflectableMethodPublic) //: ?string|array|\ReflectionMethod
    {
        $reflectableMethodPublic = null
            ?? $this->filterReflectionMethod($reflectableMethodPublic)
            ?? $this->filter->filterCallableArrayPublic($reflectableMethodPublic)
            ?? $this->filter->filterHandler($reflectableMethodPublic);

        return $reflectableMethodPublic;
    }
}
