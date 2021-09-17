<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\ServiceProviderGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\ServiceProviderGenerator
 */
class ServiceProviderGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'With PHP 7.0' => [
                '/ServiceProvider/petstore.yaml',
                '/ServiceProvider/ServiceProviderPhp70.php',
                self::BASE_NAMESPACE . '\\ServiceProvider',
                ConfigurationBuilder::fake()->build(),
            ],
            'With PHP 7.2' => [
                '/ServiceProvider/petstore.yaml',
                '/ServiceProvider/ServiceProviderPhp72.php',
                self::BASE_NAMESPACE . '\\ServiceProvider',
                ConfigurationBuilder::fake()->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return ServiceProviderGenerator::class;
    }
}
