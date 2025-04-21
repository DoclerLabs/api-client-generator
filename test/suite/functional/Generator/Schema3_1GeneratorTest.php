<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator
 */
class Schema3_1GeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'With PHP 7.0' => [
                '/Schema3_1/item.yaml',
                '/Schema3_1/ItemPhp70.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP70)->build(),
            ],
            'With PHP 7.2' => [
                '/Schema3_1/item.yaml',
                '/Schema3_1/ItemPhp72.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'With PHP 7.4' => [
                '/Schema3_1/item.yaml',
                '/Schema3_1/ItemPhp74.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'With PHP 8.0' => [
                '/Schema3_1/item.yaml',
                '/Schema3_1/ItemPhp80.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'With PHP 8.1' => [
                '/Schema3_1/item.yaml',
                '/Schema3_1/ItemPhp81.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Embedded schema' => [
                '/Schema3_1/item.yaml',
                '/Schema3_1/ItemMandatoryObject.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemMandatoryObject',
                ConfigurationBuilder::fake()->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return SchemaGenerator::class;
    }
}
