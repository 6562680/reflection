<?php

namespace Gzhegow\Reflection\Domain;

use Gzhegow\Support\Php;
use Gzhegow\Support\Filter;
use Gzhegow\Reflection\ReflectionFactory;
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
     * @var ReflectionFactory
     */
    protected $reflectionFactory;


    /**
     * Constructor
     *
     * @param Filter            $filter
     * @param Php               $php
     *
     * @param ReflectionFactory $reflectionFactory
     */
    public function __construct(
        Filter $filter,
        Php $php,

        ReflectionFactory $reflectionFactory
    )
    {
        $this->filter = $filter;
        $this->php = $php;

        $this->reflectionFactory = $reflectionFactory;
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
            ?? $this->reflectionFactory->newReflectorInfoFromReflectable($reflectable);

        $reflectionClass = $this->reflectionFactory->newReflectionClass($reflectable);

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
        $methodArray = $this->reflectionFactory->newMethodArrayFromReflectableInvokable($reflectableMethod);

        $info = $info
            ?? $this->reflectionFactory->newReflectorInfoFromReflectableMethod($methodArray);

        $reflectionMethod = $this->reflectionFactory->newReflectionMethod($methodArray[ 0 ], $methodArray[ 1 ]);

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
            ?? $this->reflectionFactory->newReflectorInfoFromReflectable($reflectable);

        if (null !== ( $reflectionClass = $this->reflectionFactory->filterReflectionClass($reflectable) )) {
            try {
                $reflectionProperty = $reflectionClass->getProperty($propertyName);
            }
            catch ( \ReflectionException $e ) {
                throw new ReflectionRuntimeException(
                    [ 'Unable to get property: %s %s', $reflectionClass, $propertyName ], null, $e
                );
            }
        } else {
            $reflectionProperty = $this->reflectionFactory->newReflectionProperty($reflectable, $propertyName);
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

        $methodArray = $this->reflectionFactory->newMethodArrayFromReflectableInvokable($reflectableInvokable);

        $hasInfo = ( null !== $info );

        if (! $hasInfo
            && ( null !== $this->reflectionFactory->filterReflectableMethod($reflectableInvokable) )
        ) {
            $info = $this->reflectionFactory->newReflectorInfoFromReflectableMethod($reflectableInvokable);

            $hasInfo = true;
        }

        $reflectionParameter = $this->reflectionFactory->newReflectionParameter($methodArray, $parameter);

        if ($hasInfo) {
            if ($reflectionClass = $reflectionParameter->getDeclaringClass()) {
                $info->setReflectionClass($reflectionClass);
            }
        }

        return $reflectionParameter;
    }


    /**
     * @return ReflectionFactory
     */
    public function getReflectionFactory() : ReflectionFactory
    {
        return $this->reflectionFactory;
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

        if (null !== ( $reflection = $this->reflectionFactory->filterReflectionClass($reflectable) )) {
            $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflection);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflection;

        } elseif (null !== $this->reflectionFactory->filterReflectable($reflectable)) {
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

        if (null !== ( $reflection = $this->reflectionFactory->filterReflectionClass($reflectable) )) {
            if (! ( $reflection->isInterface() || $reflection->isTrait() )) {
                $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflection);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflection;
            }
        } elseif (null !== $this->reflectionFactory->filterReflectableClass($reflectable)) {
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

        if (null !== ( $reflection = $this->reflectionFactory->filterReflectionClass($reflectable) )) {
            if ($reflection->isInterface()) {
                $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflection);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflection;
            }
        } elseif (null !== $this->reflectionFactory->filterReflectableInterface($reflectable)) {
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

        if (null !== ( $reflection = $this->reflectionFactory->filterReflectionClass($reflectable) )) {
            if ($reflection->isTrait()) {
                $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflection);

                $info = $info ?? $newInfo;
                $info->sync($newInfo);

                $result = $reflection;
            }
        } elseif (null !== $this->reflectionFactory->filterReflectableTrait($reflectable)) {
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

        if (null !== ( $reflectionMethod = $this->reflectionFactory->filterReflectionMethod($reflectableCallable) )) {
            $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflectionMethod);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionMethod;

        } elseif (null !== ( $reflectionFunction = $this->reflectionFactory->filterReflectionFunction($reflectableCallable) )) {
            $result = $reflectionFunction;

        } elseif (( null !== ( $function = $this->filter->filterClosure($reflectableCallable) ) )
            || ( null !== ( $function = $this->filter->filterCallableStringFunction($reflectableCallable) ) )
        ) {
            $result = $this->reflectionFactory->newReflectionFunction($function);

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

        if (null !== ( $reflectionMethod = $this->reflectionFactory->filterReflectionMethod($reflectableInvokable) )) {
            $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflectionMethod);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionMethod;

        } elseif (null !== ( $reflectionFunction = $this->reflectionFactory->filterReflectionFunction($reflectableInvokable) )) {
            $result = $reflectionFunction;

        } elseif (( null !== ( $function = $this->filter->filterClosure($reflectableInvokable) ) )
            || ( null !== ( $function = $this->filter->filterCallableStringFunction($reflectableInvokable) ) )
        ) {
            $result = $this->reflectionFactory->newReflectionFunction($function);

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

        if (null !== ( $reflectionFunction = $this->reflectionFactory->filterReflectionFunction($reflectableFunction) )) {
            $result = $reflectionFunction;

        } elseif (( null !== ( $function = $this->filter->filterClosure($reflectableFunction) ) )
            || ( null !== ( $function = $this->filter->filterCallableStringFunction($reflectableFunction) ) )
        ) {
            $result = $this->reflectionFactory->newReflectionFunction($reflectableFunction);
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

        if (null !== ( $reflectionMethod = $this->reflectionFactory->filterReflectionMethod($reflectableMethod) )) {
            $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflectionMethod);

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

        if (null !== ( $reflectionFunction = $this->reflectionFactory->filterReflectionFunction($reflectableClosure) )) {
            if ($reflectionFunction->isClosure()) {
                $result = $reflectionFunction;
            }
        } elseif (null !== ( $function = $this->filter->filterClosure($reflectableClosure) )) {
            $result = $this->reflectionFactory->newReflectionFunction($function);
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

        if (null !== ( $reflectionMethod = $this->reflectionFactory->filterReflectionMethod($reflectableMethod) )) {
            if ($reflectionMethod->isStatic()) {
                $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflectionMethod);

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

        if (null !== ( $reflectionMethod = $this->reflectionFactory->filterReflectionMethod($reflectableMethod) )) {
            if ($reflectionMethod->isPublic()) {
                $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflectionMethod);

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

        if (null !== ( $reflectionProperty = $this->reflectionFactory->filterReflectionProperty($reflectableOrProperty) )) {
            $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflectionProperty);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionProperty;

        } elseif (( null !== $this->filter->filterWord($propertyName) )
            && ( null !== ( $reflectable = $this->reflectionFactory->filterReflectable($reflectableOrProperty) ) )
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

        if (null !== ( $reflectionParameter = $this->reflectionFactory->filterReflectionParameter($reflectableInvokableOrParameter) )) {
            $newInfo = $this->reflectionFactory->newReflectorInfoFromReflection($reflectionParameter);

            $info = $info ?? $newInfo;
            $info->sync($newInfo);

            $result = $reflectionParameter;

        } elseif (null !== $this->filter->filterWordOrInt($parameter)) {
            $result = $this->buildReflectionParameter($reflectableInvokableOrParameter, $parameter, $info);
        }

        return $result;
    }


    /**
     * @param \ReflectionParameter|\ReflectionProperty $reflectionParameterOrProperty
     *
     * @return null|\ReflectionType
     */
    public function reflectType($reflectionParameterOrProperty) : ?\ReflectionType
    {
        $reflectionType = null;

        if ($reflectionParameter = $this->reflectionFactory->filterReflectionParameter($reflectionParameterOrProperty)) {
            $reflectionType = $reflectionParameter->getType();

        } else {
            if ($reflectionProperty = $this->reflectionFactory->filterReflectionProperty($reflectionParameterOrProperty)) {
                try {
                    $reflectionType = $reflectionProperty->{'getType'}();
                }
                catch ( \Throwable $e ) {
                }
            }
        }

        return $reflectionType;
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
