<?php

namespace Gzhegow\Reflection\Tests\Services;


/**
 * MyCollection
 */
class MyCollection implements \Iterator
{
    /**
     * @var mixed
     */
    protected $items;

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * @return mixed|void
     */
    public function next()
    {
        return next($this->items);
    }

    /**
     * @return null|bool|float|int|string
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return null !== $this->key();
    }

    /**
     * @return mixed|void
     */
    public function rewind()
    {
        return reset($this->items);
    }
}
