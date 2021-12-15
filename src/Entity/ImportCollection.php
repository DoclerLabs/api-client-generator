<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

class ImportCollection
{
    protected array $items = [];

    public function add(string $item): self
    {
        $this->items[$item] = $item;

        return $this;
    }

    public function append(self $anotherCollection): self
    {
        $this->items += $anotherCollection->items;

        return $this;
    }

    public function toArray(): array
    {
        return array_unique($this->items);
    }
}
