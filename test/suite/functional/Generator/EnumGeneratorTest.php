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
            'Inline enum response body' => [
                '/Client/literal-responses.yaml',
                '/Schema/GetHealthResponseBodyEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\GetHealthResponseBodyEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Inline nullable enum response body' => [
                '/Client/literal-responses.yaml',
                '/Schema/GetModeResponseBodyEnum.php',
                self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\GetModeResponseBodyEnum',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return EnumGenerator::class;
    }
}
