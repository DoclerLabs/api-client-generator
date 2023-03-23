<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use ArrayIterator;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use IteratorAggregate;

class OperationCollection implements IteratorAggregate
{
    protected array $items          = [];
    protected array $operationNames = [];

    public function add(Operation $item): self
    {
        if (isset($this->operationNames[$item->getName()])) {
            throw new InvalidSpecificationException('Duplicated operationId found: ' . $item->getName());
        }

        $this->items[]                          = $item;
        $this->operationNames[$item->getName()] = true;

        return $this;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return ArrayIterator|Operation[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
}
