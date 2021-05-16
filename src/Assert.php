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
     * @return bool
     */
    public function isReflectionClass($reflectionClass) : bool
    {
        return null !== $this->filterReflectionClass($reflectionClass);
    }


    /**
     * @param mixed $reflectionFunction
     *
     * @return bool
     */
    public function isReflectionFunction($reflectionFunction) : bool
    {
        return null !== $this->filterReflectionFunction($reflectionFunction);
    }

    /**
     * @param mixed $reflectionMethod
     *
     * @return bool
     */
    public function isReflectionMethod($reflectionMethod) : bool
    {
        return null !== $this->filterReflectionMethod($reflectionMethod);
    }


    /**
     * @param mixed $reflectionProperty
     *
     * @return bool
     */
    public function isReflectionProperty($reflectionProperty) : bool
    {
        return null !== $this->filterReflectionProperty($reflectionProperty);
    }

    /**
     * @param mixed $reflectionParameter
     *
     * @return bool
     */
    public function isReflectionParameter($reflectionParameter) : bool
    {
        return null !== $this->filterReflectionParameter($reflectionParameter);
    }


    /**
     * @param mixed $reflectionType
     *
     * @return bool
     */
    public function isReflectionType($reflectionType) : bool
    {
        return null !== $this->filterReflectionType($reflectionType);
    }

    /**
     * @param mixed $reflectionType
     *
     * @return bool
     */
    public function isReflectionUnionType($reflectionType) : bool
    {
        return null !== $this->filterReflectionUnionType($reflectionType);
    }

    /**
     * @param mixed $reflectionType
     *
     * @return bool
     */
    public function isReflectionNamedType($reflectionType) : bool
    {
        return null !== $this->filterReflectionNamedType($reflectionType);
    }


    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectable($reflectable) : bool
    {
        return null !== $this->filterReflectableObject($reflectable);
    }

    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableObject($reflectable) : bool
    {
        return null !== $this->filterReflectableObject($reflectable);
    }


    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableClass($reflectable) : bool
    {
        return null !== $this->filterReflectableClass($reflectable);
    }

    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableInstance($reflectable) : bool
    {
        return null !== $this->filterReflectableInstance($reflectable);
    }


    /**
     * @param mixed $reflectableCallable
     *
     * @return bool
     */
    public function isReflectableCallable($reflectableCallable) : bool
    {
        return null !== $this->filterReflectableCallable($reflectableCallable);
    }


    /**
     * @param mixed $reflectableCallable
     *
     * @return bool
     */
    public function isReflectableFunction($reflectableCallable) : bool
    {
        return null !== $this->filterReflectableFunction($reflectableCallable);
    }

    /**
     * @param mixed $reflectableCallable
     *
     * @return bool
     */
    public function isReflectableMethod($reflectableCallable) : bool
    {
        return null !== $this->filterReflectableMethod($reflectableCallable);
    }


    /**
     * @param mixed $reflectableCallable
     *
     * @return bool
     */
    public function isReflectableMethodStatic($reflectableCallable) : bool
    {
        return null !== $this->filterReflectableMethodStatic($reflectableCallable);
    }

    /**
     * @param mixed $reflectableCallable
     *
     * @return bool
     */
    public function isReflectableMethodPublic($reflectableCallable) : bool
    {
        return null !== $this->filterReflectableMethodPublic($reflectableCallable);
    }


    /**
     * @param mixed $reflectionClass
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionClass($reflectionClass) : ?\ReflectionClass
    {
        if (is_object($reflectionClass) && is_a($reflectionClass, \ReflectionClass::class)) {
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
        if (is_object($reflectionFunction) && is_a($reflectionFunction, \ReflectionFunction::class)) {
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
        if (is_object($reflectionMethod) && is_a($reflectionMethod, \ReflectionMethod::class)) {
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
        if (is_object($reflectionProperty) && is_a($reflectionProperty, \ReflectionProperty::class)) {
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
        if (is_object($reflectionParameter) && is_a($reflectionParameter, \ReflectionParameter::class)) {
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
        if (is_object($reflectionType) && is_a($reflectionType, \ReflectionType::class)) {
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
        if (is_object($reflectionType) && is_a($reflectionType, \ReflectionNamedType::class)) {
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
            ?? $this->filterReflectableClass($reflectable);

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
     * @param mixed $reflectableCallable
     *
     * @return null|string|array|\Closure|\ReflectionFunction|\ReflectionMethod
     */
    public function filterReflectableCallable($reflectableCallable) //: ?string|array|\Closure|\ReflectionFunction|\ReflectionMethod
    {
        $reflectableCallable = null
            ?? $this->filterReflectionFunction($reflectableCallable)
            ?? $this->filter->filterClosure($reflectableCallable)
            ?? $this->filter->filterCallable($reflectableCallable);

        return $reflectableCallable;
    }


    /**
     * @param mixed $reflectableCallable
     *
     * @return null|string|\Closure|\ReflectionFunction
     */
    public function filterReflectableFunction($reflectableCallable) //: ?string|\Closure|\ReflectionFunction
    {
        $reflectableCallable = null
            ?? $this->filterReflectionFunction($reflectableCallable)
            ?? $this->filter->filterClosure($reflectableCallable)
            ?? $this->filter->filterCallableStringFunction($reflectableCallable);

        return $reflectableCallable;
    }

    /**
     * @param mixed $reflectableCallable
     *
     * @return null|string|array|\ReflectionMethod
     */
    public function filterReflectableMethod($reflectableCallable) //: ?string|array|\ReflectionMethod
    {
        $reflectableCallable = null
            ?? $this->filterReflectionMethod($reflectableCallable)
            ?? $this->filter->filterCallableArray($reflectableCallable)
            ?? $this->filter->filterCallableStringStatic($reflectableCallable);

        return $reflectableCallable;
    }


    /**
     * @param mixed $reflectableCallable
     *
     * @return null|string|array|\ReflectionMethod
     */
    public function filterReflectableMethodStatic($reflectableCallable) //: ?string|array|\ReflectionMethod
    {
        $reflectableCallable = null
            ?? $this->filterReflectionMethod($reflectableCallable)
            ?? $this->filter->filterCallableArrayStatic($reflectableCallable)
            ?? $this->filter->filterCallableStringStatic($reflectableCallable);

        return $reflectableCallable;
    }

    /**
     * @param mixed $reflectableCallable
     *
     * @return null|string|array|\ReflectionMethod
     */
    public function filterReflectableMethodPublic($reflectableCallable) //: ?string|array|\ReflectionMethod
    {
        $reflectableCallable = null
            ?? $this->filterReflectionMethod($reflectableCallable)
            ?? $this->filter->filterCallableArrayPublic($reflectableCallable);

        return $reflectableCallable;
    }
}
