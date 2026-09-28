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
            'OneOf response with a discriminator but without mapping with php 7.4' => [
                '/SchemaMapper/oneOfDiscriminatorWithoutMapping.yaml',
                '/SchemaMapper/OneOfDiscriminatorWithoutMappingMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'OneOf response with a discriminator but without mapping with php 8.1' => [
                '/SchemaMapper/oneOfDiscriminatorWithoutMapping.yaml',
                '/SchemaMapper/OneOfDiscriminatorWithoutMappingMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'OneOf response with a discriminator mapping covering some alternatives with php 7.4' => [
                '/SchemaMapper/oneOfDiscriminatorPartialMapping.yaml',
                '/SchemaMapper/OneOfDiscriminatorPartialMappingMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\PetMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'OneOf response with a discriminator mapping covering some alternatives with php 8.1' => [
                '/SchemaMapper/oneOfDiscriminatorPartialMapping.yaml',
                '/SchemaMapper/OneOfDiscriminatorPartialMappingMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\PetMapper',
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
            'AnyOf response without discriminator with php 7.4' => [
                '/SchemaMapper/anyOfWithoutDiscriminator.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapperWithoutDiscriminator74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'AnyOf response without discriminator with php 8.0' => [
                '/SchemaMapper/anyOfWithoutDiscriminator.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapperWithoutDiscriminator80.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'AnyOf response without discriminator with php 8.1' => [
                '/SchemaMapper/anyOfWithoutDiscriminator.yaml',
                '/SchemaMapper/AnyOfResponseBodyMapperWithoutDiscriminator81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GetExampleResponseBodyMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Array of Enums with php 8.1' => [
                '/Schema/arrayOfEnums.yaml',
                '/SchemaMapper/ItemWithArraysOfEnumPropertiesMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemWithArraysOfEnumPropertiesMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Array of Enums with php 7.4' => [
                '/Schema/arrayOfEnums.yaml',
                '/SchemaMapper/ItemWithArraysOfEnumPropertiesMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemWithArraysOfEnumPropertiesMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Nullable allOf reference with php 7.4' => [
                '/SchemaMapper/nullableAllOfReference.yaml',
                '/SchemaMapper/NullableAllOfReferenceMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ProfileMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Nullable allOf reference with php 8.1' => [
                '/SchemaMapper/nullableAllOfReference.yaml',
                '/SchemaMapper/NullableAllOfReferenceMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ProfileMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Nullable enums with php 7.4' => [
                '/SchemaMapper/nullableEnum.yaml',
                '/SchemaMapper/NullableEnumMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\CookieMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Nullable enums with php 8.1' => [
                '/SchemaMapper/nullableEnum.yaml',
                '/SchemaMapper/NullableEnumMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\CookieMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'OneOf response without discriminator told apart by enums with php 7.4' => [
                '/SchemaMapper/oneOfWithoutDiscriminatorEnum.yaml',
                '/SchemaMapper/OneOfWithoutDiscriminatorEnumMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\UploaderMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'OneOf response without discriminator told apart by enums with php 8.1' => [
                '/SchemaMapper/oneOfWithoutDiscriminatorEnum.yaml',
                '/SchemaMapper/OneOfWithoutDiscriminatorEnumMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\UploaderMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Array of arrays of objects with php 7.0' => [
                '/SchemaMapper/arrayOfArraysOfObjects.yaml',
                '/SchemaMapper/ArrayOfArraysOfObjectsMapper70.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ShowSubscribersMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP70)->build(),
            ],
            'Array of arrays of objects with php 7.4' => [
                '/SchemaMapper/arrayOfArraysOfObjects.yaml',
                '/SchemaMapper/ArrayOfArraysOfObjectsMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ShowSubscribersMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Array of arrays of objects with php 8.1' => [
                '/SchemaMapper/arrayOfArraysOfObjects.yaml',
                '/SchemaMapper/ArrayOfArraysOfObjectsMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ShowSubscribersMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Collection nested in an array of arrays of objects with php 7.4' => [
                '/SchemaMapper/arrayOfArraysOfObjects.yaml',
                '/SchemaMapper/ShowSubscriberCollectionMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ShowSubscriberCollectionMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'Mapper referencing schemas named after reserved words' => [
                '/Schema/reservedWords.yaml',
                '/SchemaMapper/TournamentMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\TournamentMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Discriminator mapping to a schema named after a reserved word' => [
                '/Schema/reservedWords.yaml',
                '/SchemaMapper/GameMapper81.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\GameMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Mapper of a schema with an inline property whose class name collides' => [
                '/Schema/classNameCollisions.yaml',
                '/SchemaMapper/PageItemMapper74.php',
                self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\PageItemMapper',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
        ];
    }

    public function testArrayOfEnumsItemsGetNoMapper(): void
    {
        $this->setUpContainer(ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build());
        $specificationPath = __DIR__ . '/Schema/arrayOfEnums.yaml';
        $specification     = $this->specificationParser->parse(
            $this->specificationReader->read($specificationPath),
            $specificationPath
        );

        $this->sut->generate($specification, $this->fileRegistry);

        self::assertSame(
            [self::BASE_NAMESPACE . SchemaMapperGenerator::NAMESPACE_SUBPATH . '\\ItemWithArraysOfEnumPropertiesMapper'],
            array_keys(iterator_to_array($this->fileRegistry))
        );
    }

    protected function generatorClassName(): string
    {
        return SchemaMapperGenerator::class;
    }
}
