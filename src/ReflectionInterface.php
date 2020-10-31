<?php

namespace Gzhegow\Reflection;


/**
 * Class Reflection
 */
interface ReflectionInterface
{
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
	 * @param mixed        $item
	 * @param string|null &$class
	 *
	 * @return ReflectionClass
	 */
	public function reflectClass($item, string &$class = null) : ReflectionClass;

	/**
	 * @param mixed        $item
	 * @param string|null &$class
	 *
	 * @return ReflectionClass
	 */
	public function reflectClassNormal($item, string &$class = null) : \ReflectionClass;


	/**
	 * @param mixed        $item
	 * @param string       $method
	 * @param string|null &$class
	 *
	 * @return \ReflectionMethod
	 */
	public function reflectMethod($item, string $method, string &$class = null) : \ReflectionMethod;

	/**
	 * @param string $func
	 *
	 * @return \ReflectionFunction
	 */
	public function reflectFunction(string $func) : \ReflectionFunction;

	/**
	 * @param \Closure $func
	 *
	 * @return \ReflectionFunction
	 */
	public function reflectClosure(\Closure $func) : \ReflectionFunction;

	/**
	 * @param callable $callable
	 *
	 * @return \ReflectionFunction|\ReflectionMethod
	 */
	public function reflectCallable($callable);


	/**
	 * @param mixed        $item
	 * @param string       $property
	 * @param string|null &$class
	 *
	 * @return \ReflectionProperty
	 */
	public function reflectProperty($item, string $property, string &$class = null) : \ReflectionProperty;


	/**
	 * @param mixed  $item
	 * @param string $property
	 *
	 * @return array
	 */
	public function propertyInfo($item, string $property) : array;

	/**
	 * @param mixed  $item
	 * @param string $method
	 *
	 * @return array
	 */
	public function methodInfo($item, string $method) : array;
}