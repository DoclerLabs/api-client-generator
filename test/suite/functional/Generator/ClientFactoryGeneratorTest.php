<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\ClientFactoryGenerator;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClientImplementation;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementation;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @coversDefaultClass ClientFactoryGenerator
 */
class ClientFactoryGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Default client and message' => [
                '/ClientFactory/petstore.yaml',
                '/ClientFactory/ClientFactoryDefaultPhp74.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClientFactory',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
            'With Guzzle6 client'        => [
                '/ClientFactory/petstore.yaml',
                '/ClientFactory/ClientFactoryGuzzle6Client.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClientFactory',
                ConfigurationBuilder::fake()->withHttpClient(HttpClientImplementation::HTTP_CLIENT_GUZZLE6)->build(),
            ],
            'With Nyholm request mapper' => [
                '/ClientFactory/petstore.yaml',
                '/ClientFactory/ClientFactoryNyholmMessage.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClientFactory',
                ConfigurationBuilder::fake()->withHttpMessage(HttpMessageImplementation::HTTP_MESSAGE_NYHOLM)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return ClientFactoryGenerator::class;
    }
}
