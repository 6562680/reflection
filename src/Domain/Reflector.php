<?php

namespace Gzhegow\Reflection\Domain;

use Gzhegow\Support\Php;
use Gzhegow\Support\Filter;
use Gzhegow\Reflection\Assert;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;
use Gzhegow\Reflection\Exceptions\Runtime\ReflectionRuntimeException;


/**
 * Reflector
 */
class Reflector
{
    const TAG_ABSTRACT  = 'abstract';
    const TAG_DECLARED  = 'declared';
    const TAG_DEFAULT   = 'default';
    const TAG_FINAL     = 'final';
    const TAG_PRESENT   = 'present';
    const TAG_PRIVATE   = 'private';
    const TAG_PROTECTED = 'protected';
    const TAG_PUBLIC    = 'public';
    const TAG_STATIC    = 'static';
    const TAG_TRAIT     = 'trait';

    const THE_TAG_LIST = [
        self::TAG_ABSTRACT  => true,
        self::TAG_DECLARED  => true,
        self::TAG_DEFAULT   => true,
        self::TAG_FINAL     => true,
        self::TAG_PRESENT   => true,
        self::TAG_PRIVATE   => true,
        self::TAG_PROTECTED => true,
        self::TAG_PUBLIC    => true,
        self::TAG_STATIC    => true,
        self::TAG_TRAIT     => true,
    ];


    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var Php
     */
    protected $php;

    /**
     * @var Assert
     */
    protected $assert;


    /**
     * Constructor
     *
     * @param Filter $filter
     * @param Php    $php
     * @param Assert $assert
     */
    public function __construct(
        Filter $filter,
        Php $php,

        Assert $assert
    )
    {
        $this->filter = $filter;
        $this->php = $php;

        $this->assert = $assert;
    }


    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param null|ReflectorInfo             $info
     *
     * @return \ReflectionClass
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionClass($reflectable, ReflectorInfo &$info = null) : \ReflectionClass
    {
        $info = $info
            ?? $this->newReflectorInfoFromReflectable($reflectable);

        $reflectionClass = $this->newReflectionClass($reflectable);

        $info->setReflectionClass($reflectionClass);

        return $reflectionClass;
    }


    /**
     * @param string|array|\ReflectionMethod $reflectableMethod
     * @param null|ReflectorInfo             $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionMethod($reflectableMethod, ReflectorInfo &$info = null) : \ReflectionMethod
    {
        $methodArray = $this->newMethodArrayFromReflectableInvokable($reflectableMethod);

        $info = $info
            ?? $this->newReflectorInfoFromReflectableMethod($methodArray);

        $reflectionMethod = $this->newReflectionMethod($methodArray[ 0 ], $methodArray[ 1 ]);

        $info->setReflectionClass($reflectionMethod->getDeclaringClass());

        return $reflectionMethod;
    }


    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $propertyName
     * @param null|ReflectorInfo             $info
     *
     * @return \ReflectionProperty
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionProperty($reflectable, string $propertyName, ReflectorInfo &$info = null) : \ReflectionProperty
    {
        if (null === $this->filter->filterWord($propertyName)) {
            throw new InvalidArgumentException(
                [ 'PropertyName should be non-empty string: %s', $propertyName ]
            );
        }

        $info = $info
            ?? $this->newReflectorInfoFromReflectable($reflectable);

        if (null !== ( $reflectionClass = $this->assert->filterReflectionClass($reflectable) )) {
            try {
                $reflectionProperty = $reflectionClass->getProperty($propertyName);
            }
            catch ( \ReflectionException $e ) {
                throw new ReflectionRuntimeException(
                    [ 'Unable to get property: %s %s', $reflectionClass, $propertyName ], null, $e
                );
            }
        } else {
            $reflectionProperty = $this->newReflectionProperty($reflectable, $propertyName);
        }

        return $reflectionProperty;
    }

    /**
     * @param string|array|\ReflectionFunction|\ReflectionMethod $reflectableInvokable
     * @param int|string                                         $parameter
     * @param null|ReflectorInfo                                 $info
     *
     * @return \ReflectionParameter
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionParameter($reflectableInvokable, $parameter, ReflectorInfo &$info = null) : \ReflectionParameter
    {
        if (null === $this->filter->filterWordOrInt($parameter)) {
            throw new InvalidArgumentException(
                [ 'Parameter should be int or non-empty string: %s', $parameter ]
            );
        }

        $methodArray = $this->newMethodArrayFromReflectableInvokable($reflectableInvokable);

        $hasInfo = ( null !== $info );

        if (! $hasInfo
            && ( null !== $this->assert->filterReflectableMethod($reflectableInvokable) )
        ) {
            $info = $this->newReflectorInfoFromReflectableMethod($reflectableInvokable);

            $hasInfo = true;
        }

        $reflectionParameter = $this->newReflectionParameter($methodArray, $parameter);

        if ($hasInfo) {
            if ($reflectionClass = $reflectionParameter->getDeclaringClass()) {
                $info->setReflectionClass($reflectionClass);
            }
        }

        return $reflectionParameter;
    }


    /**
     * @param string|object $objectOrClass
     *
     * @return \ReflectionClass
     * @throws ReflectionRuntimeException
     */
    public function newReflectionClass($objectOrClass) : \ReflectionClass
    {
        try {
            $result = new \ReflectionClass($objectOrClass);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException(
                [ 'Unable to reflect class: %s', $objectOrClass ], null, $e
            );
        }

        return $result;
    }


