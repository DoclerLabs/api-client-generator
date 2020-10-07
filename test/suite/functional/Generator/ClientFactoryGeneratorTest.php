<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\ClientFactoryGenerator;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClientImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
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
                '/ClientFactory/ClientFactoryDefault.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClientFactory',
                ConfigurationBuilder::fake()->build(),
            ],
            'With Guzzle6 client'        => [
                '/ClientFactory/petstore.yaml',
                '/ClientFactory/ClientFactoryGuzzle6Client.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClientFactory',
                ConfigurationBuilder::fake()
                    ->withHttpClient(HttpClientImplementationStrategy::HTTP_CLIENT_GUZZLE6)
                    ->build(),
            ],
            'With Nyholm request mapper' => [
                '/ClientFactory/petstore.yaml',
                '/ClientFactory/ClientFactoryNyholmMessage.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClientFactory',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return ClientFactoryGenerator::class;
    }
}
