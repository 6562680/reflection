<?php

namespace Gzhegow\Reflection\Domain;

use Gzhegow\Reflection\Exceptions\Runtime\OutOfBoundsException;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;
use Gzhegow\Reflection\Exceptions\Runtime\ReflectionRuntimeException;


/**
 * ReflectionClassUseParser
 */
class ReflectionClassUseParser
{
    /**
     * @var Reflector
     */
    protected $reflector;


    /**
     * @var array
     */
    protected $loaded = [];

    /**
     * @var array
     */
    protected $useStatements = [];

    /**
     * @var array
     */
    protected $useStatementsIndex = [
        'class'       => [],
        'class.class' => [],
    ];
    /**
     * @var array
     */
    protected $useStatementsUniq = [
        'class.alias' => [],
    ];


    /**
     * Constructor
     *
     * @param Reflector $reflector
     */
    public function __construct(
        Reflector $reflector
    )
    {
        $this->reflector = $reflector;
    }


    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return static
     * @throws ReflectionRuntimeException
     */
    protected function loadUseStatements(\ReflectionClass $reflectionClass)
    {
        if (! $reflectionClass->isUserDefined()) {
            throw new ReflectionRuntimeException('Unable to parse use statements from internal class');
        }

        $reflectionClassName = $reflectionClass->getName();

        if (isset($this->loaded[ $reflectionClassName ])) {
            return $this;
        }
        $this->loaded[ $reflectionClassName ] = true;

        $useStatements = [];

        $tokens = token_get_all(
            file_get_contents($reflectionClass->getFileName())
        );

        $builtNamespace = '';
        $buildingNamespace = false;
        $matchedNamespace = false;

        $record = false;
        $currentUse = [
            'class' => '',
            'alias' => '',
        ];

        foreach ( $tokens as $token ) {
            if ($token[ 0 ] === T_NAMESPACE) {
                $buildingNamespace = true;

                if ($matchedNamespace) {
                    break;
                }
            }

            if ($buildingNamespace) {
                if ($token === ';') {
                    $buildingNamespace = false;
                    continue;
                }

                switch ( $token[ 0 ] ) {
                    case T_STRING:
                    case T_NS_SEPARATOR:
                        $builtNamespace .= $token[ 1 ];
                        break;
                }

                continue;
            }

            if ($token === ';' || ! is_array($token)) {
                if ($record) {
                    $useStatements[] = $currentUse;

                    $record = false;
                    $currentUse = [
                        'class' => '',
                        'alias' => '',
                    ];
                }

                continue;
            }

            if ($token[ 0 ] === T_CLASS) {
                break;
            }

            if (strcasecmp($builtNamespace, $reflectionClass->getNamespaceName()) === 0) {
                $matchedNamespace = true;
            }

            if ($matchedNamespace) {
                if ($token[ 0 ] === T_USE) {
                    $record = 'class';
                }

                if ($token[ 0 ] === T_AS) {
                    $record = 'alias';
                }

                if ($record) {
                    switch ( $token[ 0 ] ) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $currentUse[ $record ] .= $token[ 1 ];

                            break;
                    }
                }
            }

            if ($token[ 2 ] >= $reflectionClass->getStartLine()) {
                break;
            }
        }

        foreach ( $useStatements as $idx => $useStatement ) {
            if (empty($useStatement[ 'alias' ])) {
                $useStatement[ 'alias' ] = basename($useStatement[ 'class' ]);
            }

            $this->useStatements[] = $useStatement;

            end($this->useStatements);
            $idx = key($this->useStatements);

            $this->useStatementsUniq[ 'class.alias' ][ $reflectionClassName . '.' . $useStatement[ 'alias' ] ] = $idx;

            $this->useStatementsIndex[ 'class' ][ $reflectionClassName ][ $idx ] = true;
            $this->useStatementsIndex[ 'class.class' ][ $reflectionClassName . '.' . $useStatement[ 'class' ] ][ $idx ] = true;
        }