    /**
     * @param string|\Closure $function
     *
     * @return \ReflectionFunction
     * @throws ReflectionRuntimeException
     */
    public function newReflectionFunction($function) : ?\ReflectionFunction
    {
        try {
            $reflection = new \ReflectionFunction($function);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException(
                [ 'Unable to reflect function: %s', $function ], null, $e
            );
        }

        return $reflection;
    }

    /**
     * @param string|object $objectOrMethod
     * @param string|null   $method
     *
     * @return \ReflectionMethod
     * @throws ReflectionRuntimeException
     */
    public function newReflectionMethod($objectOrMethod, $method = null) : \ReflectionMethod
    {
        try {
            $reflection = new \ReflectionMethod($objectOrMethod, $method);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException(
                [ 'Unable to reflect method: %s %s', $objectOrMethod, $method ], null, $e
            );
        }

        return $reflection;
    }


    /**
     * @param string|object $class
     * @param string        $property
     *
     * @return \ReflectionProperty
     * @throws ReflectionRuntimeException
     */
    public function newReflectionProperty($class, $property) : ?\ReflectionProperty
    {
        try {
            $reflection = new \ReflectionProperty($class, $property);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException(
                [ 'Unable to reflect property: %s %s', $class, $property ], null, $e
            );
        }

        return $reflection;
    }

    /**
     * @param string|array|\Closure|callable $function
     * @param string|int                     $param
     *
     * @return \ReflectionParameter
     * @throws ReflectionRuntimeException
     */
    public function newReflectionParameter($function, $param) : ?\ReflectionParameter
    {
        try {
            $reflection = new \ReflectionParameter($function, $param);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException(
                [ 'Unable to reflect parameter: %s %s', $function, $param ], null, $e
            );
        }

        return $reflection;
    }


    /**
     * @param string|array|callable|\ReflectionMethod $reflectableMethod
     *
     * @return null|array
     * @throws InvalidArgumentException
     */
    public function newCallableArrayFromReflectableInvokable($reflectableMethod) : array
    {
        $result = null;

        if (null !== ( $callableArray = $this->filter->filterCallableArray($reflectableMethod) )) {
            $result = $callableArray;

        } elseif (null !== ( $callableStringStatic = $this->filter->filterCallableStringStatic($reflectableMethod) )) {
            $result = explode('::', $callableStringStatic, 2);

        } elseif (null !== ( $handler = $this->filter->filterHandler($reflectableMethod) )) {
            $result = explode('@', $handler, 2);

        } else {
            throw new InvalidArgumentException(
                [ 'ReflectableMethod should be callable array, callable string static or handler: %s', $reflectableMethod ]
            );
        }

        return $result;
    }

