<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Schema\Mapper;

use DateTimeImmutable;
use DoclerLabs\ApiClientException\UnexpectedResponseBodyException;
use Test\Schema\Item;
use Test\Schema\ItemMandatoryEnum;
use Test\Schema\ItemOptionalEnum;

class ItemMapper implements SchemaMapperInterface
{
    public function __construct(private readonly EmbeddedObjectMapper $embeddedObjectMapper, private readonly EmbeddedNullableObjectMapper $embeddedNullableObjectMapper)
    {
    }

    /**
     * @throws UnexpectedResponseBodyException
     */
    public function toSchema(array $payload): Item
    {
        $missingFields = implode(', ', array_diff(['mandatoryInteger', 'mandatoryString', 'mandatoryEnum', 'mandatoryDate', 'mandatoryNullableDate', 'mandatoryFloat', 'mandatoryBoolean', 'mandatoryArray', 'mandatoryObject', 'mandatoryNullableObject'], array_keys($payload)));
        if (!empty($missingFields)) {
            throw new UnexpectedResponseBodyException('Required attributes for `Item` missing in the response body: ' . $missingFields);
        }
        $schema = new Item($payload['mandatoryInteger'], $payload['mandatoryString'], ItemMandatoryEnum::from($payload['mandatoryEnum']), new DateTimeImmutable($payload['mandatoryDate']), $payload['mandatoryNullableDate'] !== null ? new DateTimeImmutable($payload['mandatoryNullableDate']) : null, $payload['mandatoryFloat'], $payload['mandatoryBoolean'], $payload['mandatoryArray'], $this->embeddedObjectMapper->toSchema($payload['mandatoryObject']), $payload['mandatoryNullableObject'] !== null ? $this->embeddedNullableObjectMapper->toSchema($payload['mandatoryNullableObject']) : null);
        if (isset($payload['optionalInteger'])) {
            $schema->setOptionalInteger($payload['optionalInteger']);
        }
        if (isset($payload['optionalString'])) {
            $schema->setOptionalString($payload['optionalString']);
        }
        if (isset($payload['optionalEnum'])) {
            $schema->setOptionalEnum(ItemOptionalEnum::from($payload['optionalEnum']));
        }
        if (isset($payload['optionalDate'])) {
            $schema->setOptionalDate(new DateTimeImmutable($payload['optionalDate']));
        }
        if (array_key_exists('optionalNullableDate', $payload)) {
            $schema->setOptionalNullableDate($payload['optionalNullableDate'] !== null ? new DateTimeImmutable($payload['optionalNullableDate']) : null);
        }
        if (isset($payload['optionalFloat'])) {
            $schema->setOptionalFloat($payload['optionalFloat']);
        }
        if (isset($payload['optionalBoolean'])) {
            $schema->setOptionalBoolean($payload['optionalBoolean']);
        }
        if (isset($payload['optionalArray'])) {
            $schema->setOptionalArray($payload['optionalArray']);
        }
        if (isset($payload['optionalObject'])) {
            $schema->setOptionalObject($this->embeddedObjectMapper->toSchema($payload['optionalObject']));
        }
        if (array_key_exists('optionalNullableObject', $payload)) {
            $schema->setOptionalNullableObject($payload['optionalNullableObject'] !== null ? $this->embeddedNullableObjectMapper->toSchema($payload['optionalNullableObject']) : null);
        }

        return $schema;
    }
}
