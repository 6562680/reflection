<?php

namespace Gzhegow\Reflection\Domain;


use Gzhegow\Support\Arr;
use Gzhegow\Support\Str;
use Gzhegow\Support\Filter;
use Gzhegow\Reflection\Assert;
use Symfony\Component\PropertyInfo\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;


/**
 * ReflectionTypeParser
 */
class ReflectionTypeParser
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
     * @var Str
     */
    protected $str;

    /**
     * @var Reflector
     */
    protected $reflector;

    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;
    /**
     * @var PropertyInfoExtractorInterface
     */
    protected $extractor;
    /**
     * @var Assert
     */
    protected $assert;


    /**
     * Constructor
     *
     * @param Arr       $arr
     * @param Filter    $filter
     * @param Str       $str
     *
     * @param Assert    $assert
     * @param Reflector $reflector
     */
    public function __construct(
        Arr $arr,
        Filter $filter,
        Str $str,

        Assert $assert,
        Reflector $reflector
    )
    {
        $this->arr = $arr;
        $this->filter = $filter;
        $this->str = $str;

        $this->assert = $assert;
        $this->reflector = $reflector;

        $this->docBlockFactory = $this->newDocBlockFactory();
        $this->extractor = $this->newPropertyInfoExtractor();
    }


    /**
     * @return DocBlockFactory
     */
    public function newDocBlockFactory() : DocBlockFactory
    {
        return DocBlockFactory::createInstance();
    }

    /**
     * @return PropertyInfoExtractorInterface
     */
    public function newPropertyInfoExtractor() : PropertyInfoExtractorInterface
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $listExtractors = [ $reflectionExtractor ];
        $typeExtractors = [ $phpDocExtractor, $reflectionExtractor ];

        $extractor = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
        );

        return $extractor;
    }


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
    protected function filterDocBlockTypeList($type) : ?AbstractList
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
     * @return mixed
     */
    public function extractParameterType($reflectableInvokableOrParameter, $parameter)
    {
        $result = [];

        $reflectionParameter = $this->reflector->reflectParameter($reflectableInvokableOrParameter, $parameter);
        $reflectionParameterName = $reflectionParameter->getName();
        $reflectionParameterType = $reflectionParameter->getType();

        $reflectionFunction = $reflectionParameter->getDeclaringFunction();
        $reflectionFunctionDocBlock = $reflectionFunction->getDocComment();

        $phpTypes = [];
        if ($reflectionParameterType) {
            if ($reflectionParameterType->allowsNull()) {
                $phpTypes[ 0 ] = 'null';
            }

            if (null !== ( $reflectionNamedType = $this->assert->filterReflectionNamedType($reflectionParameterType) )) {
                $phpTypes[ 1 ] = $reflectionNamedType->getName();

            } else {
                $phpTypes[ 1 ] = 'mixed';
            }
        }

        $docBlockTypes = [];
        if ($reflectionFunctionDocBlock) {
            $functionDocBlock = $this->docBlockFactory->create($reflectionFunction->getDocComment());

            foreach ( $functionDocBlock->getTagsByName('param') as $param ) {
                if (null === ( $docBlockParam = $this->filterDocBlockTagParam($param) )) {
                    continue;
                }

                if ($reflectionParameterName !== $docBlockParam->getVariableName()) {
                    continue;
                }

                if (null === ( $docBlockType = $docBlockParam->getType() )) {
                    continue;
                }

                if (null !== ( $docBlockTypeCompound = $this->filterDocBlockTypeCompound($docBlockType) )) {
                    foreach ( $docBlockTypeCompound->getIterator() as $type ) {
                        $docBlockTypes[] = $type;
                    }
                } else {
                    $docBlockTypes[] = $docBlockType;
                }

                foreach ( $docBlockTypes as $idx => $docBlockType ) {
                    // if (null !== ( $docBlockTypeList = $this->filterDocBlockTypeList($docBlockType) )) {
                    //     if (null !== ( $docBlockTypeCollection = $this->filterDocBlockTypeCollection($docBlockType) )) {
                    //         $type = $docBlockTypeCollection->getFqsen()->getName();
                    //     }
                    //
                    //     foreach ($docBlockTypeList->getKeyType())
                    // } elseif (null !== ( $docBlockTypeObject = $this->filterDocBlockTypeObject($docBlockType) )) {
                    // } elseif (null !== ( $docBlockTypeMixed = $this->filterDocBlockTypeMixed($docBlockType) )) {
                    // }
                    $docBlockTypes[ $idx ] = (string) $docBlockType;
                }
            }
        }

        dump([ $phpTypes, $docBlockTypes ]);

        return $result;
    }
    

    /**
     * @param string|object|\ReflectionClass|\ReflectionProperty $reflectableOrProperty
     * @param string                                             $propertyName
     *
     * @return string
     */
    public function extractPropertyType($reflectableOrProperty, string $propertyName) : string
    {
        $parsedTypes = $this->extractPropertyTypesArray($reflectableOrProperty, $propertyName);

        $result = implode('|', $this->compactTypes($parsedTypes));

        return $result;
    }

    /**
     * @param string|object|\ReflectionClass|\ReflectionProperty $reflectableOrProperty
     * @param string                                             $propertyName
     *
     * @return array
     */
    public function extractPropertyTypes($reflectableOrProperty, string $propertyName) : array
    {
        $parsedTypes = $this->extractPropertyTypesArray($reflectableOrProperty, $propertyName);

        $result = $this->compactTypes($parsedTypes);

        return $result;
    }

    /**
     * @param string|object|\ReflectionClass|\ReflectionProperty $reflectableOrProperty
     * @param string                                             $propertyName
     *
     * @return array
     */
    public function extractPropertyTypesArray($reflectableOrProperty, string $propertyName) : array
    {
        $reflectionProperty = $this->reflector->reflectProperty($reflectableOrProperty, $propertyName);

        $class = $reflectionProperty->getDeclaringClass()->getName();
        $propertyName = $reflectionProperty->getName();

        $propertyTypes = $this->extractor->getTypes($class, $propertyName);

        $types = [];
        foreach ( $propertyTypes as $idx => $propertyType ) {
            $types += $this->parseType($propertyType, $idx);
        }

        return $types;
    }


    /**
     * @param Type $type
     * @param int  $idx
     *
     * @return array
     */
    protected function parseType(Type $type, int $idx) : array
    {
        $result = [];

        $queue = [ $type ];
        $pathes = [ [ $idx ] ];

        $flatten = [];
        while ( null !== key($queue) ) {
            $current = array_shift($queue);
            $currentPath = array_shift($pathes);

            $path = $currentPath;

            $name = $this->parseTypeName($current);

            if ($current->isCollection()) {
                $keyPath = $valuePath = $currentPath;

                $path[] = 'type';
                $keyPath[] = 'key';
                $valuePath[] = 'val';

                $queue[] = $current->getCollectionKeyType();
                $pathes[] = $keyPath;

                $queue[] = $current->getCollectionValueType();
                $pathes[] = $valuePath;
            }

            $flatten[] = [ $path, $name ];
        }

        foreach ( $flatten as [ $fullpath, $name ] ) {
            $this->arr->set($result, $fullpath, $name);
        }

        return $result;
    }

    /**
     * @param Type $type
     *
     * @return string
     */
    protected function parseTypeName(Type $type) : string
    {
        $result = $type->getBuiltinType();

        if ('object' === $result) {
            $result = ( null !== ( $class = $type->getClassName() ) )
                ? '\\' . $class
                : $result;
        }

        return $result;
    }


    /**
     * @param array $parsedTypes
     *
     * @return array
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function compactTypes(array $parsedTypes) : array
    {
        $result = [];

        foreach ( $parsedTypes as $idx => $parsedType ) {
            if (is_string($parsedType)) {
                $result[ $idx ] = $parsedType;

            } else {
                $std = json_decode(json_encode($parsedType, JSON_FORCE_OBJECT));

                $it = new \RecursiveArrayIterator($std, \RecursiveArrayIterator::ARRAY_AS_PROPS);
                $iit = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

                foreach ( $iit as $key => $val ) {
                    if (is_object($val)) {
                        $iit->getInnerIterator()->offsetSet($key, $this->compactTypeName($val));
                    }
                }

                $result[ $idx ] = $this->compactTypeName($std);
            }
        }

        return $result;
    }

    /**
     * @param \StdClass $parsedType
     *
     * @return string
     */
    protected function compactTypeName(\StdClass $parsedType) : string
    {
        if ($parsedType->type === 'array' && $parsedType->key === 'int') {
            $result = $parsedType->val . '[]';

        } else {
            $result = $parsedType->type . '<' . $parsedType->key . ',' . $parsedType->val . '>';
        }

        return $result;
    }
}
