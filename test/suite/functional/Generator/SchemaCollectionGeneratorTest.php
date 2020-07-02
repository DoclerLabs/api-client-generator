<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\SchemaCollectionGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;

/**
 * @coversDefaultClass SchemaCollectionGenerator
 */
class SchemaCollectionGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Schema collection'    => [
                '/SchemaCollection/itemCollection.yaml',
                '/SchemaCollection/ItemCollection.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemCollection',
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaCollectionGenerator::class;
    }
}
