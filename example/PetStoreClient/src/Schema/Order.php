<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace OpenApi\PetStoreClient\Schema;

use DateTimeInterface;
use JsonSerializable;

class Order implements SerializableInterface, JsonSerializable
{
    public const STATUS_PLACED = 'placed';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DELIVERED = 'delivered';

    private ?int $id = null;

    private ?int $petId = null;

    private ?int $quantity = null;

    private ?DateTimeInterface $shipDate = null;

    private ?string $status = null;

    private ?bool $complete = null;

    private array $optionalPropertyChanged = ['id' => false, 'petId' => false, 'quantity' => false, 'shipDate' => false, 'status' => false, 'complete' => false];

    public function setId(int $id): self
    {
        $this->id                            = $id;
        $this->optionalPropertyChanged['id'] = true;

        return $this;
    }

    public function setPetId(int $petId): self
    {
        $this->petId                            = $petId;
        $this->optionalPropertyChanged['petId'] = true;

        return $this;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity                            = $quantity;
        $this->optionalPropertyChanged['quantity'] = true;

        return $this;
    }

    public function setShipDate(DateTimeInterface $shipDate): self
    {
        $this->shipDate                            = $shipDate;
        $this->optionalPropertyChanged['shipDate'] = true;

        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status                            = $status;
        $this->optionalPropertyChanged['status'] = true;

        return $this;
    }

    public function setComplete(bool $complete): self
    {
        $this->complete                            = $complete;
        $this->optionalPropertyChanged['complete'] = true;

        return $this;
    }

    public function hasId(): bool
    {
        return $this->optionalPropertyChanged['id'];
    }

    public function hasPetId(): bool
    {
        return $this->optionalPropertyChanged['petId'];
    }

    public function hasQuantity(): bool
    {
        return $this->optionalPropertyChanged['quantity'];
    }

    public function hasShipDate(): bool
    {
        return $this->optionalPropertyChanged['shipDate'];
    }

    public function hasStatus(): bool
    {
        return $this->optionalPropertyChanged['status'];
    }

    public function hasComplete(): bool
    {
        return $this->optionalPropertyChanged['complete'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPetId(): ?int
    {
        return $this->petId;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getShipDate(): ?DateTimeInterface
    {
        return $this->shipDate;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getComplete(): ?bool
    {
        return $this->complete;
    }

    public function toArray(): array
    {
        $fields = [];
        if ($this->hasId()) {
            $fields['id'] = $this->id;
        }
        if ($this->hasPetId()) {
            $fields['petId'] = $this->petId;
        }
        if ($this->hasQuantity()) {
            $fields['quantity'] = $this->quantity;
        }
        if ($this->hasShipDate()) {
            $fields['shipDate'] = $this->shipDate->format(DATE_RFC3339);
        }
        if ($this->hasStatus()) {
            $fields['status'] = $this->status;
        }
        if ($this->hasComplete()) {
            $fields['complete'] = $this->complete;
        }

        return $fields;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
