<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\ResponseMapperGenerator;

/**
 * @coversDefaultClass ResponseMapperGenerator
 */
class ResponseMapperGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        return [
            'Single object response'    => [
                '/ResponseMapper/item.yaml',
                '/ResponseMapper/ItemResponseMapper.php',
                self::BASE_NAMESPACE . ResponseMapperGenerator::NAMESPACE_SUBPATH . '\\ItemResponseMapper',
            ],
            'Collection response' => [
                '/ResponseMapper/itemCollection.yaml',
                '/ResponseMapper/ItemCollectionResponseMapper.php',
                self::BASE_NAMESPACE . ResponseMapperGenerator::NAMESPACE_SUBPATH . '\\ItemCollectionResponseMapper',
            ],
            'No optional fields in response'    => [
                '/ResponseMapper/noOptional.yaml',
                '/ResponseMapper/ResourceResponseMapper.php',
                self::BASE_NAMESPACE . ResponseMapperGenerator::NAMESPACE_SUBPATH . '\\ResourceResponseMapper',
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return ResponseMapperGenerator::class;
    }
}
