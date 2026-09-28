<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\EnumGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator
 */
class EnumGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'With PHP 8.1 - ItemOptionalEnum' => [
                '/Schema/item.yaml',
                '/Schema/ItemOptionalEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemOptionalEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'With PHP 8.1 - ItemOptionalIntEnum' => [
                '/Schema/item.yaml',
                '/Schema/ItemOptionalIntEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemOptionalIntEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'With PHP 8.1 - ItemMandatoryEnum' => [
                '/Schema/item.yaml',
                '/Schema/ItemMandatoryEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ItemMandatoryEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Array of Enums - String Enum' => [
                '/Schema/arrayOfEnums.yaml',
                '/Schema/StringParamOneEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\StringParamOneEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Array of Enums - Integer Enum' => [
                '/Schema/arrayOfEnums.yaml',
                '/Schema/IntParamEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\IntParamEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Inline array of enums request parameter, value starting with a digit' => [
                '/Request/getResourcesByStatuses.yaml',
                '/Schema/GetResourcesByStatusesStatusesItemEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\GetResourcesByStatusesStatusesItemEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Request parameter enum with a value consisting of symbols only' => [
                '/Request/getResourcesBySymbols.yaml',
                '/Schema/OperatorEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\OperatorEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Inline array of enums request parameter with a value consisting of symbols only' => [
                '/Request/getResourcesBySymbols.yaml',
                '/Schema/GetResourcesBySymbolsEmbedItemEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\GetResourcesBySymbolsEmbedItemEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Enum named after a reserved word, with the value class' => [
                '/Schema/reservedWords.yaml',
                '/Schema/ListEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\ListEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Enum property colliding with an enum query parameter keeps the emitted definition' => [
                '/Schema/classNameCollisions.yaml',
                '/Schema/CallStatusEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\CallStatusEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Enum property colliding with an enum query parameter gets a numeric suffix' => [
                '/Schema/classNameCollisions.yaml',
                '/Schema/CallStatus2Enum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\CallStatus2Enum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Same enum query parameter with different values, the last one keeps the name' => [
                '/Schema/classNameCollisions.yaml',
                '/Schema/CriterionStatusEqEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\CriterionStatusEqEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Same enum query parameter with different values, the other one gets a numeric suffix' => [
                '/Schema/classNameCollisions.yaml',
                '/Schema/CriterionStatusEq2Enum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\CriterionStatusEq2Enum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return EnumGenerator::class;
    }
}
