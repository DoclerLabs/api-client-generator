<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;

/**
 * @coversDefaultClass SchemaGenerator
 */
class SchemaGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Basic schema'                => [
                '/Schema/item.yaml',
                '/Schema/Item.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
            ],
            'Extended (inherited) schema' => [
                '/Schema/extendedItem.yaml',
                '/Schema/ExtendedItem.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ExtendedItem',
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaGenerator::class;
    }
}
