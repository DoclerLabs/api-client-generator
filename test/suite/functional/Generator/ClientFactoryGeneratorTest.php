<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\ClientFactoryGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\ClientFactoryGenerator
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
        ];
    }

    protected function generatorClassName(): string
    {
        return ClientFactoryGenerator::class;
    }
}
