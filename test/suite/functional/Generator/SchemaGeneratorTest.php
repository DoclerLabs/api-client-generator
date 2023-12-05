<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator
 */
class SchemaGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'With PHP 7.0'    => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp70.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP70)->build(),
            ],
            'With PHP 7.2'    => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp72.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'With PHP 7.4'    => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp74.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Embedded schema' => [
                '/Schema/item.yaml',
                '/Schema/ItemMandatoryObject.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemMandatoryObject',
                ConfigurationBuilder::fake()->build(),
            ],
            'All of'          => [
                '/Schema/extendedItem.yaml',
                '/Schema/AllOfItem.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ExtendedItem',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - Dog'    => [
                '/Schema/oneOf.yaml',
                '/Schema/Dog.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Dog',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - Cat'    => [
                '/Schema/oneOf.yaml',
                '/Schema/Cat.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Cat',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - Animal' => [
                '/Schema/complexOneOf.yaml',
                '/Schema/Animal.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Animal',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - Bird' => [
                '/Schema/complexOneOf.yaml',
                '/Schema/Bird.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Bird',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - Characteristics' => [
                '/Schema/complexOneOf.yaml',
                '/Schema/Characteristics.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Characteristics',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - Machine' => [
                '/Schema/complexOneOf.yaml',
                '/Schema/Machine.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Machine',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - MachineSpecifications' => [
                '/Schema/complexOneOf.yaml',
                '/Schema/MachineSpecifications.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\MachineSpecifications',
                ConfigurationBuilder::fake()->build(),
            ],
            'One of - Mammal' => [
                '/Schema/complexOneOf.yaml',
                '/Schema/Mammal.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Mammal',
                ConfigurationBuilder::fake()->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaGenerator::class;
    }
}
