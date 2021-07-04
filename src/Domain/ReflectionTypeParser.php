<?php

namespace Gzhegow\Reflection\Domain;

use Gzhegow\Support\Arr;
use Gzhegow\Support\Str;
use Gzhegow\Support\Filter;
use Gzhegow\Support\Loader;
use Gzhegow\Reflection\ReflectionFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Type as DocBlockType;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Gzhegow\Reflection\Models\ValueObjects\TypeListVal;
use Gzhegow\Reflection\Models\ValueObjects\TypeValueVal;
use Gzhegow\Reflection\Models\ValueObjects\TypeUnionVal;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;
use Gzhegow\Reflection\Exceptions\Runtime\ReflectionRuntimeException;


/**
 * ReflectionTypeParser
 */
class ReflectionTypeParser implements ReflectionTypeParserInterface
{
    /**
     * @var Arr
     */
    protected $arr;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var Loader
     */
    protected $loader;
    /**
     * @var Str
     */
    protected $str;

    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    /**
     * @var ReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * @var Reflector
     */
    protected $reflector;


    /**
     * Constructor
     *
     * @param Arr               $arr
     * @param Filter            $filter
     * @param Loader            $filter
     * @param Str               $str
     *
     * @param DocBlockFactory   $docBlockFactory
     *
     * @param ReflectionFactory $reflectionFactory
     *
     * @param Reflector         $reflector
     */
    public function __construct(
        Arr $arr,
        Filter $filter,
        Loader $loader,
        Str $str,

        DocBlockFactory $docBlockFactory,

        ReflectionFactory $reflectionFactory,

        Reflector $reflector
    )
    {
        $this->arr = $arr;
        $this->filter = $filter;
        $this->loader = $loader;
        $this->str = $str;

        $this->docBlockFactory = $docBlockFactory;

        $this->reflectionFactory = $reflectionFactory;

        $this->reflector = $reflector;
    }


