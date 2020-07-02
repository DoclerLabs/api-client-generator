<?php

namespace Test\Schema;

use JsonSerializable;
use Countable;
use IteratorAggregate;
use ArrayIterator;
class ItemCollection implements IteratorAggregate, JsonSerializable, Countable
{
    /** @var Item[] */
    private $items;
    /**
     * @param Item[] $items
    */
    public function __construct(Item ...$items)
    {
        $this->items = $items;
    }
    /**
     * @return Item[]
    */
    public function toArray() : array
    {
        return $this->items;
    }
    /**
     * @return Item[]
    */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
    /**
     * @return Item[]
    */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }
    /**
     * @return int
    */
    public function count() : int
    {
        return count($this->toArray());
    }
    /**
     * @return Item|null
    */
    public function first()
    {
        $items = $this->toArray();
        $first = reset($items);
        if ($first === false) {
            return null;
        }
        return $first;
    }
}