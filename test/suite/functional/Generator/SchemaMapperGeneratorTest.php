<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\SchemaMapperGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @coversDefaultClass SchemaMapperGenerator
 */
class SchemaMapperGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Single object response'         => [
                '/ResponseMapper/item.yaml',
                '/ResponseMapper/ItemResponseMapper.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemResponseMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'Collection response'            => [
                '/ResponseMapper/itemCollection.yaml',
                '/ResponseMapper/ItemCollectionResponseMapper.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemCollectionResponseMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'No optional fields in response' => [
                '/ResponseMapper/noOptional.yaml',
                '/ResponseMapper/ResourceResponseMapper.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ResourceResponseMapper',
                ConfigurationBuilder::fake()->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaMapperGenerator::class;
    }
}
