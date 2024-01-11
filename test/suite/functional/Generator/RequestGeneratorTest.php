<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
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
            'Request with body with php 7.4 + api key in header' => [
                '/Request/patchResource.yaml',
                '/Request/PatchResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 7.4 + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchSubResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchSubResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 7.4 + query params + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchYetAnotherSubResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchYetAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 7.4 + api key in cookie' => [
                '/Request/patchResource.yaml',
                '/Request/PatchAnotherSubResourceRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with body with php 8.0 + api key in header' => [
                '/Request/patchResource.yaml',
                '/Request/PatchResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with body with php 8.0 + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchSubResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with body with php 8.0 + query params + api key in query' => [
                '/Request/patchResource.yaml',
                '/Request/PatchYetAnotherSubResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchYetAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with body with php 8.0 + api key in cookie' => [
                '/Request/patchResource.yaml',
                '/Request/PatchAnotherSubResourceRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PatchAnotherSubResourceRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with mandatory parameters and body with php 7.4' => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with mandatory parameters and body with php 8.0' => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request without mandatory parameters and body with php 7.4' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request without mandatory parameters and body with php 8.0' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Request with same parameter name but different parameters with php 7.4' => [
                '/Request/getResources.yaml',
                '/Request/GetSubResourcesRequest74.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetSubResourcesRequest',
                ConfigurationBuilder::fake()->build(),
            ],
            'Request with same parameter name but different parameters with php 8.0' => [
                '/Request/getResources.yaml',
                '/Request/GetSubResourcesRequest80.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetSubResourcesRequest',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return RequestGenerator::class;
    }
}
