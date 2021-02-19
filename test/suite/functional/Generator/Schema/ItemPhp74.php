<?php declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Schema;

use DateTimeInterface;
use DoclerLabs\ApiClientException\RequestValidationException;
use JsonSerializable;

class Item implements SerializableInterface, JsonSerializable
{
    public const MANDATORY_ENUM_ONE_OPTION = 'one option';

    public const MANDATORY_ENUM_ANOTHER_OPTION = 'another option';

    public const ALLOWED_MANDATORY_ENUM_LIST = [self::MANDATORY_ENUM_ONE_OPTION, self::MANDATORY_ENUM_ANOTHER_OPTION];

    public const OPTIONAL_ENUM_ONE_OPTION = 'one option';

    public const OPTIONAL_ENUM_ANOTHER_OPTION = 'another option';

    public const ALLOWED_OPTIONAL_ENUM_LIST = [self::OPTIONAL_ENUM_ONE_OPTION, self::OPTIONAL_ENUM_ANOTHER_OPTION];

    private int $mandatoryInteger;

    private string $mandatoryString;

    private string $mandatoryEnum;

    private DateTimeInterface $mandatoryDate;

    private ?DateTimeInterface $mandatoryNullableDate = null;

    private float $mandatoryFloat;

    private bool $mandatoryBoolean;

    private array $mandatoryArray;

    private ItemMandatoryObject $mandatoryObject;

    private ?ItemNullableObject $nullableObject = null;

    private ?DateTimeInterface $nullableDate = null;

    private ?int $optionalInteger = null;

    private ?string $optionalString = null;

    private ?string $optionalEnum = null;

    private ?DateTimeInterface $optionalDate = null;

    private ?float $optionalFloat = null;

    private ?bool $optionalBoolean = null;

    private ?array $optionalArray = null;

    private ?EmbeddedObject $optionalObject = null;

    /**
     * @param string[] $mandatoryArray
     *
     * @throws RequestValidationException
     */
    public function __construct(int $mandatoryInteger, string $mandatoryString, string $mandatoryEnum, DateTimeInterface $mandatoryDate, ?DateTimeInterface $mandatoryNullableDate, float $mandatoryFloat, bool $mandatoryBoolean, array $mandatoryArray, ItemMandatoryObject $mandatoryObject)
    {
        $this->mandatoryInteger = $mandatoryInteger;
        $this->mandatoryString  = $mandatoryString;
        if (! \in_array($mandatoryEnum, self::ALLOWED_MANDATORY_ENUM_LIST, true)) {
            throw new RequestValidationException(\sprintf('Invalid %s value. Given: `%s` Allowed: %s', 'mandatoryEnum', $mandatoryEnum, \json_encode(self::ALLOWED_MANDATORY_ENUM_LIST)));
        }
        $this->mandatoryEnum         = $mandatoryEnum;
        $this->mandatoryDate         = $mandatoryDate;
        $this->mandatoryNullableDate = $mandatoryNullableDate;
        $this->mandatoryFloat        = $mandatoryFloat;
        $this->mandatoryBoolean      = $mandatoryBoolean;
        $this->mandatoryArray        = $mandatoryArray;
        $this->mandatoryObject       = $mandatoryObject;
    }

    public function setNullableObject(?ItemNullableObject $nullableObject): self
    {
        $this->nullableObject = $nullableObject;

        return $this;
    }

    public function setNullableDate(?DateTimeInterface $nullableDate): self
    {
        $this->nullableDate = $nullableDate;

        return $this;
    }

    public function setOptionalInteger(int $optionalInteger): self
    {
        $this->optionalInteger = $optionalInteger;

        return $this;
    }

    public function setOptionalString(string $optionalString): self
    {
        $this->optionalString = $optionalString;

        return $this;
    }

    /**
     * @throws RequestValidationException
     */
    public function setOptionalEnum(string $optionalEnum): self
    {
        if (! \in_array($optionalEnum, self::ALLOWED_OPTIONAL_ENUM_LIST, true)) {
            throw new RequestValidationException(\sprintf('Invalid %s value. Given: `%s` Allowed: %s', 'optionalEnum', $optionalEnum, \json_encode(self::ALLOWED_OPTIONAL_ENUM_LIST)));
        }
        $this->optionalEnum = $optionalEnum;

        return $this;
    }

    public function setOptionalDate(DateTimeInterface $optionalDate): self
    {
        $this->optionalDate = $optionalDate;

        return $this;
    }