    /**
     * @param string|array|callable|\ReflectionMethod $reflectableMethod
     *
     * @return null|array
     * @throws InvalidArgumentException
     */
    public function newMethodArrayFromReflectableInvokable($reflectableMethod) : array
    {
        $result = null;

        if (null !== ( $methodArray = $this->filter->filterMethodArray($reflectableMethod) )) {
            $result = $methodArray;

        } elseif (null !== ( $callableStringStatic = $this->filter->filterCallableStringStatic($reflectableMethod) )) {
            $result = explode('::', $callableStringStatic, 2);

        } elseif (null !== ( $handler = $this->filter->filterHandler($reflectableMethod) )) {
            $result = explode('@', $handler, 2);

        } else {
            throw new InvalidArgumentException(
                [ 'ReflectableMethod should be method array, callable string static or handler: %s', $reflectableMethod ]
            );
        }

        return $result;
    }


    /**
     * @param \ReflectionClass|\ReflectionMethod|\ReflectionProperty|\ReflectionParameter $reflection
     *
     * @return ReflectorInfo
     * @throws InvalidArgumentException
     */
    public function newReflectorInfoFromReflection($reflection) : ReflectorInfo
    {
        $info = new ReflectorInfo();

        if (null !== ( $reflectionClass = $this->assert->filterReflectionClass($reflection) )) {
            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== ( $reflectionMethod = $this->assert->filterReflectionMethod($reflection) )) {
            $reflectionClass = $reflectionMethod->getDeclaringClass();

            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== ( $reflectionProperty = $this->assert->filterReflectionProperty($reflection) )) {
            $reflectionClass = $reflectionProperty->getDeclaringClass();

            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== ( $reflectionParameter = $this->assert->filterReflectionParameter($reflection) )) {
            $reflectionClass = $reflectionParameter->getDeclaringClass();

            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } else {
            throw new InvalidArgumentException([
                'Reflection should be one of [ %s, %s, %s ]: %s',
                \ReflectionClass::class,
                \ReflectionMethod::class,
                \ReflectionProperty::class,
                \ReflectionParameter::class,
                $reflection,
            ]);
        }

        return $info;
    }

    /**
     * @param string|object|\ReflectionClass $reflectable
     *
     * @return ReflectorInfo
     * @throws InvalidArgumentException
     */
    public function newReflectorInfoFromReflectable($reflectable) : ReflectorInfo
    {
        $info = new ReflectorInfo();

        if (null !== ( $reflectionClass = $this->assert->filterReflectionClass($reflectable) )) {
            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== $this->assert->filterReflectableInstance($reflectable)) {
            $info->setObject($reflectable);
            $info->setClass(get_class($reflectable));

        } elseif (null !== $this->assert->filterReflectableClass($reflectable)) {
            $info->setClass($reflectable);

        } elseif (null !== $this->assert->filterReflectableTrait($reflectable)) {
            $info->setClass($reflectable);

        } else {
            throw new InvalidArgumentException(
                [ 'Reflectable should be object, class or trait: %s', $reflectable ]
            );
        }

        return $info;
    }

    /**
     * @param string|array|\ReflectionMethod $reflectableMethod
     *
     * @return ReflectorInfo
     * @throws InvalidArgumentException
     */
    public function newReflectorInfoFromReflectableMethod($reflectableMethod) : ReflectorInfo
    {
        [ $reflectable ] = $this->newMethodArrayFromReflectableInvokable($reflectableMethod);

        $info = $this->newReflectorInfoFromReflectable($reflectable);

        return $info;
    }


    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param null|ReflectorInfo             $info
     *
     * @return null|\ReflectionClass
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectClass($reflectable, ReflectorInfo &$info = null) : ?\ReflectionClass
    {
        $result = null;

        if (null !== ( $reflection = $this->assert->filterReflectionClass($reflectable) )) {
            $newInfo = $this->newReflectorInfoFromReflection($reflection);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflection;

        } elseif (null !== $this->assert->filterReflectable($reflectable)) {
            $result = $this->buildReflectionClass($reflectable, $info);

        }

        return $result;
    }


