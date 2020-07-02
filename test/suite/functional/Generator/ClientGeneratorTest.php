<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\ClientGenerator;

/**
 * @coversDefaultClass ClientGenerator
 */
class ClientGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Basic schema'    => [
                '/Client/petstore.yaml',
                '/Client/SwaggerPetstoreClient.php',
                self::BASE_NAMESPACE . '\\SwaggerPetstoreClient',
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return ClientGenerator::class;
    }
}
