<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\ClientGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\ClientGenerator
 */
class ClientGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Basic schema with php 7.4' => [
                '/Client/petstore.yaml',
                '/Client/SwaggerPetstoreClient74.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClient',
                ConfigurationBuilder::fake()->build(),
            ],
            'Basic schema with php 8.0' => [
                '/Client/petstore.yaml',
                '/Client/SwaggerPetstoreClient80.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClient',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Basic schema with php 8.1' => [
                '/Client/petstore.yaml',
                '/Client/SwaggerPetstoreClient81.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClient',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Multiple responses schema with php 7.4' => [
                '/Client/multiple-responses.yaml',
                '/Client/MultipleResponsesClient74.php',
                self::BASE_NAMESPACE . '\\MultipleResponsesClient',
                ConfigurationBuilder::fake()->build(),
            ],
            'Multiple responses schema with php 8.0' => [
                '/Client/multiple-responses.yaml',
                '/Client/MultipleResponsesClient80.php',
                self::BASE_NAMESPACE . '\\MultipleResponsesClient',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Multiple responses schema with php 8.1' => [
                '/Client/multiple-responses.yaml',
                '/Client/MultipleResponsesClient81.php',
                self::BASE_NAMESPACE . '\\MultipleResponsesClient',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'Basic schema with php 8.1 and include tags' => [
                '/Client/petstore.yaml',
                '/Client/SwaggerPetstoreClientWithTags.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClient',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->withIncludeTags(['tag1', 'tag2'])->build(),
            ],
            'Basic schema with php 8.1 and exclude tags' => [
                '/Client/petstore.yaml',
                '/Client/SwaggerPetstoreClientWithTags.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClient',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->withExcludeTags(['tag3', 'tag4'])->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return ClientGenerator::class;
    }
}
