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
            'With PHP 7.0' => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp70.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP70)->build(),
            ],
            'With PHP 7.2' => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp72.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'With PHP 7.4' => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp74.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'With PHP 8.0' => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp80.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'With PHP 8.1' => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp81.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'With PHP 8.2' => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp82.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP82)->build(),
            ],
            'With PHP 8.3' => [
                '/Schema/item.yaml',
                '/Schema/ItemPhp83.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP83)->build(),
            ],
            'Embedded schema' => [
                '/Schema/item.yaml',
                '/Schema/ItemMandatoryObject.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemMandatoryObject',
                ConfigurationBuilder::fake()->build(),
            ],
            'All of' => [
                '/Schema/extendedItem.yaml',
                '/Schema/AllOfItem.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ExtendedItem',
                ConfigurationBuilder::fake()->build(),
            ],
            'Array of Enums with PHP 8.1' => [
                '/Schema/arrayOfEnums.yaml',
                '/Schema/ItemWithArraysOfEnumProperties81.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemWithArraysOfEnumProperties',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Array of Enums with PHP 7.4' => [
                '/Schema/arrayOfEnums.yaml',
                '/Schema/ItemWithArraysOfEnumProperties74.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemWithArraysOfEnumProperties',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Untyped (mixed) properties with PHP 7.4' => [
                '/Schema/itemWithMixed.yaml',
                '/Schema/ItemWithMixedPhp74.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemWithMixed',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Untyped (mixed) properties with PHP 8.0' => [
                '/Schema/itemWithMixed.yaml',
                '/Schema/ItemWithMixedPhp80.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemWithMixed',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Untyped (mixed) properties with PHP 8.1' => [
                '/Schema/itemWithMixed.yaml',
                '/Schema/ItemWithMixedPhp81.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemWithMixed',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Schema named after a reserved word with PHP 7.4' => [
                '/Schema/reservedWords.yaml',
                '/Schema/MatchSchemaPhp74.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\MatchSchema',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Schema named after a reserved word with PHP 8.1' => [
                '/Schema/reservedWords.yaml',
                '/Schema/MatchSchemaPhp81.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\MatchSchema',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Schema referencing schemas named after reserved words' => [
                '/Schema/reservedWords.yaml',
                '/Schema/Tournament.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Tournament',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Inline schema with a reserved word fallback name' => [
                '/Schema/reservedWords.yaml',
                '/Schema/ClassSchema.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ClassSchema',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaGenerator::class;
    }
}
