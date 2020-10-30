<?php

namespace Gzhegow\Reflection;

use Gzhegow\Support\Php;
use Gzhegow\Support\Type;
use Psr\SimpleCache\CacheInterface;
use Gzhegow\Reflection\Exceptions\RuntimeException;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;

/**
 * Class CachedReflection
 */
class CachedReflection
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
	 * @var CacheInterface
	 */
	protected $cache;


	/**
	 * Constructor
	 *
	 * @param Php            $php
	 * @param Type           $type
	 * @param CacheInterface $cache
	 */
	public function __construct(
		Php $php,
		Type $type,

		CacheInterface $cache
	)
	{
		$this->php = $php;
		$this->type = $type;

		$this->cache = $cache;
	}


	/**
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function cacheGet(string $key, $default = null)
	{
		try {
			$result = $this->cache->get($key, $default);
		}
		catch ( \Psr\SimpleCache\InvalidArgumentException $e ) {
			throw new RuntimeException('Unable to ' . __METHOD__, func_get_args(), $e);
		}

		return $result;
	}


	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function cacheHas(string $key) : bool
	{
		try {
			$result = $this->cache->has($key);
		}
		catch ( \Psr\SimpleCache\InvalidArgumentException $e ) {
			throw new RuntimeException('Unable to ' . __METHOD__, func_get_args(), $e);
		}

		return $result;
	}


	/**
	 * @param string                 $key
	 * @param mixed                  $value
	 * @param null|int|\DateInterval $ttl
	 *
	 * @return CachedReflection
	 */
	public function cacheSet(string $key, $value, $ttl = null)
	{
		try {
			$this->cache->set($key, $value, $ttl);
		}
		catch ( \Psr\SimpleCache\InvalidArgumentException $e ) {
			throw new RuntimeException('Unable to ' . __METHOD__, func_get_args(), $e);
		}

		return $this;
	}


	/**
	 * @param mixed        $item
	 * @param string|null &$class
	 *
	 * @return ReflectionClass
	 */
	public function reflectClass($item, string &$class = null) : ReflectionClass
	{
		switch ( true ):
			case $this->type->isReflectableClass($item, $class):
			case $this->type->isReflectionClass($item, $class):
				break;

			default:
				throw new InvalidArgumentException('Argument 1 should be object or class', func_get_args());

		endswitch;

		if ($this->cacheHas($key = $class)) {
			$result = $this->cacheGet($key);

		} else {
			try {
				$result = new ReflectionClass($item);
			}
			catch ( \ReflectionException $e ) {
				throw new RuntimeException('Unable to reflect', func_get_args(), $e);
			}

			$this->cacheSet($key, $result, 7 * 86400);
		}

		return $result;
	}

	/**
	 * @param mixed        $item
	 * @param string|null &$class
	 *
	 * @return \ReflectionClass
	 */
	public function reflectClassNormal($item, string &$class = null) : \ReflectionClass
	{
		switch ( true ):
			case $this->type->isReflectableClass($item, $class):
			case $this->type->isReflectionClass($item, $class):
				break;

			default:
				throw new InvalidArgumentException('Argument 1 should be object or class', func_get_args());

		endswitch;

		if ($this->cacheHas($key = $class)) {
			$result = $this->cacheGet($key);

		} else {
			try {
				$result = new \ReflectionClass($item);
			}
			catch ( \ReflectionException $e ) {
				throw new RuntimeException('Unable to reflect', func_get_args(), $e);
			}

			$this->cacheSet($key, $result, 7 * 86400);
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

		if ($this->cacheHas($key = $class . '::' . $method)) {
			$result = $this->cacheGet($key);

		} else {
			try {
				$result = new \ReflectionMethod($item, $method);
			}
			catch ( \ReflectionException $e ) {
				throw new RuntimeException('Unable to reflect', func_get_args(), $e);
			}

			$this->cacheSet($key, $result, 7 * 86400);
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

		if ($this->cacheHas($key = $class . '.' . $property)) {
			$result = $this->cacheGet($key);

		} else {
			try {
				$result = new \ReflectionProperty($item, $property);
			}
			catch ( \ReflectionException $e ) {
				throw new RuntimeException('Unable to reflect', func_get_args(), $e);
			}

			$this->cacheSet($key, $result, 7 * 86400);
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
		if ($this->cacheHas($key = $func)) {
			$result = $this->cacheGet($key);

		} else {
			try {
				$result = new \ReflectionFunction($func);
			}
			catch ( \ReflectionException $e ) {
				throw new RuntimeException('Unable to reflect', func_get_args(), $e);
			}

			$this->cacheSet($key, $result, 7 * 86400);
		}

		return $result;
	}
}
