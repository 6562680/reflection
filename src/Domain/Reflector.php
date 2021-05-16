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
     * @param mixed              $reflectable
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionClass
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionClass($reflectable, ReflectorInfo &$info = null) : \ReflectionClass
    {
        $info = $info ?? $this->newReflectableInfo($reflectable);

        $reflectionClass = $this->newReflectionClass($reflectable);

        $info->setReflectionClass($reflectionClass);

        return $reflectionClass;
    }

    /**
     * @param mixed              $reflectableCallable
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionMethod($reflectableCallable, ReflectorInfo &$info = null) : \ReflectionMethod
    {
        $info = $info ?? $this->newReflectableInfoCallableMethod($reflectableCallable);

        $reflectionMethod = $this->newReflectionMethod($reflectableCallable);

        $info->setReflectionClass($reflectionMethod->getDeclaringClass());

        return $reflectionMethod;
    }

    /**
     * @param mixed              $reflectable
     * @param string             $propertyName
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionProperty
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionProperty($reflectable, string $propertyName, ReflectorInfo &$info = null) : \ReflectionProperty
    {
        if ('' === $propertyName) {
            throw new InvalidArgumentException('Property should be not empty', func_get_args());
        }

        $info = $info ?? $this->newReflectableInfo($reflectable);

        $reflectionProperty = $this->newReflectionProperty($reflectable, $propertyName);

        $info->setReflectionClass($reflectionProperty->getDeclaringClass());

        return $reflectionProperty;
    }

    /**
     * @param mixed              $reflectableCallable
     * @param string             $parameterName
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionParameter
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    protected function buildReflectionParameter($reflectableCallable, string $parameterName, ReflectorInfo &$info = null) : \ReflectionParameter
    {
        if ('' === $parameterName) {
            throw new InvalidArgumentException('Parameter should be not empty', func_get_args());
        }

        $info = $info ?? $this->newReflectableInfoCallableMethod($reflectableCallable);

        $reflectionParameter = $this->newReflectionParameter($reflectableCallable, $parameterName);

        if ($reflectionClass = $reflectionParameter->getDeclaringClass()) {
            $info->setReflectionClass($reflectionClass);
        }

        return $reflectionParameter;
    }


    /**
     * @param mixed $reflectable
     *
     * @return ReflectorInfo
     * @throws InvalidArgumentException
     */
    public function newReflectableInfo($reflectable) : ReflectorInfo
    {
        $info = new ReflectorInfo();

        if (null !== $this->assert->filterReflectableInstance($reflectable)) {
            $info->setObject($reflectable);
            $info->setClass(get_class($reflectable));

        } elseif (null !== $this->assert->filterReflectableClass($reflectable)) {
            $info->setClass($reflectable);

        } else {
            throw new InvalidArgumentException('Reflectable should be object or class', func_get_args());
        }

        return $info;
    }

    /**
     * @param mixed $reflection
     *
     * @return ReflectorInfo
     * @throws InvalidArgumentException
     */
    public function newReflectableInfoReflection($reflection) : ReflectorInfo
    {
        $info = new ReflectorInfo();

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionClass($reflection) ) )
        ) {
            $info->setReflectionClass($reflection);
            $info->setClass($reflection->getName());

        } elseif (null !== ( $reflection = $this->assert->filterReflectionMethod($reflection) )) {
            $reflectionClass = $reflection->getDeclaringClass();

            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== ( $reflection = $this->assert->filterReflectionProperty($reflection) )) {
            $reflectionClass = $reflection->getDeclaringClass();

            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } else {
            throw new InvalidArgumentException('Reflection should be class that implements \Reflector', func_get_args());
        }

        return $info;
    }

    /**
     * @param mixed $reflectableMethod
     *
     * @return ReflectorInfo
     * @throws InvalidArgumentException
     */
    public function newReflectableInfoCallableMethod($reflectableMethod) : ReflectorInfo
    {
        $reflectable = null;

        if (null !== $this->filter->filterCallableArray($reflectableMethod)) {
            $reflectable = $reflectableMethod[ 0 ];

        } elseif (null !== $this->filter->filterCallableStringStatic($reflectableMethod)) {
            [ $reflectable ] = explode('::', $reflectableMethod, 2);

        } else {
            throw new InvalidArgumentException('ReflectableMethod should be callable string static or array', func_get_args());
        }

        $info = $this->newReflectableInfo($reflectable);

        return $info;
    }


    /**
     * @param mixed $reflectable
     *
     * @return \ReflectionClass
     * @throws ReflectionRuntimeException
     */
    public function newReflectionClass($reflectable) : \ReflectionClass
    {
        try {
            $result = new \ReflectionClass($reflectable);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $result;
    }


    /**
     * @param mixed $reflectableFunction
     *
     * @return \ReflectionFunction
     * @throws ReflectionRuntimeException
     */
    public function newReflectionFunction($reflectableFunction) : ?\ReflectionFunction
    {
        try {
            $reflection = new \ReflectionFunction($reflectableFunction);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $reflection;
    }

    /**
     * @param mixed $reflectableMethod
     *
     * @return \ReflectionMethod
     * @throws ReflectionRuntimeException
     */
    public function newReflectionMethod($reflectableMethod) : \ReflectionMethod
    {
        try {
            $reflection = new \ReflectionMethod($reflectableMethod);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $reflection;
    }


    /**
     * @param        $reflectable
     * @param string $propertyName
     *
     * @return \ReflectionProperty
     * @throws ReflectionRuntimeException
     */
    public function newReflectionProperty($reflectable, string $propertyName) : ?\ReflectionProperty
    {
        try {
            $reflection = new \ReflectionProperty($reflectable, $propertyName);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $reflection;
    }

    /**
     * @param mixed  $reflectableFunction
     * @param string $parameterName
     *
     * @return \ReflectionParameter
     * @throws ReflectionRuntimeException
     */
    public function newReflectionParameter($reflectableFunction, string $parameterName) : ?\ReflectionParameter
    {
        try {
            $reflection = new \ReflectionParameter($reflectableFunction, $parameterName);
        }
        catch ( \ReflectionException $e ) {
            throw new ReflectionRuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $reflection;
    }


    /**
     * @param mixed                  $item
     * @param string                 $property
     * @param string|string[]|bool[] ...$tags
     *
     * @return bool
     */
    public function propertyExists($item, string $property, ...$tags) : bool
    {
        try {
            $info = $this->propertyTags($item, $property);
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
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function propertyPresent($item, string $method) : bool
    {
        $result = $this->propertyExists($item, $method, static::TAG_DEFAULT,
            static::TAG_PRESENT
        );

        return $result;
    }

    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function propertyDeclared($item, string $method) : bool
    {
        $result = $this->propertyExists($item, $method, static::TAG_DEFAULT,
            static::TAG_DECLARED
        );

        return $result;
    }

    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function propertyTrait($item, string $method) : bool
    {
        $result = $this->propertyExists($item, $method, static::TAG_TRAIT,
            static::TAG_TRAIT
        );

        return $result;
    }


    /**
     * @param mixed                  $item
     * @param string                 $method
     * @param string|string[]|bool[] ...$tags
     *
     * @return bool
     */
    public function methodExists($item, string $method, ...$tags) : bool
    {
        try {
            $info = $this->methodTags($item, $method);
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
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function methodPresent($item, string $method) : bool
    {
        $result = $this->methodExists($item, $method,
            static::TAG_PRESENT
        );

        return $result;
    }

    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function methodDeclared($item, string $method) : bool
    {
        $result = $this->methodExists($item, $method,
            static::TAG_DECLARED
        );

        return $result;
    }

    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function methodTrait($item, string $method) : bool
    {
        $result = $this->methodExists($item, $method,
            static::TAG_TRAIT
        );

        return $result;
    }


    /**
     * @param mixed              $reflectable
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionClass
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectClass($reflectable, ReflectorInfo &$info = null) : ?\ReflectionClass
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionClass($reflectable) ) )
        ) {
            $newInfo = $this->newReflectableInfoReflection($reflection);
            $info = $info ?? $newInfo;
            $info->copy($newInfo);

            $result = $reflection;

        } elseif (0
            || ( null !== $this->assert->isReflectable($reflectable) )
        ) {
            $result = $this->buildReflectionClass($reflectable, $info);
        }

        return $result;
    }


    /**
     * @param mixed $reflectableFunction
     *
     * @return \ReflectionFunction
     * @throws ReflectionRuntimeException
     */
    public function reflectFunction($reflectableFunction) : ?\ReflectionFunction
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionFunction($reflectableFunction) ) )
        ) {
            $result = $reflection;

        } elseif (0
            || ( null !== $this->assert->isReflectableFunction($reflectableFunction) )
        ) {
            $result = $this->newReflectionFunction($reflectableFunction);
        }

        return $result;
    }

    /**
     * @param mixed              $reflectableMethod
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectMethod($reflectableMethod, ReflectorInfo &$info = null) : ?\ReflectionMethod
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionMethod($reflectableMethod) ) )
        ) {
            $newInfo = $this->newReflectableInfoReflection($reflection);
            $info = $info ?? $newInfo;
            $info->copy($newInfo);

            $result = $reflection;

        } elseif (0
            || ( null !== $this->assert->isReflectableMethod($reflectableMethod) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }


    /**
     * @param mixed $reflectableClosure
     *
     * @return \ReflectionFunction
     * @throws ReflectionRuntimeException
     */
    public function reflectClosure($reflectableClosure) : ?\ReflectionFunction
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionFunction($reflectableClosure) ) )
        ) {
            $result = $reflection;

        } elseif (0
            || ( null !== $this->filter->filterClosure($reflectableClosure) )
        ) {
            $result = $this->newReflectionFunction($reflectableClosure);
        }

        return $result;
    }


    /**
     * @param mixed $reflectableFunction
     *
     * @return \ReflectionFunction
     * @throws ReflectionRuntimeException
     */
    public function reflectCallableFunction($reflectableFunction) : ?\ReflectionFunction
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionFunction($reflectableFunction) ) )
        ) {
            $result = $reflection;

        } elseif (0
            || ( null !== $this->filter->filterClosure($reflectableFunction) )
            || ( null !== $this->filter->filterCallableStringFunction($reflectableFunction) )
        ) {
            $result = $this->newReflectionFunction($reflectableFunction);
        }

        return $result;
    }

    /**
     * @param mixed              $reflectableMethod
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectCallableMethod($reflectableMethod, ReflectorInfo &$info = null) : ?\ReflectionMethod
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionMethod($reflectableMethod) ) )
        ) {
            $newInfo = $this->newReflectableInfoReflection($reflection);
            $info = $info ?? $newInfo;
            $info->copy($newInfo);

            $result = $reflection;

        } elseif (0
            || ( null !== $this->filter->filterCallableArray($reflectableMethod) )
            || ( null !== $this->filter->filterCallableStringStatic($reflectableMethod) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }


    /**
     * @param mixed              $reflectableMethod
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectCallableMethodStatic($reflectableMethod, ReflectorInfo &$info = null) : ?\ReflectionMethod
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionMethod($reflectableMethod) ) )
        ) {
            $newInfo = $this->newReflectableInfoReflection($reflection);
            $info = $info ?? $newInfo;
            $info->copy($newInfo);

            $result = $reflection;

        } elseif (0
            || ( null !== $this->filter->filterCallableArrayStatic($reflectableMethod) )
            || ( null !== $this->filter->filterCallableStringStatic($reflectableMethod) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }

    /**
     * @param mixed              $reflectableMethod
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionMethod
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectCallableMethodPublic($reflectableMethod, ReflectorInfo &$info = null) : ?\ReflectionMethod
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionMethod($reflectableMethod) ) )
        ) {
            $newInfo = $this->newReflectableInfoReflection($reflection);
            $info = $info ?? $newInfo;
            $info->copy($newInfo);

            $result = $reflection;

        } elseif (0
            || ( null !== $this->filter->filterCallableArrayPublic($reflectableMethod) )
        ) {
            $result = $this->buildReflectionMethod($reflectableMethod, $info);
        }

        return $result;
    }


    /**
     * @param mixed              $reflectable
     * @param string             $propertyName
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionProperty
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectProperty($reflectable, string $propertyName, ReflectorInfo &$info = null) : ?\ReflectionProperty
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionProperty($reflectable) ) )
        ) {
            $newInfo = $this->newReflectableInfoReflection($reflection);
            $info = $info ?? $newInfo;
            $info->copy($newInfo);

            $result = $reflection;

        } elseif (1
            && ( '' !== $propertyName )
            && ( null !== $this->assert->isReflectable($reflectable) )
        ) {
            $result = $this->buildReflectionProperty($reflectable, $propertyName, $info);
        }

        return $result;
    }

    /**
     * @param mixed              $reflectableCallable
     * @param string             $parameterName
     * @param null|ReflectorInfo $info
     *
     * @return \ReflectionParameter
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function reflectParameter($reflectableCallable, string $parameterName, ReflectorInfo &$info = null) : ?\ReflectionParameter
    {
        $result = null;
        $reflection = null;

        if (0
            || ( null !== ( $reflection = $this->assert->filterReflectionParameter($reflectableCallable) ) )
        ) {
            $newInfo = $this->newReflectableInfoReflection($reflection);
            $info = $info ?? $newInfo;
            $info->copy($newInfo);

            $result = $reflection;

        } elseif (1
            && ( '' !== $parameterName )
            && ( null !== $this->assert->isReflectableCallable($reflectableCallable) )
        ) {
            $result = $this->buildReflectionParameter($reflectableCallable, $parameterName, $info);
        }

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

        $reflectionCallable = [ $reflectable, $method ];

        $rm = $this->reflectMethod($reflectionCallable, $info);

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
