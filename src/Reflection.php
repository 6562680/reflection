<?php

namespace Gzhegow\Reflection;

use Gzhegow\Reflection\Domain\Reflector;
use Gzhegow\Reflection\Domain\ReflectionTypeParser;
use Gzhegow\Reflection\Domain\ReflectionClassUseParser;


/**
 * Reflection
 */
class Reflection implements ReflectionInterface
{
    /**
     * @var Assert
     */
    protected $assert;
    /**
     * @var Reflector
     */
    protected $reflector;
    /**
     * @var ReflectionClassUseParser
     */
    protected $reflectionClassUseParser;
    /**
     * @var ReflectionTypeParser
     */
    protected $reflectionClassTypeParser;


    /**
     * Constructor
     *
     * @param Assert                   $assert
     * @param Reflector                $reflector
     * @param ReflectionClassUseParser $reflectionClassUseParser
     * @param ReflectionTypeParser     $reflectionClassTypeParser
     */
    public function __construct(
        Assert $assert,
        Reflector $reflector,
        ReflectionClassUseParser $reflectionClassUseParser,
        ReflectionTypeParser $reflectionClassTypeParser
    )
    {
        $this->reflector = $reflector;
        $this->assert = $assert;
        $this->reflectionClassUseParser = $reflectionClassUseParser;
        $this->reflectionClassTypeParser = $reflectionClassTypeParser;
    }
}
