<?php declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace OpenApi\PetStoreClient\Schema;

use JsonSerializable;

class Category implements SerializableInterface, JsonSerializable
{
    /** @var int|null */
    private $id;

    /** @var string|null */
    private $name;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        $fields = [];
        if ($this->id !== null) {
            $fields['id'] = $this->id;
        }
        if ($this->name !== null) {
            $fields['name'] = $this->name;
        }

        return $fields;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
