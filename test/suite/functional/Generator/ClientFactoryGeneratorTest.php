<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\ClientFactoryGenerator;

/**
 * @coversDefaultClass ClientFactoryGenerator
 */
class ClientFactoryGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Basic schema'    => [
                '/ClientFactory/petstore.yaml',
                '/ClientFactory/SwaggerPetstoreClientFactory.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClientFactory',
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return ClientFactoryGenerator::class;
    }
}
