<?php

namespace DoclerLabs\ApiClientGenerator\Entity;

use ArrayIterator;
use IteratorAggregate;

class ResponseExceptionCollection implements IteratorAggregate
{
    protected array $items = [];

    public function add(ResponseException $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function getUniqueByPhpExceptionName(): array
    {
        $unique = [];
        foreach ($this as $exceptionType) {
            $unique[$exceptionType->getPhpExceptionName()] = $exceptionType;
        }

        return $unique;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
}
