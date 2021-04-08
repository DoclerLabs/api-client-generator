<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity\Constraint;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class ConstraintCollection implements IteratorAggregate
{
    private array $contraints;

    public function __construct(ConstraintInterface ...$constraints)
    {
        $this->contraints = $constraints;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->contraints);
    }
}
