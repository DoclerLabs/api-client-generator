<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\SchemaMapperGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\SchemaMapperGenerator
 */
class SchemaMapperGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Single object response'         => [
                '/SchemaMapper/item.yaml',
                '/SchemaMapper/ItemMapper.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'Collection response'            => [
                '/SchemaMapper/itemCollection.yaml',
                '/SchemaMapper/ItemCollectionMapper.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemCollectionMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'No optional fields in response' => [
                '/SchemaMapper/noOptional.yaml',
                '/SchemaMapper/ResourceMapper.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ResourceMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'Free form object response'         => [
                '/SchemaMapper/freeFormItem.yaml',
                '/SchemaMapper/FreeFormItemMapper.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\FreeFormItemMapper',
                ConfigurationBuilder::fake()->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaMapperGenerator::class;
    }
}
