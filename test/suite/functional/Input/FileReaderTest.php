<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Input;

use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationAwareTrait;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Input\FileReader
 */
class FileReaderTest extends TestCase
{
    use ConfigurationAwareTrait;

    protected FileReader  $sut;

    protected function setUp(): void
    {
        $container = $this->getContainerWith(ConfigurationBuilder::fake()->build());

        $this->sut = $container[FileReader::class];
    }

    /**
     * @dataProvider validFileProvider
     */
    public function testParseValidFile(string $filePath): void
    {
        self::assertNotNull($this->sut->read($filePath));
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testParseInvalidFile(string $filePath): void
    {
        $this->expectException(InvalidSpecificationException::class);
        $this->sut->read($filePath);
    }

    public function validFileProvider()
    {
        return [
            [__DIR__ . '/FileReader/openapi.yaml'],
            [__DIR__ . '/FileReader/openapi.yml'],
            [__DIR__ . '/FileReader/openapi.json'],
        ];
    }

    public function invalidFileProvider()
    {
        return [
            [__DIR__ . '/FileReader/non_existing'],
            [__DIR__ . '/FileReader/openapi.invalid'],
        ];
    }
}
