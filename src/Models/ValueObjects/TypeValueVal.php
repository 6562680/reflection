<?php


namespace Gzhegow\Reflection\Models\ValueObjects;


/**
 * TypeValueVal
 */
class TypeValueVal extends AbstractTypeVal implements
    TypeValueInterface,
    TypeSingleInterface,
    TypeMultipleInterface
{
    /**
     * @var string
     */
    protected $name = 'single';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $php;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $alias;


    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPhp() : string
    {
        return $this->php;
    }

    /**
     * @return string
     */
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getAlias() : string
    {
        return $this->alias;
    }


    /**
     * @param string $type
     *
     * @return static
     */
    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $php
     *
     * @return static
     */
    public function setPhp(?string $php)
    {
        $this->php = $php;

        return $this;
    }

    /**
     * @param string $class
     *
     * @return static
     */
    public function setClass(?string $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string $alias
     *
     * @return static
     */
    public function setAlias(?string $alias)
    {
        $this->alias = $alias;

        return $this;
    }
}
