<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\RequestMapperGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\RequestMapperGenerator
 */
class RequestMapperGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'With Guzzle message' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/GuzzleRequestMapper.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\GuzzleRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE)
                    ->build(),
            ],
            'With Nyholm message' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/NyholmRequestMapper.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\NyholmRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return RequestMapperGenerator::class;
    }
}
