<?php


namespace Gzhegow\Reflection\Models\ValueObjects;


/**
 * TypeUnionVal
 */
class TypeUnionVal extends AbstractTypeVal implements
    TypeUnionInterface,
    TypeSingleInterface
{
    /**
     * @var string
     */
    protected $name = 'union';

    /**
     * @var TypeMultipleInterface[]
     */
    protected $types = [];


    /**
     * @return TypeMultipleInterface[]
     */
    public function getTypes()
    {
        return $this->types;
    }


    /**
     * @param TypeMultipleInterface[] $types
     *
     * @return static
     */
    public function setTypes(array $types)
    {
        $this->types = [];

        $this->addTypes($types);

        return $this;
    }


    /**
     * @param array $types
     *
     * @return static
     */
    public function addTypes(array $types)
    {
        foreach ( $types as $type ) {
            $this->addType($type);
        }

        return $this;
    }

    /**
     * @param TypeMultipleInterface $type
     *
     * @return static
     */
    public function addType(TypeMultipleInterface $type)
    {
        $this->types[] = $type;

        return $this;
    }
}
