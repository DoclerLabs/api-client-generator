<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
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
            'Single object response with php 7.4' => [
                '/SchemaMapper/item.yaml',
                '/SchemaMapper/ItemMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'Single object response with php 8.0' => [
                '/SchemaMapper/item.yaml',
                '/SchemaMapper/ItemMapper80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Single object response with php 8.1' => [
                '/SchemaMapper/item.yaml',
                '/SchemaMapper/ItemMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Collection response with php 7.4' => [
                '/SchemaMapper/itemCollection.yaml',
                '/SchemaMapper/ItemCollectionMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemCollectionMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'Collection response with php 8.0' => [
                '/SchemaMapper/itemCollection.yaml',
                '/SchemaMapper/ItemCollectionMapper80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemCollectionMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Collection response with php 8.1' => [
                '/SchemaMapper/itemCollection.yaml',
                '/SchemaMapper/ItemCollectionMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemCollectionMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'No optional fields in response with php 7.4' => [
                '/SchemaMapper/noOptional.yaml',
                '/SchemaMapper/ResourceMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ResourceMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'No optional fields in response with php 8.0' => [
                '/SchemaMapper/noOptional.yaml',
                '/SchemaMapper/ResourceMapper80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ResourceMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'No optional fields in response with php 8.1' => [
                '/SchemaMapper/noOptional.yaml',
                '/SchemaMapper/ResourceMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ResourceMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Free form object response with php 7.4' => [
                '/SchemaMapper/freeFormItem.yaml',
                '/SchemaMapper/FreeFormItemMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\FreeFormItemMapper',
                ConfigurationBuilder::fake()->build(),
            ],
            'Free form object response with php 8.0' => [
                '/SchemaMapper/freeFormItem.yaml',
                '/SchemaMapper/FreeFormItemMapper80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\FreeFormItemMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Free form object response with php 8.1' => [
                '/SchemaMapper/freeFormItem.yaml',
                '/SchemaMapper/FreeFormItemMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\FreeFormItemMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'OneOf response with php 7.4' => [
                '/SchemaMapper/oneOf.yaml',
                '/SchemaMapper/OneOfResponseBodyMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'OneOf response with php 8.0' => [
                '/SchemaMapper/oneOf.yaml',
                '/SchemaMapper/OneOfResponseBodyMapper80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'OneOf response with php 8.1' => [
                '/SchemaMapper/oneOf.yaml',
                '/SchemaMapper/OneOfResponseBodyMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'OneOf response without discriminator with php 7.4' => [
                '/SchemaMapper/oneOfWithoutDiscriminator.yaml',
                '/SchemaMapper/OneOfResponseBodyMapperWithoutDiscriminator74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'OneOf response without discriminator with php 8.0' => [
                '/SchemaMapper/oneOfWithoutDiscriminator.yaml',
                '/SchemaMapper/OneOfResponseBodyMapperWithoutDiscriminator80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'OneOf response without discriminator with php 8.1' => [
                '/SchemaMapper/oneOfWithoutDiscriminator.yaml',
                '/SchemaMapper/OneOfResponseBodyMapperWithoutDiscriminator81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'AnyOf response with php 7.4' => [
                '/SchemaMapper/anyOf.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'AnyOf response with php 8.0' => [
                '/SchemaMapper/anyOf.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapper80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'AnyOf response with php 8.1' => [
                '/SchemaMapper/anyOf.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'AnyOf response with php 7.4' => [
                '/SchemaMapper/anyOfWithoutDiscriminator.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapperWithoutDiscriminator74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'AnyOf response with php 8.0' => [
                '/SchemaMapper/anyOfWithoutDiscriminator.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapperWithoutDiscriminator80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'AnyOf response with php 8.1' => [
                '/SchemaMapper/anyOfWithoutDiscriminator.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapperWithoutDiscriminator81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaMapperGenerator::class;
    }
}
