<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use ArrayIterator;
use IteratorAggregate;

class FieldCollection implements IteratorAggregate
{
    protected array $fields = [];

    public function add(Field $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function set(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function merge(FieldCollection $fieldCollection): self
    {
        $unique = [];
        foreach ($this as $field) {
            $unique[$field->getPhpClassName()] = $field;
        }

        foreach ($fieldCollection as $field) {
            $unique[$field->getPhpClassName()] = $field;
        }

        return (new self())->set(array_values($unique));
    }

    public function getUniqueByPhpClassName(): array
    {
        $unique = [];
        foreach ($this as $field) {
            $unique[$field->getPhpClassName()] = $field;
        }

        return $unique;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->fields);
    }
}
