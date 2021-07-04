<?php

namespace Gzhegow\Reflection\Domain;


/**
 * ReflectionTypeParser
 */
interface ReflectionTypeParserInterface
{
    /**
     * @param string|array|\ReflectionFunction|\ReflectionMethod $reflectableInvokableOrParameter
     * @param null|int|string                                    $parameter
     *
     * @return mixed
     */
    public function extractParameterType($reflectableInvokableOrParameter, $parameter);
}
