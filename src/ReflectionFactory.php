<?php

namespace Gzhegow\Reflection;

use Gzhegow\Support\Filter;
use Psr\Container\ContainerInterface;
use Gzhegow\Reflection\Domain\Reflector;
use Gzhegow\Support\Domain\SupportFactory;
use Gzhegow\Reflection\Domain\ReflectorInfo;
use phpDocumentor\Reflection\DocBlockFactory;
use Gzhegow\Reflection\Domain\ReflectionTypeParser;
use Gzhegow\Support\Domain\SupportFactoryInterface;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Gzhegow\Reflection\Domain\ReflectionTypeParserInterface;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;
use Gzhegow\Reflection\Exceptions\Runtime\ReflectionRuntimeException;


/**
 * ReflectionFactory
 */
class ReflectionFactory
{
    /**
     * @var null|ContainerInterface
     */
    protected $container;

    /**
     * @var Filter
     */
    protected $filter;


    /**
     * Constructor
     *
     * @param null|ContainerInterface $container
     * @param Filter                  $filter
     */
    public function __construct(?ContainerInterface $container,
        Filter $filter
    )
    {
        $this->container = $container;

        $this->filter = $filter;
    }


    /**
     * @return Reflector
     */
    public function newReflector() : Reflector
    {
        $supportFactory = $this->getSupportFactory();

        return new Reflector(
            $supportFactory->getFilter(),
            $supportFactory->getPhp(),

            $this
        );
    }


    /**
     * @return ReflectionTypeParser
     */
    public function newReflectionTypeParser() : ReflectionTypeParser
    {
        $supportFactory = $this->getSupportFactory();

        return new ReflectionTypeParser(
            $supportFactory->getArr(),
            $supportFactory->getFilter(),
            $supportFactory->getLoader(),
            $supportFactory->getStr(),

            $this->getDocBlockFactory(),

            $this,

            $this->newReflector()
        );
    }


    /**
     * @return ReflectionInterface
     */
    public function newReflection() : ReflectionInterface
    {
        return new Reflection(
            $this,

            $this->newReflector(),
            $this->getReflectionTypeParser()
        );
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

        if (null !== ( $reflectionClass = $this->filterReflectionClass($reflection) )) {
            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== ( $reflectionMethod = $this->filterReflectionMethod($reflection) )) {
            $reflectionClass = $reflectionMethod->getDeclaringClass();

            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== ( $reflectionProperty = $this->filterReflectionProperty($reflection) )) {
            $reflectionClass = $reflectionProperty->getDeclaringClass();

            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== ( $reflectionParameter = $this->filterReflectionParameter($reflection) )) {
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

        if (null !== ( $reflectionClass = $this->filterReflectionClass($reflectable) )) {
            $info->setReflectionClass($reflectionClass);
            $info->setClass($reflectionClass->getName());

        } elseif (null !== $this->filterReflectableInstance($reflectable)) {
            $info->setObject($reflectable);
            $info->setClass(get_class($reflectable));

        } elseif (null !== $this->filterReflectableClass($reflectable)) {
            $info->setClass($reflectable);

        } elseif (null !== $this->filterReflectableTrait($reflectable)) {
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
     * @return SupportFactory
     */
    public function getSupportFactory() : SupportFactory
    {
        return null
            ?? $this->containerGet(SupportFactoryInterface::class)
            ?? SupportFactory::getInstance();
    }

    /**
     * @return DocBlockFactory
     */
    public function getDocBlockFactory() : DocBlockFactory
    {
        return null
            ?? $this->containerGet(DocBlockFactoryInterface::class)
            ?? DocBlockFactory::createInstance();
    }


    /**
     * @return ReflectionTypeParserInterface
     */
    public function getReflectionTypeParser() : ReflectionTypeParserInterface
    {
        return null
            ?? $this->containerGet(ReflectionTypeParserInterface::class)
            ?? $this->newReflectionTypeParser();
    }


    /**
     * @param mixed $reflectionClass
     *
     * @return null|\ReflectionClass
     */
    public function filterReflectionClass($reflectionClass) : ?\ReflectionClass
    {
        if (is_a($reflectionClass, \ReflectionClass::class)) {
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
        if (is_a($reflectionFunction, \ReflectionFunction::class)) {
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
        if (is_a($reflectionMethod, \ReflectionMethod::class)) {
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
        if (is_a($reflectionProperty, \ReflectionProperty::class)) {
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
        if (is_a($reflectionParameter, \ReflectionParameter::class)) {
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
        if (is_a($reflectionType, \ReflectionType::class)) {
            return $reflectionType;
        }

        return null;
    }

    /**
     * @param mixed $reflectionType
     *
     * @return null|\ReflectionUnionType
     */
    public function filterReflectionUnionType($reflectionType) : ?object
    {
        if (is_a($reflectionType, 'ReflectionUnionType')) {
            return $reflectionType;
        }

        return null;
    }

    /**
     * @param mixed $reflectionType
     *
     * @return null|\ReflectionNamedType
     */
    public function filterReflectionNamedType($reflectionType) : ?object
    {
        if (is_a($reflectionType, 'ReflectionNamedType')) {
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


    /**
     * @param string $id
     *
     * @return mixed
     */
    protected function containerGet(string $id)
    {
        return $this->container && $this->container->has($id)
            ? $this->container->get($id)
            : null;
    }
}