    /**
     * @param string|\ReflectionClass $reflectable
     * @param null|ReflectorInfo      $info
     *
     * @return null|\ReflectionClass
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectTheClass($reflectable, ReflectorInfo &$info = null) : ?\ReflectionClass
    {
        $result = null;

        if (null !== ( $reflection = $this->assert->filterReflectionClass($reflectable) )) {
            if (! ( $reflection->isInterface() || $reflection->isTrait() )) {
                $newInfo = $this->newReflectorInfoFromReflection($reflection);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflection;
            }
        } elseif (null !== $this->assert->filterReflectableClass($reflectable)) {
            $result = $this->buildReflectionClass($reflectable, $info);

        }

        return $result;
    }

    /**
     * @param string|\ReflectionClass $reflectable
     * @param null|ReflectorInfo      $info
     *
     * @return null|\ReflectionClass
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectTheInterface($reflectable, ReflectorInfo &$info = null) : ?\ReflectionClass
    {
        $result = null;

        if (null !== ( $reflection = $this->assert->filterReflectionClass($reflectable) )) {
            if ($reflection->isInterface()) {
                $newInfo = $this->newReflectorInfoFromReflection($reflection);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflection;
            }
        } elseif (null !== $this->assert->filterReflectableInterface($reflectable)) {
            $result = $this->buildReflectionClass($reflectable, $info);

        }

        return $result;
    }

    /**
     * @param string|\ReflectionClass $reflectable
     * @param null|ReflectorInfo      $info
     *
     * @return null|\ReflectionClass
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectTheTrait($reflectable, ReflectorInfo &$info = null) : ?\ReflectionClass
    {
        $result = null;

        if (null !== ( $reflection = $this->assert->filterReflectionClass($reflectable) )) {
            if ($reflection->isTrait()) {
                $newInfo = $this->newReflectorInfoFromReflection($reflection);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflection;
            }
        } elseif (null !== $this->assert->filterReflectableTrait($reflectable)) {
            $result = $this->buildReflectionClass($reflectable, $info);

        }

        return $result;
    }


    /**
     * @param string|\Closure|\ReflectionFunction $reflectableCallable
     * @param null|ReflectorInfo                  $info
     *
     * @return \ReflectionFunction|\ReflectionMethod|\ReflectionFunctionAbstract
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectCallable($reflectableCallable, ReflectorInfo &$info = null) : ?\ReflectionFunctionAbstract
    {
        $result = null;

        if (null !== ( $reflectionMethod = $this->assert->filterReflectionMethod($reflectableCallable) )) {
            $newInfo = $this->newReflectorInfoFromReflection($reflectionMethod);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionMethod;

        } elseif (null !== ( $reflectionFunction = $this->assert->filterReflectionFunction($reflectableCallable) )) {
            $result = $reflectionFunction;

        } elseif (( null !== ( $function = $this->filter->filterClosure($reflectableCallable) ) )
            || ( null !== ( $function = $this->filter->filterCallableStringFunction($reflectableCallable) ) )
        ) {
            $result = $this->newReflectionFunction($function);

        } elseif (( null !== ( $reflectableMethod = $this->filter->filterCallableArray($reflectableCallable) ) )
            || ( null !== ( $reflectableMethod = $this->filter->filterCallableStringStatic($reflectableCallable) ) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }

    /**
     * @param string|array|\ReflectionMethod $reflectableInvokable
     * @param null|ReflectorInfo             $info
     *
     * @return \ReflectionFunction|\ReflectionMethod|\ReflectionFunctionAbstract
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectInvokable($reflectableInvokable, ReflectorInfo &$info = null) : ?\ReflectionFunctionAbstract
    {
        $result = null;

        if (null !== ( $reflectionMethod = $this->assert->filterReflectionMethod($reflectableInvokable) )) {
            $newInfo = $this->newReflectorInfoFromReflection($reflectionMethod);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionMethod;

        } elseif (null !== ( $reflectionFunction = $this->assert->filterReflectionFunction($reflectableInvokable) )) {
            $result = $reflectionFunction;

        } elseif (( null !== ( $function = $this->filter->filterClosure($reflectableInvokable) ) )
            || ( null !== ( $function = $this->filter->filterCallableStringFunction($reflectableInvokable) ) )
        ) {
            $result = $this->newReflectionFunction($function);

        } elseif (( null !== ( $reflectableMethod = $this->filter->filterCallableArray($reflectableInvokable) ) )
            || ( null !== ( $reflectableMethod = $this->filter->filterCallableStringStatic($reflectableInvokable) ) )
            || ( null !== ( $reflectableMethod = $this->filter->filterHandler($reflectableInvokable) ) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }


    /**
     * @param string|\Closure|\ReflectionFunction $reflectableFunction
     *
     * @return \ReflectionFunction
     * @throws ReflectionRuntimeException
     */
    public function reflectFunction($reflectableFunction) : ?\ReflectionFunction
    {
        $result = null;

        if (null !== ( $reflectionFunction = $this->assert->filterReflectionFunction($reflectableFunction) )) {
            $result = $reflectionFunction;

        } elseif (( null !== ( $function = $this->filter->filterClosure($reflectableFunction) ) )
            || ( null !== ( $function = $this->filter->filterCallableStringFunction($reflectableFunction) ) )
        ) {
            $result = $this->newReflectionFunction($reflectableFunction);
        }

        return $result;
    }

