<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\RequestGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\RequestGenerator
 */
class RequestGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Request with body'                             => [
                '/Request/patchResource.yaml',
                '/Request/PatchResourceRequest.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with mandatory parameters and body'    => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request without mandatory parameters and body' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with same parameter name but different parameters' => [
                '/Request/getResources.yaml',
                '/Request/GetSubResourcesRequest.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetSubResourcesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return RequestGenerator::class;
    }
}
