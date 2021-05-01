<?php

namespace Gzhegow\Reflection;


/**
 * Reflection
 */
interface ReflectionInterface
{
    /**
     * @param \ReflectionClass    $reflection
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return bool
     */
    public function isReflection($reflection, ReflectionInfo &$reflectionInfo = null) : bool;

    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectable($reflectable) : bool;

    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableObject($reflectable) : bool;


    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableClass($reflectable) : bool;

    /**
     * @param mixed $reflectable
     *
     * @return bool
     */
    public function isReflectableInstance($reflectable) : bool;


    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function isPropertyDeclared($item, string $method) : bool;

    /**
     * @param mixed           $item
     * @param string          $property
     * @param string[]|bool[] ...$tags
     *
     * @return bool
     */
    public function isPropertyExists($item, string $property, ...$tags) : bool;


    /**
     * @param mixed  $item
     * @param string $method
     *
     * @return bool
     */
    public function isMethodDeclared($item, string $method) : bool;

    /**
     * @param mixed           $item
     * @param string          $method
     * @param string[]|bool[] ...$tags
     *
     * @return bool
     */
    public function isMethodExists($item, string $method, ...$tags) : bool;


    /**
     * @param mixed $reflectable
     *
     * @return \ReflectionClass
     */
    public function newReflection($reflectable) : \ReflectionClass;

    /**
     * @param \ReflectionClass    $reflection
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflection($reflection, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass;


    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectable($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass;

    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectableObject($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass;


    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectableClass($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass;

    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectableInstance($reflectable, ReflectionInfo &$reflectionInfo = null) : ?\ReflectionClass;


    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return ReflectionClass
     */
    public function reflectClass($reflectable, ReflectionInfo &$reflectionInfo = null) : ReflectionClass;

    /**
     * @param mixed               $reflectable
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return ReflectionClass
     */
    public function reflectClassNative($reflectable, ReflectionInfo &$reflectionInfo = null) : \ReflectionClass;


    /**
     * @param mixed               $reflectable
     * @param string              $method
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return \ReflectionMethod
     */
    public function reflectMethod($reflectable, string $method,
        ReflectionInfo &$reflectionInfo = null
    ) : \ReflectionMethod;

    /**
     * @param mixed               $reflectable
     * @param string              $property
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return \ReflectionProperty
     */
    public function reflectProperty($reflectable, string $property,
        ReflectionInfo &$reflectionInfo = null
    ) : \ReflectionProperty;


    /**
     * @param callable $callable
     *
     * @return null|\ReflectionFunction|\ReflectionMethod
     */
    public function reflectCallable($callable);

    /**
     * @param callable $callable
     *
     * @return null|\ReflectionFunction|\ReflectionMethod
     */
    public function reflectFunction($callable);

    /**
     * @param \Closure $func
     *
     * @return \ReflectionFunction
     */
    public function reflectClosure($func) : ?\ReflectionFunction;


    /**
     * @param mixed $func
     *
     * @return \ReflectionFunction
     */
    public function reflectCallableString($func) : ?\ReflectionFunction;


    /**
     * @param callable $callable
     *
     * @return null|\ReflectionFunction
     */
    public function reflectCallableArray($callable) : ?\ReflectionMethod;

    /**
     * @param callable $callable
     *
     * @return null|\ReflectionMethod
     */
    public function reflectCallableArrayStatic($callable) : ?\ReflectionMethod;

    /**
     * @param callable $callable
     *
     * @return null|\ReflectionMethod
     */
    public function reflectCallableArrayPublic($callable) : ?\ReflectionMethod;


    /**
     * @param mixed               $item
     * @param string              $property
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return array
     */
    public function propertyInfo($item, string $property, ReflectionInfo &$reflectionInfo = null) : array;

    /**
     * @param mixed               $item
     * @param string              $method
     * @param null|ReflectionInfo $reflectionInfo
     *
     * @return array
     */
    public function methodInfo($item, string $method, ReflectionInfo &$reflectionInfo = null) : array;
}