    /**
     * @param string|array|\ReflectionMethod $reflectableMethod
     * @param null|ReflectorInfo             $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectMethod($reflectableMethod, ReflectorInfo &$info = null) : ?\ReflectionMethod
    {
        $result = null;

        if (null !== ( $reflectionMethod = $this->assert->filterReflectionMethod($reflectableMethod) )) {
            $newInfo = $this->newReflectorInfoFromReflection($reflectionMethod);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionMethod;

        } elseif (0
            || ( null !== $this->filter->filterMethodArray($reflectableMethod) )
            || ( null !== $this->filter->filterCallableStringStatic($reflectableMethod) )
            || ( null !== $this->filter->filterHandler($reflectableMethod) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }


    /**
     * @param \Closure|\ReflectionFunction $reflectableClosure
     *
     * @return \ReflectionFunction
     * @throws ReflectionRuntimeException
     */
    public function reflectClosure($reflectableClosure) : ?\ReflectionFunction
    {
        $result = null;

        if (null !== ( $reflectionFunction = $this->assert->filterReflectionFunction($reflectableClosure) )) {
            if ($reflectionFunction->isClosure()) {
                $result = $reflectionFunction;
            }
        } elseif (null !== ( $function = $this->filter->filterClosure($reflectableClosure) )) {
            $result = $this->newReflectionFunction($function);
        }

        return $result;
    }


