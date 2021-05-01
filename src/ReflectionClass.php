<?php

namespace Gzhegow\Reflection;

use Gzhegow\Reflection\Exceptions\RuntimeException;
use Gzhegow\Reflection\Exceptions\Logic\InvalidArgumentException;


/**
 * ReflectionClass
 */
class ReflectionClass extends \ReflectionClass
{
    /**
     * @var bool
     */
    protected $isUseStatementsParsed = false;

    /**
     * @var array
     */
    protected $useStatements = [];
    /**
     * @var array
     */
    protected $useStatementsIndex = [];


    /**
     * @param \ReflectionClass $reflection
     *
     * @return static
     */
    public static function fromNative(\ReflectionClass $reflection) : self
    {
        try {
            $result = new static($reflection->getName());
        }
        catch ( \ReflectionException $e ) {
            throw new RuntimeException('Unable to reflect', func_get_args(), $e);
        }

        return $result;
    }


    /**
     * @return array[][]
     */
    public function getUseStatements() : array
    {
        $this->parseUseStatements();

        return [ $this->useStatements, $this->useStatementsIndex ];
    }


    /**
     * @param string $class
     *
     * @return array[]
     */
    public function getUseStatementsByClass(string $class) : array
    {
        if ('' === $class) {
            throw new InvalidArgumentException('Class should be not empty');
        }

        $this->parseUseStatements();

        $useStatements = [];
        foreach ( $this->useStatementsIndex[ 'class' ][ $class ] as $idx ) {
            $useStatements[ $idx ] = $this->useStatements[ $idx ];
        }

        return $useStatements;
    }

    /**
     * @param string $alias
     *
     * @return null|array
     */
    public function getUseStatementByAlias(string $alias) : array
    {
        if ('' === $alias) {
            throw new InvalidArgumentException('Alias should be not empty');
        }

        $this->parseUseStatements();

        return $this->useStatements[ $this->useStatementsIndex[ 'alias' ][ $alias ] ];
    }


    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasUseStatementsByClass($class) : bool
    {
        if (! is_string($class)) return false;
        if ('' === $class) return false;

        $this->parseUseStatements();

        return isset($this->useStatementsIndex[ 'class' ][ $class ]);
    }

    /**
     * @param string $alias
     *
     * @return bool
     */
    public function hasUseStatementByAlias($alias) : bool
    {
        if (! is_string($alias)) return false;
        if ('' === $alias) return false;

        $this->parseUseStatements();

        return isset($this->useStatementsIndex[ 'alias' ][ $alias ]);
    }


    /**
     * @return array
     */
    protected function parseUseStatements() : array
    {
        if ($this->isUseStatementsParsed) {
            return $this->useStatements;
        }

        if (! $this->isUserDefined()) {
            throw new RuntimeException('Must parse use statements from user defined classes.');
        }

        $source = file_get_contents($this->getFileName());

        [ $this->useStatements, $this->useStatementsIndex ] = $this->tokenizeSource($source);

        $this->isUseStatementsParsed = true;

        return $this->useStatements;
    }


    /**
     * @param string $source
     *
     * @return array
     */
    protected function tokenizeSource($source) : array
    {
        $useStatements = [];
        $useStatementsIndex = [
            'alias' => [],
        ];

        $tokens = token_get_all($source);

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

            if (strcasecmp($builtNamespace, $this->getNamespaceName()) === 0) {
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

                            if ($record) {
                                $currentUse[ $record ] .= $token[ 1 ];
                            }

                            break;
                    }
                }
            }

            if ($token[ 2 ] >= $this->getStartLine()) {
                break;
            }
        }

        foreach ( $useStatements as $idx => $useStatement ) {
            if (empty($useStatement[ 'alias' ])) {
                $useStatement[ 'alias' ] = basename($useStatement[ 'class' ]);
            }

            $useStatements[ $idx ] = $useStatement;
            $useStatementsIndex[ 'alias' ][ $useStatement[ 'alias' ] ] = $idx;
            $useStatementsIndex[ 'class' ][ $useStatement[ 'class' ] ][] = $idx;
        }
        unset($useStatement);

        return [ $useStatements, $useStatementsIndex ];
    }
}