        return $this;
    }


    /**
     * @return array[][]
     */
    public function getUseStatements() : array
    {
        return $this->useStatements;
    }

    /**
     * @param $reflectable
     *
     * @return array[][]
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function getUseStatementsFor($reflectable) : array
    {
        $result = [];

        $reflectionClass = $this->reflector->reflectClass($reflectable);

        $this->loadUseStatements($reflectionClass);

        $useStatementsByClass = $this->useStatementsIndex[ 'class' ][ $reflectionClass->getName() ] ?? [];

        foreach ( $useStatementsByClass as $idx ) {
            $result[] = $this->useStatements[ $idx ];
        }

        return $result;
    }

    /**
     * @param mixed  $reflectable
     * @param string $aliasOrClassName
     *
     * @return null|array
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws ReflectionRuntimeException
     */
    public function getUseStatementByAlias($reflectable, string $aliasOrClassName) : array
    {
        if (null === ( $use = $this->useStatementByAlias($reflectable, $aliasOrClassName) )) {
            throw new OutOfBoundsException('No use statements for given alias/className');
        }

        return $use;
    }


    /**
     * @param mixed  $reflectable
     * @param string $class
     *
     * @return array[]
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws ReflectionRuntimeException
     */
    public function getUseStatementsByClass($reflectable, string $class) : array
    {
        if ([] === ( $uses = $this->useStatementsByClass($reflectable, $class) )) {
            throw new OutOfBoundsException('No use statements for given class');
        }

        return $uses;
    }

    /**
     * @param mixed  $reflectable
     * @param string $alias
     *
     * @return bool
     */
    public function hasUseStatementByAlias($reflectable, $alias) : bool
    {
        if (! is_string($alias)) return false;
        if ('' === $alias) return false;

        try {
            $reflectionClass = $this->reflector->reflectClass($reflectable);
            $reflectionClassName = $reflectionClass->getName();

            $this->loadUseStatements($reflectionClass);
        }
        catch ( \Exception $e ) {
            return false;
        }

        return isset($this->useStatementsUniq[ 'class.alias' ][ $reflectionClassName . '.' . $alias ]);
    }

    /**
     * @param mixed  $reflectable
     * @param string $class
     *
     * @return bool
     */
    public function hasUseStatementsByClass($reflectable, $class) : bool
    {
        if (! is_string($class)) return false;
        if ('' === $class) return false;

        try {
            $reflectionClass = $this->reflector->reflectClass($reflectable);
            $reflectionClassName = $reflectionClass->getName();

            $this->loadUseStatements($reflectionClass);
        }
        catch ( \Exception $e ) {
            return false;
        }

        return isset($this->useStatementsIndex[ 'class.class' ][ $reflectionClassName . '.' . $class ]);
    }

    /**
     * @param mixed  $reflectable
     * @param string $alias
     *
     * @return null|array
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function useStatementByAlias($reflectable, string $alias) : ?array
    {
        if ('' === $alias) {
            throw new InvalidArgumentException('Alias should be not empty');
        }

        $reflectionClass = $this->reflector->reflectClass($reflectable);
        $reflectionClassName = $reflectionClass->getName();

        $this->loadUseStatements($reflectionClass);

        $idx = $this->useStatementsUniq[ 'class.alias' ][ $reflectionClassName . '.' . $alias ] ?? null;

        $result = $this->useStatements[ $idx ];

        return $result;
    }

    /**
     * @param mixed  $reflectable
     * @param string $class
     *
     * @return array[]
     * @throws InvalidArgumentException
     * @throws ReflectionRuntimeException
     */
    public function useStatementsByClass($reflectable, string $class) : ?array
    {
        if ('' === $class) {
            throw new InvalidArgumentException('Class should be not empty');
        }

        $reflectionClass = $this->reflector->reflectClass($reflectable);
        $reflectionClassName = $reflectionClass->getName();

        $this->loadUseStatements($reflectionClass);

        $list = $this->useStatementsIndex[ 'class.class' ][ $reflectionClassName . '.' . $class ] ?? [];

        $result = [];
        foreach ( $list as $idx => $bool ) {
            $result[] = $this->useStatements[ $idx ];
        }

        return $result;
    }
}