    /**
     * @param string|array|\ReflectionMethod $reflectableMethod
     * @param null|ReflectorInfo             $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectMethodStatic($reflectableMethod, ReflectorInfo &$info = null) : ?\ReflectionMethod
    {
        $result = null;
        $reflectionMethod = null;

        if (null !== ( $reflectionMethod = $this->assert->filterReflectionMethod($reflectableMethod) )) {
            if ($reflectionMethod->isStatic()) {
                $newInfo = $this->newReflectorInfoFromReflection($reflectionMethod);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflectionMethod;
            }
        } elseif (( null !== $this->filter->filterCallableArrayStatic($reflectableMethod) )
            || ( null !== $this->filter->filterCallableStringStatic($reflectableMethod) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }

    /**
     * @param string|array|\ReflectionMethod $reflectableMethod
     * @param null|ReflectorInfo             $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectMethodPublic($reflectableMethod, ReflectorInfo &$info = null) : ?\ReflectionMethod
    {
        $result = null;

        if (null !== ( $reflectionMethod = $this->assert->filterReflectionMethod($reflectableMethod) )) {
            if ($reflectionMethod->isPublic()) {
                $newInfo = $this->newReflectorInfoFromReflection($reflectionMethod);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflectionMethod;
            }
        } elseif (( null !== $this->filter->filterCallableArrayPublic($reflectableMethod) )
            || ( null !== $this->filter->filterHandler($reflectableMethod) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }


    /**
     * @param string|object|\ReflectionClass|\ReflectionProperty $reflectableOrProperty
     * @param null|string                                        $propertyName
     * @param null|ReflectorInfo                                 $info
     *
     * @return \ReflectionProperty
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectProperty($reflectableOrProperty, string $propertyName = null, ReflectorInfo &$info = null) : ?\ReflectionProperty
    {
        $result = null;

        if (null !== ( $reflectionProperty = $this->assert->filterReflectionProperty($reflectableOrProperty) )) {
            $newInfo = $this->newReflectorInfoFromReflection($reflectionProperty);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionProperty;

        } elseif (( null !== $this->filter->filterWord($propertyName) )
            && ( null !== ( $reflectable = $this->assert->filterReflectable($reflectableOrProperty) ) )
        ) {
            $result = $this->buildReflectionProperty($reflectableOrProperty, $propertyName, $info);
        }

        return $result;
    }

    /**
     * @param string|array|\ReflectionFunction|\ReflectionMethod $reflectableInvokableOrParameter
     * @param null|int|string                                    $parameter
     * @param null|ReflectorInfo                                 $info
     *
     * @return \ReflectionParameter
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectParameter($reflectableInvokableOrParameter, $parameter = null, ReflectorInfo &$info = null) : ?\ReflectionParameter
    {
        $result = null;
        $reflectionParameter = null;

        if (null !== ( $reflectionParameter = $this->assert->filterReflectionParameter($reflectableInvokableOrParameter) )) {
            $newInfo = $this->newReflectorInfoFromReflection($reflectionParameter);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionParameter;

        } elseif (null !== $this->filter->filterWordOrInt($parameter)) {
            $result = $this->buildReflectionParameter($reflectableInvokableOrParameter, $parameter, $info);
        }

        return $result;
    }


    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $property
     * @param string|string[]|bool[]         ...$tags
     *
     * @return bool
     */
    public function propertyExists($reflectable, string $property, ...$tags) : bool
    {
        try {
            $info = $this->propertyTags($reflectable, $property);
        }
        catch ( \Exception $e ) {
            return false;
        }

        if ($tags) {
            return $this->matchInfoTags($info, ...$tags);
        }

        return true;
    }

    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $method
     *
     * @return bool
     */
    public function propertyPresent($reflectable, string $method) : bool
    {
        $result = $this->propertyExists($reflectable, $method, static::TAG_DEFAULT,
            static::TAG_PRESENT
        );

        return $result;
    }

    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $method
     *
     * @return bool
     */
    public function propertyDeclared($reflectable, string $method) : bool
    {
        $result = $this->propertyExists($reflectable, $method, static::TAG_DEFAULT,
            static::TAG_DECLARED
        );

        return $result;
    }

    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $method
     *
     * @return bool
     */
    public function propertyTrait($reflectable, string $method) : bool
    {
        $result = $this->propertyExists($reflectable, $method, static::TAG_TRAIT,
            static::TAG_TRAIT
        );

        return $result;
    }


    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $method
     * @param string|string[]|bool[]         ...$tags
     *
     * @return bool
     */
    public function methodExists($reflectable, string $method, ...$tags) : bool
    {
        try {
            $info = $this->methodTags($reflectable, $method);
        }
        catch ( \Exception $e ) {
            return false;
        }

        if ($tags) {
            return $this->matchInfoTags($info, ...$tags);
        }

        return true;
    }

    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $method
     *
     * @return bool
     */
    public function methodPresent($reflectable, string $method) : bool
    {
        $result = $this->methodExists($reflectable, $method,
            static::TAG_PRESENT
        );

        return $result;
    }

    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $method
     *
     * @return bool
     */
    public function methodDeclared($reflectable, string $method) : bool
    {
        $result = $this->methodExists($reflectable, $method,
            static::TAG_DECLARED
        );

        return $result;
    }

