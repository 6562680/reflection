<?php

namespace Gzhegow\Reflection;

use Gzhegow\Support\Php;
use Gzhegow\Support\Type;
use Gzhegow\Reflection\Exceptions\RuntimeException;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;

/**
 * Class Reflection
 */
class Reflection
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
	 * @param mixed        $item
	 * @param string|null &$class
	 *
	 * @return \ReflectionClass
	 */
	public function reflectClass($item, string &$class = null) : \ReflectionClass
	{
		switch ( true ):
			case $this->type->isReflectableClass($item, $class):
			case $this->type->isReflectionClass($item, $class):
				break;

			default:
				throw new InvalidArgumentException('Argument 1 should be object or class', func_get_args());

		endswitch;

		try {
			$result = new \ReflectionClass($item);
		}
		catch ( \ReflectionException $e ) {
			throw new RuntimeException('Unable to reflect', func_get_args(), $e);
		}

		return $result;
	}

	/**
	 * @param mixed        $item
	 * @param string       $method
	 * @param string|null &$class
	 *
	 * @return \ReflectionMethod
	 */
	public function reflectMethod($item, string $method, string &$class = null) : \ReflectionMethod
	{
		switch ( true ):
			case $this->type->isReflectableClass($item, $class):
			case $this->type->isReflectionClass($item, $class):
				break;

			default:
				throw new InvalidArgumentException('Argument 1 should be object or class', func_get_args());

		endswitch;

		if ('' === $method) {
			throw new InvalidArgumentException('Property should be not empty', func_get_args());
		}

		try {
			$result = new \ReflectionMethod($item, $method);
		}
		catch ( \ReflectionException $e ) {
			throw new RuntimeException('Unable to reflect', func_get_args(), $e);
		}

		return $result;
	}

	/**
	 * @param mixed        $item
	 * @param string       $property
	 * @param string|null &$class
	 *
	 * @return \ReflectionProperty
	 */
	public function reflectProperty($item, string $property, string &$class = null) : \ReflectionProperty
	{
		switch ( true ):
			case $this->type->isReflectableClass($item, $class):
			case $this->type->isReflectionClass($item, $class):
				break;

			default:
				throw new InvalidArgumentException('Argument 1 should be object or class', func_get_args());

		endswitch;

		if ('' === $property) {
			throw new InvalidArgumentException('Property should be not empty', func_get_args());
		}

		try {
			$result = new \ReflectionProperty($item, $property);

		}
		catch ( \ReflectionException $e ) {
			throw new RuntimeException('Unable to reflect', func_get_args(), $e);
		}

		return $result;
	}


	/**
	 * @param string $func
	 *
	 * @return \ReflectionFunction
	 */
	public function reflectFunction(string $func) : \ReflectionFunction
	{
		try {
			$result = new \ReflectionFunction($func);
		}
		catch ( \ReflectionException $e ) {
			throw new RuntimeException('Unable to reflect', func_get_args(), $e);
		}

		return $result;
	}

	/**
	 * @param \Closure $func
	 *
	 * @return \ReflectionFunction
	 */
	public function reflectClosure(\Closure $func) : \ReflectionFunction
	{
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
	 * @return \ReflectionFunction|\ReflectionMethod
	 */
	public function reflectCallable($callable)
	{
		if ($this->type->isClosure($callable)) {
			$rf = $this->reflectClosure($callable);

		} elseif ($this->type->isCallableArray($callable)) {
			$rf = $this->reflectMethod($callable[ 0 ], $callable[ 1 ]);

		} else {
			$rf = $this->reflectFunction($callable);

		}

		return $rf;
	}


	/**
	 * @param mixed  $item
	 * @param string $property
	 *
	 * @return array
	 */
	public function propertyInfo($item, string $property) : array
	{
		$result = [];

		$rp = $this->reflectProperty($item, $property, $class);

		$result[ 'declared' ] = $rp->getDeclaringClass()->getName() === $class;
		$result[ 'default' ] = $rp->isDefault();
		$result[ 'private' ] = $rp->isPrivate();
		$result[ 'protected' ] = $rp->isProtected();
		$result[ 'public' ] = $rp->isPublic();
		$result[ 'static' ] = $rp->isStatic();

		return $result;
	}

	/**
	 * @param mixed  $item
	 * @param string $method
	 *
	 * @return array
	 */
	public function methodInfo($item, string $method) : array
	{
		$result = [];

		$rm = $this->reflectMethod($item, $method, $class);

		$result[ 'declared' ] = $rm->getDeclaringClass()->getName() === $class;
		$result[ 'abstract' ] = $rm->isAbstract();
		$result[ 'final' ] = $rm->isFinal();
		$result[ 'private' ] = $rm->isPrivate();
		$result[ 'protected' ] = $rm->isProtected();
		$result[ 'public' ] = $rm->isPublic();
		$result[ 'static' ] = $rm->isStatic();

		return $result;
	}
}