    public function setOptionalFloat(float $optionalFloat): self
    {
        $this->optionalFloat = $optionalFloat;

        return $this;
    }

    public function setOptionalBoolean(bool $optionalBoolean): self
    {
        $this->optionalBoolean = $optionalBoolean;

        return $this;
    }

    /**
     * @param string[] $optionalArray
     */
    public function setOptionalArray(array $optionalArray): self
    {
        $this->optionalArray = $optionalArray;

        return $this;
    }

    public function setOptionalObject(EmbeddedObject $optionalObject): self
    {
        $this->optionalObject = $optionalObject;

        return $this;
    }

    public function getMandatoryInteger(): int
    {
        return $this->mandatoryInteger;
    }

    public function getMandatoryString(): string
    {
        return $this->mandatoryString;
    }

    public function getMandatoryEnum(): string
    {
        return $this->mandatoryEnum;
    }

    public function getMandatoryDate(): DateTimeInterface
    {
        return $this->mandatoryDate;
    }

    public function getMandatoryNullableDate(): ?DateTimeInterface
    {
        return $this->mandatoryNullableDate;
    }

    public function getMandatoryFloat(): float
    {
        return $this->mandatoryFloat;
    }

    public function getMandatoryBoolean(): bool
    {
        return $this->mandatoryBoolean;
    }

    /**
     * @return string[]
     */
    public function getMandatoryArray(): array
    {
        return $this->mandatoryArray;
    }

    public function getMandatoryObject(): ItemMandatoryObject
    {
        return $this->mandatoryObject;
    }

    public function getNullableObject(): ?ItemNullableObject
    {
        return $this->nullableObject;
    }

    public function getNullableDate(): ?DateTimeInterface
    {
        return $this->nullableDate;
    }

    public function getOptionalInteger(): ?int
    {
        return $this->optionalInteger;
    }

    public function getOptionalString(): ?string
    {
        return $this->optionalString;
    }

    public function getOptionalEnum(): ?string
    {
        return $this->optionalEnum;
    }

    public function getOptionalDate(): ?DateTimeInterface
    {
        return $this->optionalDate;
    }

    public function getOptionalFloat(): ?float
    {
        return $this->optionalFloat;
    }

    public function getOptionalBoolean(): ?bool
    {
        return $this->optionalBoolean;
    }

    /**
     * @return string[]|null
     */
    public function getOptionalArray(): ?array
    {
        return $this->optionalArray;
    }

    public function getOptionalObject(): ?EmbeddedObject
    {
        return $this->optionalObject;
    }

    public function toArray(): array
    {
        $fields                          = [];
        $fields['mandatoryInteger']      = $this->mandatoryInteger;
        $fields['mandatoryString']       = $this->mandatoryString;
        $fields['mandatoryEnum']         = $this->mandatoryEnum;
        $fields['mandatoryDate']         = $this->mandatoryDate->format(DATE_RFC3339);
        $fields['mandatoryNullableDate'] = $this->mandatoryNullableDate !== null ? $this->mandatoryNullableDate->format(DATE_RFC3339) : null;
        $fields['mandatoryFloat']        = $this->mandatoryFloat;
        $fields['mandatoryBoolean']      = $this->mandatoryBoolean;
        $fields['mandatoryArray']        = $this->mandatoryArray;
        $fields['mandatoryObject']       = $this->mandatoryObject->toArray();
        $fields['nullableObject']        = $this->nullableObject !== null ? $this->nullableObject->toArray() : null;
        $fields['nullableDate']          = $this->nullableDate   !== null ? $this->nullableDate->format(DATE_RFC3339) : null;
        if ($this->optionalInteger !== null) {
            $fields['optionalInteger'] = $this->optionalInteger;
        }
        if ($this->optionalString !== null) {
            $fields['optionalString'] = $this->optionalString;
        }
        if ($this->optionalEnum !== null) {
            $fields['optionalEnum'] = $this->optionalEnum;
        }
        if ($this->optionalDate !== null) {
            $fields['optionalDate'] = $this->optionalDate->format(DATE_RFC3339);
        }
        if ($this->optionalFloat !== null) {
            $fields['optionalFloat'] = $this->optionalFloat;
        }
        if ($this->optionalBoolean !== null) {
            $fields['optionalBoolean'] = $this->optionalBoolean;
        }
        if ($this->optionalArray !== null) {
            $fields['optionalArray'] = $this->optionalArray;
        }
        if ($this->optionalObject !== null) {
            $fields['optionalObject'] = $this->optionalObject->toArray();
        }

        return $fields;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