    /**
     * @param string|object|\ReflectionClass $reflectable
     * @param string                         $method
     *
     * @return bool
     */
    public function methodTrait($reflectable, string $method) : bool
    {
        $result = $this->methodExists($reflectable, $method,
            static::TAG_TRAIT
        );

        return $result;
    }


    /**
     * @param mixed              $reflectable
     * @param string             $property
     * @param null|ReflectorInfo $info
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function propertyTags($reflectable, string $property, ReflectorInfo &$info = null) : array
    {
        $reflectionTags = [];

        $rp = $this->reflectProperty($reflectable, $property, $info);

        $declaringClass = $rp->getDeclaringClass();

        $isPresent = $rp->isDefault() && $declaringClass->getName() === $info->getClass();
        $isDeclared = $isPresent;

        $isTrait = false;
        if ($isPresent) {
            foreach ( $declaringClass->getTraits() as $trait => $traitRc ) {
                if ($traitRc->hasProperty($rp->getName())) {
                    $isDeclared = false;
                    $isTrait = true;
                    break;
                }
            }
        }

        $reflectionTags[ static::TAG_DEFAULT ] = $rp->isDefault();
        $reflectionTags[ static::TAG_PRESENT ] = $isPresent;
        $reflectionTags[ static::TAG_DECLARED ] = $isDeclared;
        $reflectionTags[ static::TAG_TRAIT ] = $isTrait;

        $reflectionTags[ static::TAG_PRIVATE ] = $rp->isPrivate();
        $reflectionTags[ static::TAG_PROTECTED ] = $rp->isProtected();
        $reflectionTags[ static::TAG_PUBLIC ] = $rp->isPublic();

        $reflectionTags[ static::TAG_STATIC ] = $rp->isStatic();

        return $reflectionTags;
    }

    /**
     * @param mixed              $reflectable
     * @param string             $method
     * @param null|ReflectorInfo $info
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function methodTags($reflectable, string $method, ReflectorInfo &$info = null) : array
    {
        $reflectionTags = [];

        $callableArray = [ $reflectable, $method ];

        $rm = $this->reflectMethod($callableArray, $info);

        $declaringClass = $rm->getDeclaringClass();

        $isPresent = $declaringClass->getName() === $info->getClass();
        $isDeclared = $isPresent;

        $isTrait = false;
        if ($isPresent) {
            foreach ( $declaringClass->getTraits() as $trait => $traitRc ) {
                if ($traitRc->hasMethod($rm->getName())) {
                    $isDeclared = false;
                    $isTrait = true;
                    break;
                }
            }
        }

        $reflectionTags[ static::TAG_PRIVATE ] = $rm->isPrivate();
        $reflectionTags[ static::TAG_PROTECTED ] = $rm->isProtected();
        $reflectionTags[ static::TAG_PUBLIC ] = $rm->isPublic();

        $reflectionTags[ static::TAG_STATIC ] = $rm->isStatic();

        $reflectionTags[ static::TAG_ABSTRACT ] = $rm->isAbstract();
        $reflectionTags[ static::TAG_FINAL ] = $rm->isFinal();

        $reflectionTags[ static::TAG_PRESENT ] = $isPresent;
        $reflectionTags[ static::TAG_DECLARED ] = $isDeclared;
        $reflectionTags[ static::TAG_TRAIT ] = $isTrait;

        return $reflectionTags;
    }


    /**
     * @param array $reflectionTags
     * @param mixed ...$tags
     *
     * @return bool
     */
    protected function matchInfoTags(array $reflectionTags, ...$tags) : bool
    {
        $index = [];

        [ $kwargs, $args ] = $this->php->kwargsFlatten(...$tags);

        foreach ( array_keys($kwargs) as $arg ) {
            $index[ $arg ] = true;
        }

        foreach ( $args as $arg ) {
            $index[ $arg ] = true;
        }

        if ($index) {
            if (array_diff_key($index, $reflectionTags)) {
                return false;
            }

            if ($index !== array_intersect_assoc($index, $reflectionTags)) {
                return false;
            }
        }

        return true;
    }
}
