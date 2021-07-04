<?php

namespace Gzhegow\Reflection;

use Gzhegow\Reflection\Domain\Reflector;
use Gzhegow\Reflection\Domain\ReflectionTypeParserInterface;


/**
 * Reflection
 */
class Reflection implements ReflectionInterface
{
    /**
     * @var ReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * @var Reflector
     */
    protected $reflector;
    /**
     * @var ReflectionTypeParserInterface
     */
    protected $reflectionTypeParser;


    /**
     * Constructor
     *
     * @param ReflectionFactory             $reflectionFactory
     *
     * @param Reflector                     $reflector
     * @param ReflectionTypeParserInterface $reflectionTypeParser
     */
    public function __construct(
        ReflectionFactory $reflectionFactory,

        Reflector $reflector,
        ReflectionTypeParserInterface $reflectionTypeParser
    )
    {
        $this->reflectionFactory = $reflectionFactory;

        $this->reflector = $reflector;
        $this->reflectionTypeParser = $reflectionTypeParser;
    }


    /**
     * @return ReflectionFactory
     */
    public function getReflectionFactory() : ReflectionFactory
    {
        return $this->reflectionFactory;
    }


    /**
     * @return Reflector
     */
    public function getReflector() : Reflector
    {
        return $this->reflector;
    }

    /**
     * @return ReflectionTypeParserInterface
     */
    public function getReflectionTypeParser() : ReflectionTypeParserInterface
    {
        return $this->reflectionTypeParser;
    }
}
