<?php


namespace Gzhegow\Reflection\Models\ValueObjects;


/**
 * TypeListVal
 */
class TypeListVal extends AbstractTypeVal implements
    TypeListInterface,
    TypeMultipleInterface
{
    /**
     * @var string
     */
    protected $name = 'list';

    /**
     * @var TypeValueInterface
     */
    protected $type;
    /**
     * @var TypeSingleInterface
     */
    protected $keyType;
    /**
     * @var TypeSingleInterface
     */
    protected $valueType;


    /**
     * @return TypeValueInterface
     */
    public function getType() : TypeValueInterface
    {
        return $this->type;
    }

    /**
     * @return TypeSingleInterface
     */
    public function getKeyType() : TypeSingleInterface
    {
        return $this->keyType;
    }

    /**
     * @return TypeSingleInterface
     */
    public function getValueType() : TypeSingleInterface
    {
        return $this->valueType;
    }


    /**
     * @param TypeValueInterface $type
     *
     * @return static
     */
    public function setType(?TypeValueInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param TypeSingleInterface $keyType
     *
     * @return static
     */
    public function setKeyType(?TypeSingleInterface $keyType)
    {
        $this->keyType = $keyType;

        return $this;
    }

    /**
     * @param TypeSingleInterface $valueType
     *
     * @return static
     */
    public function setValueType(?TypeSingleInterface $valueType)
    {
        $this->valueType = $valueType;

        return $this;
    }
}
