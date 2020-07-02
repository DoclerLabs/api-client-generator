<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\RequestGenerator;

/**
 * @coversDefaultClass RequestGenerator
 */
class RequestGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Request with mandatory parameters and body'    => [
                '/Request/putResourceById.yaml',
                '/Request/PutResourceByIdRequest.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\PutResourceByIdRequest',
            ],
            'Request without mandatory parameters and body' => [
                '/Request/getResources.yaml',
                '/Request/GetResourcesRequest.php',
                self::BASE_NAMESPACE . RequestGenerator::NAMESPACE_SUBPATH . '\\GetResourcesRequest',
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return RequestGenerator::class;
    }
}
