<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Schema;

use JsonSerializable;

class Dog implements SerializableInterface, JsonSerializable
{
    public const BREED_DINGO = 'Dingo';

    public const BREED_HUSKY = 'Husky';

    public const BREED_RETRIEVER = 'Retriever';

    public const BREED_SHEPHERD = 'Shepherd';

    private ?bool $bark = null;

    private ?string $breed = null;

    private array $optionalPropertyChanged = ['bark' => false, 'breed' => false];

    public function setBark(bool $bark): self
    {
        $this->bark                            = $bark;
        $this->optionalPropertyChanged['bark'] = true;

        return $this;
    }

    public function setBreed(string $breed): self
    {
        $this->breed                            = $breed;
        $this->optionalPropertyChanged['breed'] = true;

        return $this;
    }

    public function hasBark(): bool
    {
        return $this->optionalPropertyChanged['bark'];
    }

    public function hasBreed(): bool
    {
        return $this->optionalPropertyChanged['breed'];
    }

    public function getBark(): ?bool
    {
        return $this->bark;
    }

    public function getBreed(): ?string
    {
        return $this->breed;
    }

    public function toArray(): array
    {
        $fields = [];
        if ($this->hasBark()) {
            $fields['bark'] = $this->bark;
        }
        if ($this->hasBreed()) {
            $fields['breed'] = $this->breed;
        }

        return $fields;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
