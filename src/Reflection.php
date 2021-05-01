<?php

namespace Gzhegow\Reflection;

use Gzhegow\Support\Php;
use Gzhegow\Support\Type;
use Gzhegow\Reflection\Exceptions\RuntimeException;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;


/**
 * Reflection
 */
class Reflection implements ReflectionInterface
{
    /**
     * @var Php
     */
    protected $php;
    /**
     * @var Type
     */
    protected $type;


    /**
     * Constructor
     *
     * @param Php  $php
     * @param Type $type
     */
    public function __construct(
        Php $php,
        Type $type
    )
    {
        $this->php = $php;
        $this->type = $type;
    }


    /**
     * @param mixed $reflectable
     *
     * @return \ReflectionClass
     */
    public function newReflection($reflectable) : \ReflectionClass
    {
        try {
            $result = new \ReflectionClass($reflectable);
        }
        catch ( \ReflectionException $e ) {
            throw new RuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $result;
    }


    /**
     * @param \ReflectionClass    $reflection
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return bool
     */
    public function isReflection($reflection, ReflectionInfo &$reflectionInfo = null) : bool
    {
        return null !== $this->filterReflection($reflection, $reflectionInfo);
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
            || $this->isReflection($reflectable)
            || $this->isReflectableInstance($reflectable)
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
        if (! ( is_string($reflectable) && class_exists($reflectable) )) {
            return false;
        }

        if ($reflectable === \ReflectionClass::class) {
            return false;
        }

        return true;
    }


    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableInstance($reflectable) : bool
    {
        if (! ( is_object($reflectable) && ! is_a($reflectable, \ReflectionClass::class) )) {
            return false;
        }

        return true;
    }


    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function isPropertyDeclared($item, string $method) : bool
    {
        try {
            $array = $this->propertyInfo($item, $method);
        }
        catch ( \Exception $e ) {
            return false;
        }

        return $array[ 'default' ] && $array[ 'declared' ];
    }

    /**
     * @param mixed           $item
     * @param string          $property
     * @param string[]|bool[] ...$tags
     *
     * @return bool
     */
    public function isPropertyExists($item, string $property, ...$tags) : bool
    {
        try {
            $array = $this->propertyInfo($item, $property);
        }
        catch ( \Exception $e ) {
            return false;
        }

        [ $kwargs, $args ] = $this->php->kwargs(...$tags);

        $index = [];
        foreach ( $kwargs as $arg => $bool ) {
            $index[ $arg ] = $bool;
        }

        foreach ( $args as $arg ) {
            $index[ $arg ] = true;
        }

        if (! array_filter($index)) {
            return false;
        }

        if (array_diff_key($index, $array)) return false;
        if ($index !== array_intersect_assoc($index, $array)) return false;

        return true;
    }


    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function isMethodDeclared($item, string $method) : bool
    {
        try {
            $array = $this->methodInfo($item, $method);
        }
        catch ( \Exception $e ) {
            return false;
        }

        return (bool) $array[ 'declared' ];
    }

    /**
     * @param mixed           $item
     * @param string          $method
     * @param string[]|bool[] ...$tags
     *
     * @return bool
     */
    public function isMethodExists($item, string $method, ...$tags) : bool
    {
        try {
            $array = $this->methodInfo($item, $method);
        }
        catch ( \Exception $e ) {
            return false;
        }

        [ $kwargs, $args ] = $this->php->kwargs(...$tags);

        $index = [];
        foreach ( $kwargs as $arg => $bool ) {
            $index[ $arg ] = $bool;
        }

        foreach ( $args as $arg ) {
            $index[ $arg ] = true;
        }

        if (! array_filter($index)) {
            return false;
        }

        if (array_diff_key($index, $array)) return false;
        if ($index !== array_intersect_assoc($index, $array)) return false;

        return true;
    }


    /**
     * @param \ReflectionClass    $reflection
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflection($reflection, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass
    {
        $reflectionInfo = $reflectionInfo ?? new ReflectionInfo();

        if (is_object($reflection)
            && is_a($reflection, \ReflectionClass::class)
        ) {
            $reflectionInfo->class = $reflection->getName();
            $reflectionInfo->reflection = $reflection;

            return $reflectionInfo->reflection;
        }

        return null;
    }


    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectable($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass
    {
        $reflection = null
            ?? $this->filterReflectableObject($reflectable, $reflectionInfo)
            ?? $this->filterReflectableClass($reflectable, $reflectionInfo);

        if (null !== $reflection) {
            return $reflectionInfo->reflection;
        }

        return null;
    }


    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectableObject($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass
    {
        $reflection = null
            ?? $this->filterReflection($reflectable, $reflectionInfo)
            ?? $this->filterReflectableInstance($reflectable, $reflectionInfo);

        if (null !== $reflection) {
            return $reflectionInfo->reflection;
        }

        return null;
    }


    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectableClass($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass
    {
        $reflectionInfo = $reflectionInfo ?? new ReflectionInfo();

        if (! ( is_string($reflectable) && class_exists($reflectable) )) {
            return null;
        }

        if ($reflectable === \ReflectionClass::class) {
            return null;
        }

        $reflectionInfo->class = $reflectable;

        $reflectionInfo->reflection = $this->newReflection($reflectable);

        return $reflectionInfo->reflection;
    }

    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectableInstance($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass
    {
        $reflectionInfo = $reflectionInfo ?? new ReflectionInfo();

        if (! ( is_object($reflectable) && ! is_a($reflectable, \ReflectionClass::class) )) {
            return null;
        }

        $reflectionInfo->object = $reflectable;
        $reflectionInfo->class = get_class($reflectable);

        $reflectionInfo->reflection = $this->newReflection($reflectable);

        return $reflectionInfo->reflection;
    }


    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return ReflectionClass
     */
    public function reflectClass($reflectable, ReflectionInfo &$reflectionInfo = null) : ReflectionClass
    {
        if (null === ( $reflection = $this->filterReflectable($reflectable, $reflectionInfo) )) {
            throw new InvalidArgumentException('Item should be reflectable', func_get_args());
        }

        $reflection = ReflectionClass::fromNative($reflection);

        return $reflection;
    }

    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return ReflectionClass
     */
    public function reflectClassNative($reflectable, ReflectionInfo &$reflectionInfo = null) : \ReflectionClass
    {
        if (null === ( $reflection = $this->filterReflectable($reflectable, $reflectionInfo) )) {
            throw new InvalidArgumentException('Item should be reflectable', func_get_args());
        }

        return $reflection;
    }


    /**
     * @param mixed               $reflectable
     * @param string              $method
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return \ReflectionMethod
     */
    public function reflectMethod($reflectable, string $method,
        ReflectionInfo &$reflectionInfo = null
    ) : \ReflectionMethod
    {
        if ('' === $method) {
            throw new InvalidArgumentException('Method should be not empty', func_get_args());
        }

        $reflection = $this->reflectClass($reflectable, $reflectionInfo);

        try {
            $rm = $reflection->getMethod($method);
        }
        catch ( \ReflectionException $e ) {
            throw new RuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $rm;
    }

    /**
     * @param mixed               $reflectable
     * @param string              $property
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return \ReflectionProperty
     */
    public function reflectProperty($reflectable, string $property,
        ReflectionInfo &$reflectionInfo = null
    ) : \ReflectionProperty
    {
        if ('' === $property) {
            throw new InvalidArgumentException('Property should be not empty', func_get_args());
        }

        $reflection = $this->reflectClass($reflectable, $reflectionInfo);

        try {
            $rp = $reflection->getProperty($property);
        }
        catch ( \ReflectionException $e ) {
            throw new RuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $rp;
    }


    /**
     * @param callable $callable
     *
     * @return null|\ReflectionFunction|\ReflectionMethod
     */
    public function reflectCallable($callable) // : ?\ReflectionFunction|\ReflectionMethod
    {
        $reflection = null
            ?? $this->reflectFunction($callable)
            ?? $this->reflectCallableArray($callable);

        return $reflection;
    }


    /**
     * @param callable $callable
     *
     * @return null|\ReflectionFunction|\ReflectionMethod
     */
    public function reflectFunction($callable) // : ?\ReflectionFunction|\ReflectionMethod
    {
        $reflection = null
            ?? $this->reflectClosure($callable)
            ?? $this->reflectCallableString($callable);

        return $reflection;
    }


    /**
     * @param \Closure $func
     *
     * @return \ReflectionFunction
     */
    public function reflectClosure($func) : ?\ReflectionFunction
    {
        if (! $this->type->isClosure($func)) {
            return null;
        }

        try {
            $result = new \ReflectionFunction($func);
        }
        catch ( \ReflectionException $e ) {
            throw new RuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $result;
    }


    /**
     * @param mixed $func
     *
     * @return \ReflectionFunction
     */
    public function reflectCallableString($func) : ?\ReflectionFunction
    {
        if (! ( is_string($func) && function_exists($func) )) {
            return null;
        }

        try {
            $result = new \ReflectionFunction($func);
        }
        catch ( \ReflectionException $e ) {
            throw new RuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $result;
    }


    /**
     * @param callable $callable
     *
     * @return null|\ReflectionFunction
     */
    public function reflectCallableArray($callable) : ?\ReflectionMethod
    {
        $reflection = $this->type->isCallableArray($callable)
            ? $this->reflectMethod($callable[ 0 ], $callable[ 1 ])
            : null;

        return $reflection;
    }

    /**
     * @param callable $callable
     *
     * @return null|\ReflectionMethod
     */
    public function reflectCallableArrayStatic($callable) : ?\ReflectionMethod
    {
        $reflection = $this->type->isCallableArrayStatic($callable)
            ? $this->reflectMethod($callable[ 0 ], $callable[ 1 ])
            : null;

        return $reflection;
    }

    /**
     * @param callable $callable
     *
     * @return null|\ReflectionMethod
     */
    public function reflectCallableArrayPublic($callable) : ?\ReflectionMethod
    {
        $reflection = $this->type->isCallableArrayPublic($callable)
            ? $this->reflectMethod($callable[ 0 ], $callable[ 1 ])
            : null;

        return $reflection;
    }


    /**
     * @param mixed               $item
     * @param string              $property
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return array
     */
    public function propertyInfo($item, string $property, ReflectionInfo &$reflectionInfo = null) : array
    {
        $result = [];

        $rp = $this->reflectProperty($item, $property, $reflectionInfo);

        $result[ 'declared' ] = $rp->getDeclaringClass()->getName() === $reflectionInfo->class;
        $result[ 'default' ] = $rp->isDefault();
        $result[ 'private' ] = $rp->isPrivate();
        $result[ 'protected' ] = $rp->isProtected();
        $result[ 'public' ] = $rp->isPublic();
        $result[ 'static' ] = $rp->isStatic();

        return $result;
    }

    /**
     * @param mixed               $item
     * @param string              $method
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return array
     */
    public function methodInfo($item, string $method, ReflectionInfo &$reflectionInfo = null) : array
    {
        $result = [];

        $rm = $this->reflectMethod($item, $method, $reflectionInfo);

        $result[ 'declared' ] = $rm->getDeclaringClass()->getName() === $reflectionInfo->class;
        $result[ 'abstract' ] = $rm->isAbstract();
        $result[ 'final' ] = $rm->isFinal();
        $result[ 'private' ] = $rm->isPrivate();
        $result[ 'protected' ] = $rm->isProtected();
        $result[ 'public' ] = $rm->isPublic();
        $result[ 'static' ] = $rm->isStatic();

        return $result;
    }
}