    /**
     * @param string $type
     *
     * @return array
     */
    protected function buildType($type) : TypeValueVal
    {
        $type = $this->str->theWordval($type);

        $instance = new TypeValueVal();

        $typePhp = null
            ?? ( isset(static::getTypesBuiltIn()[ $type ]) ? $type : null )
            ?? null;

        $typeClass = null
            ?? ( ( ! $typePhp && class_exists($type) ) ? $type : null )
            ?? null;

        $typeAlias = null
            ?? ( $typeClass ? $this->loader->className($typeClass) : null )
            ?? null;

        $instance->setType($type);
        $instance->setPhp($typePhp);
        $instance->setClass($typeClass);
        $instance->setAlias($typeAlias);

        return $instance;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    protected function buildTypeList($type, $valueTypes, $keyTypes = null) : TypeListVal
    {
        $keyTypes = $keyTypes ?? [ 'int', 'string' ];

        $instance = new TypeListVal();

        $listType = $this->buildType($type);

        $valueType = null;
        $valueTypeUnion = null;
        if (! is_array($valueTypes)) {
            $valueType = $this->buildType($valueTypes);

        } else {
            $valueTypes = $this->str->theWordvals($valueTypes);

            $valueTypeUnion = new TypeUnionVal();

            foreach ( $valueTypes as $item ) {
                $valueTypeUnion->addType(
                    $this->buildType($item)
                );
            }
        }

        $keyType = null;
        $keyTypeUnion = null;
        if (! is_array($keyTypes)) {
            $keyType = $this->buildType($keyTypes);

        } else {
            $keyTypes = $this->str->theWordvals($keyTypes);

            $keyTypeUnion = new TypeUnionVal();

            foreach ( $keyTypes as $item ) {
                $keyTypeUnion->addType(
                    $this->buildType($item)
                );
            }
        }

        $instance->setType($listType);

        if ($valueType || $valueTypeUnion) {
            $instance->setValueType($valueType ?? $valueTypeUnion);
        }
        if ($keyType || $keyTypeUnion) {
            $instance->setKeyType($keyType ?? $keyTypeUnion);
        }

        return $instance;
    }


    // /**
    //  * @param string $type
    //  *
    //  * @return array
    //  */
    // protected function buildTypeUnion(...$types) : array
    // {
    //     return [
    //         'name'     => 'union',
    //         'children' => $typeBuiltIn,
    //         'class'    => $typeClass,
    //         'alias'    => $typeAlias,
    //     ];
    // }


    /**
     * @param Param|mixed $tag
     *
     * @return null|Param
     */
    protected function filterDocBlockTagParam($tag) : ?Param
    {
        return is_object($tag) && is_a($tag, Param::class)
            ? $tag
            : null;
    }


    /**
     * @param Compound|mixed $type
     *
     * @return null|Compound
     */
    protected function filterDocBlockTypeCompound($type) : ?Compound
    {
        return is_object($type) && is_a($type, Compound::class)
            ? $type
            : null;
    }


    /**
     * @param AbstractList|mixed $type
     *
     * @return null|AbstractList
     */
    protected function filterDocBlockTypeAbstractList($type) : ?AbstractList
    {
        return is_object($type) && is_a($type, AbstractList::class)
            ? $type
            : null;
    }

    /**
     * @param Array_|mixed $type
     *
     * @return null|Array_
     */
    protected function filterDocBlockTypeArray($type) : ?Array_
    {
        return is_object($type) && is_a($type, Array_::class)
            ? $type
            : null;
    }

    /**
     * @param Iterable_|mixed $type
     *
     * @return null|Iterable_
     */
    protected function filterDocBlockTypeIterable($type) : ?Iterable_
    {
        return is_object($type) && is_a($type, Iterable_::class)
            ? $type
            : null;
    }

    /**
     * @param Collection|mixed $type
     *
     * @return null|Collection
     */
    protected function filterDocBlockTypeCollection($type) : ?Collection
    {
        return is_object($type) && is_a($type, Collection::class)
            ? $type
            : null;
    }


    /**
     * @param Object_|mixed $type
     *
     * @return null|Object_
     */
    protected function filterDocBlockTypeObject($type) : ?Object_
    {
        return is_object($type) && is_a($type, Object_::class)
            ? $type
            : null;
    }

    /**
     * @param Mixed_|mixed $type
     *
     * @return null|Mixed_
     */
    protected function filterDocBlockTypeMixed($type) : ?Mixed_
    {
        return is_object($type) && is_a($type, Mixed_::class)
            ? $type
            : null;
    }


    /**
     * @param string|array|\ReflectionFunction|\ReflectionMethod $reflectableInvokableOrParameter
     * @param null|int|string                                    $parameter
     *
     * @return array
     */
    public function extractParameterType($reflectableInvokableOrParameter, $parameter) : array
    {
        if (! $reflectionParameter = $this->reflector->reflectParameter($reflectableInvokableOrParameter, $parameter)) {
            throw new ReflectionRuntimeException([
                [ 'Unable to reflect function parameter: %s / %s', $reflectableInvokableOrParameter, $parameter ],
            ]);
        }

        $phpTypes = [];
        $docBlockTypes = [];

        $reflectionParameterName = $reflectionParameter->getName();
        $reflectionParameterFunction = $reflectionParameter->getDeclaringFunction();

        $reflectionClass = null
            ?? ( ( $reflectionMethod = $this->reflectionFactory->filterReflectionMethod($reflectionParameterFunction) )
                ? $reflectionMethod->getDeclaringClass()
                : null
            )
            ?? $reflectionParameterFunction->getClosureScopeClass()
            ?? null;

        $phpClassTypes = [];
        if ($reflectionType = $this->reflector->reflectType($reflectionParameter)) {
            [ $phpTypes, $phpClassTypes ] = $this->parsePhpType($reflectionType, $reflectionClass);
        }

        if ($reflectionParameterFunctionDocBlock = $reflectionParameterFunction->getDocComment()) {
            $docBlock = $this->docBlockFactory->create($reflectionParameterFunctionDocBlock);

            foreach ( $docBlock->getTagsByName('param') as $param ) {
                if (! $docBlockParam = $this->filterDocBlockTagParam($param)) {
                    continue;
                }

                if (! $docBlockType = $docBlockParam->getType()) {
                    continue;
                }

                if ($reflectionParameterName !== $docBlockParam->getVariableName()) {
                    continue;
                }

                $docBlockTypes = $this->parseDocBlockType($docBlockType, $reflectionClass);

                break;
            }
        }

        ( ! $phpClassTypes )
            ? ( $types = $docBlockTypes )
            : ( $types = $phpTypes );

        $types = $types ?: $phpTypes;

        return [
            'types'         => $types,
            'phpTypes'      => $phpTypes,
            'docBlockTypes' => $docBlockTypes,
        ];
    }

    /**
     * @param string|array|\ReflectionFunction|\ReflectionMethod $reflectableInvokableOrParameter
     * @param null|int|string                                    $parameter
     *
     * @return mixed
     */
    public function extractPropertyType($reflectable, $property)
    {
        if (! $reflectionProperty = $this->reflector->reflectProperty($reflectable, $property)) {
            throw new ReflectionRuntimeException([
                [ 'Unable to reflect property: %s / %s', $reflectable, $property ],
            ]);
        }

        $phpTypes = [];
        $docBlockTypes = [];

        $reflectionPropertyName = $reflectionProperty->getName();

        $reflectionClass = $reflectionProperty->getDeclaringClass();

        $phpClassTypes = [];
        if ($reflectionType = $this->reflector->reflectType($reflectionProperty)) {
            [ $phpTypes, $phpClassTypes ] = $this->parsePhpType($reflectionType, $reflectionClass);
        }

        if ($reflectionPropertyDocBlock = $reflectionProperty->getDocComment()) {
            $docBlock = $this->docBlockFactory->create($reflectionPropertyDocBlock);

            foreach ( $docBlock->getTagsByName('var') as $param ) {
                if (! $docBlockParam = $this->filterDocBlockTagParam($param)) {
                    continue;
                }

                if (! $docBlockType = $docBlockParam->getType()) {
                    continue;
                }

                if ($reflectionPropertyName !== $docBlockParam->getVariableName()) {
                    continue;
                }

                $docBlockTypes = $this->parseDocBlockType($docBlockType, $reflectionClass);

                break;
            }
        }

        ( ! $phpClassTypes )
            ? ( $types = $docBlockTypes )
            : ( $types = $phpTypes );

        $types = $types ?: $phpTypes;

        return [
            'types'         => $types,
            'phpTypes'      => $phpTypes,
            'docBlockTypes' => $docBlockTypes,
        ];
    }


    /**
     * @param \ReflectionType $reflectionType
     *
     * @return array
     */
    protected function parsePhpType(\ReflectionType $reflectionType, \ReflectionClass $reflectionClass = null) : array
    {
        $isReflectionUnionType = null !== ( $typeUnion = $this->reflectionFactory->filterReflectionUnionType($reflectionType) );

        $isReflectionUnionType
            ? ( $result = $this->parsePhpTypeUnion($typeUnion, $reflectionClass) )
            : ( $result = $this->parsePhpTypeSingle($reflectionType, $reflectionClass) );

        return $result;
    }

    /**
     * @param \ReflectionUnionType $reflectionUnionType
     *
     * @return array
     */
    protected function parsePhpTypeUnion(\ReflectionType $reflectionUnionType, \ReflectionClass $reflectionClass = null) : array
    {
        if (null === $this->reflectionFactory->filterReflectionUnionType($reflectionUnionType)) {
            throw new InvalidArgumentException('Invalid ReflectionUnion type: %s', $reflectionUnionType);
        }

        $result = [];

        $classTypes = [];

        $queue = [ $reflectionUnionType ];
        $pathes = [ [] ];

        while ( null !== key($queue) ) {
            $type = array_shift($queue);
            $path = array_shift($pathes);

            foreach ( $type->getTypes() as $idx => $type ) {
                if (null !== ( $typeUnion = $this->reflectionFactory->filterReflectionUnionType($type) )) {
                    $fullpath = $path;
                    $fullpath[] = $idx;

                    $queue[] = $typeUnion;
                    $pathes[] = $fullpath;

                } else {
                    [ $parsed, $parsedClassTypes ] = $this->parsePhpTypeSingle($type, $reflectionClass);

                    $classTypes = array_merge($classTypes, $parsedClassTypes);

                    $this->arr->set($result, $path, $parsed);
                }
            }
        }

        return [ $result, $classTypes ];
    }

    /**
     * @param \ReflectionType $reflectionType
     *
     * @return array
     */
    protected function parsePhpTypeSingle(\ReflectionType $reflectionType, \ReflectionClass $reflectionClass = null) : array
    {
        $result = [];

        $classTypes = [];

        if ($typeUnion = $this->reflectionFactory->filterReflectionUnionType($reflectionType)) {
            [ $result, $classTypes ] = $this->parsePhpTypeUnion($typeUnion, $reflectionClass);

        } else {
            if ($reflectionType->allowsNull()) {
                $result[] = $this->buildType('null');
            }

            if (null === ( $typeNamed = $this->reflectionFactory->filterReflectionNamedType($reflectionType) )) {
                $result[] = $this->buildType('mixed');

            } else {
                $typeName = ltrim($typeNamed->getName(), '\\');

                if ($typeNamed->isBuiltin()) {
                    ( 'array' === $typeName )
                        ? ( $result[] = $this->buildTypeList($typeName, 'mixed') )
                        : ( $result[] = $this->buildType($typeName) );

                } else {
                    $typeName = null
                        ?? $this->loader->useClassVal($typeName, $reflectionClass)
                        ?? $typeName;

                    $classTypes[] = $typeName;

                    is_a($typeName, \Traversable::class, true)
                        ? ( $result[] = $this->buildTypeList($typeName, 'mixed') )
                        : ( $result[] = $this->buildType($typeName) );
                }
            }
        }

        return [ $result, $classTypes ];
    }


    /**
     * @param \ReflectionType $docBlockType
     *
     * @return array
     */
    protected function parseDocBlockType(DocBlockType $docBlockType, \ReflectionClass $reflectionClass = null)
    {
        $isCompound = null !== $docBlockTypeCompound = $this->filterDocBlockTypeCompound($docBlockType);

        $isCompound
            ? ( $result = $this->parseDocBlockTypeCompound($docBlockTypeCompound, $reflectionClass) )
            : ( $result = $this->parseDocBlockTypeSingle($docBlockType, $reflectionClass) );

        return $result;
    }

    /**
     * @param Compound $docBlockTypeCompound
     *
     * @return array
     */
    protected function parseDocBlockTypeCompound(Compound $docBlockTypeCompound, \ReflectionClass $reflectionClass = null) : array
    {
        $result = [];

        $queue = [ $docBlockTypeCompound ];
        $pathes = [ [] ];

        while ( null !== key($queue) ) {
            $current = array_shift($queue);
            $currentPath = array_shift($pathes);

            foreach ( $current->getIterator() as $idx => $type ) {
                if (null !== ( $typeCompound = $this->filterDocBlockTypeCompound($type) )) {
                    $fullpath = $currentPath;
                    $fullpath[] = $idx;

                    $queue[] = $typeCompound;
                    $pathes[] = $fullpath;

                } else {
                    $parsed = $this->parseDocBlockTypeSingle($type, $reflectionClass);

                    $currentPath
                        ? $this->arr->set($result, $currentPath, $parsed)
                        : ( $result[] = $parsed );
                }
            }
        }

        return $result;
    }

    /**
     * @param DocBlockType $docBlockType
     *
     * @return array
     */
    protected function parseDocBlockTypeSingle(DocBlockType $docBlockType, \ReflectionClass $reflectionClass = null) : object
    {
        if ($docBlockTypeCompound = $this->filterDocBlockTypeCompound($docBlockType)) {
            $result = $this->parseDocBlockTypeCompound($docBlockTypeCompound, $reflectionClass);

        } elseif ($docBlockTypeList = $this->filterDocBlockTypeAbstractList($docBlockType)) {
            $keyType = $this->parseDocBlockType($docBlockTypeList->getKeyType(), $reflectionClass);
            $valueType = $this->parseDocBlockType($docBlockTypeList->getValueType(), $reflectionClass);

            if (! $docBlockTypeCollection = $this->filterDocBlockTypeCollection($docBlockType)) {
                $typeName = (string) $docBlockType;

            } else {
                $typeName = trim($docBlockTypeCollection->getFqsen()->getName(), '\\');
                $typeName = null
                    ?? $this->loader->useClassVal($typeName, $reflectionClass)
                    ?? $typeName;
            }

            throw \RuntimeException('Here');
            $result = [
                'type'      => $typeName,
                'keyType'   => $keyType,
                'valueType' => $valueType,
            ];

        } else {
            if (! $docBlockTypeObject = $this->filterDocBlockTypeObject($docBlockType)) {
                $typeName = (string) $docBlockType;

            } else {
                $typeName = trim($docBlockTypeObject->getFqsen()->getName(), '\\');
                $typeName = null
                    ?? $this->loader->useClassVal($typeName, $reflectionClass)
                    ?? $typeName;
            }

            $result = $this->buildType($typeName);
        }

        return $result;
    }


    /**
     * @return bool[]
     */
    protected static function getTypesBuiltIn() : array
    {
        return [
            'bool'     => true,
            'int'      => true,
            'float'    => true,
            'string'   => true,
            'array'    => true,
            'object'   => true,
            'callable' => true,
            'iterable' => true,
            'resource' => true,
            'null'     => true,
        ];
    }
}
