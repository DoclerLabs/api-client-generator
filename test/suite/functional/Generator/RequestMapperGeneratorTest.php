<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
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
            'With Guzzle message + PHP 7.4' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/GuzzleRequestMapper74.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\GuzzleRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE)
                    ->build(),
            ],
            'With Guzzle message + PHP 8.0' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/GuzzleRequestMapper80.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\GuzzleRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE)
                    ->withPhpVersion(PhpVersion::VERSION_PHP80)
                    ->build(),
            ],
            'With Guzzle message + PHP 8.1' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/GuzzleRequestMapper81.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\GuzzleRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE)
                    ->withPhpVersion(PhpVersion::VERSION_PHP81)
                    ->build(),
            ],
            'With Nyholm message + PHP 7.4' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/NyholmRequestMapper74.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\NyholmRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->build(),
            ],
            'With Nyholm message + PHP 8.0' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/NyholmRequestMapper80.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\NyholmRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->withPhpVersion(PhpVersion::VERSION_PHP80)
                    ->build(),
            ],
            'With Nyholm message + PHP 8.1' => [
                '/RequestMapper/petstore.yaml',
                '/RequestMapper/NyholmRequestMapper81.php',
                self::BASE_NAMESPACE . RequestMapperGenerator::NAMESPACE_SUBPATH . '\\NyholmRequestMapper',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->withPhpVersion(PhpVersion::VERSION_PHP81)
                    ->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return RequestMapperGenerator::class;
    }
}
