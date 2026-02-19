<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Entity;

use DoclerLabs\ApiClientGenerator\Entity\ContentType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Entity\ContentType
 */
class ContentTypeTest extends TestCase
{
    /**
     * @dataProvider supportedContentTypesProvider
     */
    public function testIsSupportedReturnsTrueForSupportedTypes(string $contentType): void
    {
        self::assertTrue(ContentType::isSupported($contentType));
    }

    public function supportedContentTypesProvider(): array
    {
        return [
            'application/json'                  => ['application/json'],
            'application/xml'                   => ['application/xml'],
            'application/x-www-form-urlencoded' => ['application/x-www-form-urlencoded'],
            'application/vnd.api+json'          => ['application/vnd.api+json'],
            'SDMX JSON'                         => ['application/vnd.sdmx.data+json'],
            'HAL JSON'                          => ['application/hal+json'],
            'JSON-LD'                           => ['application/ld+json'],
            'Problem JSON'                      => ['application/problem+json'],
            'JSON with charset'                 => ['application/json; charset=utf-8'],
            'Vendor JSON with version'          => ['application/vnd.sdmx.data+json;version=1.0.0-wd'],
            'Uppercase'                         => ['Application/JSON'],
            'With spaces'                       => ['application/json ; charset=utf-8'],
        ];
    }

    /**
     * @dataProvider unsupportedContentTypesProvider
     */
    public function testIsSupportedReturnsFalseForUnsupportedTypes(string $contentType): void
    {
        self::assertFalse(ContentType::isSupported($contentType));
    }

    public function unsupportedContentTypesProvider(): array
    {
        return [
            'text/plain'               => ['text/plain'],
            'text/html'                => ['text/html'],
            'text/csv'                 => ['text/csv'],
            'image/png'                => ['image/png'],
            'application/octet-stream' => ['application/octet-stream'],
        ];
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(string $input, string $expected): void
    {
        self::assertEquals($expected, ContentType::normalize($input));
    }

    public function normalizeProvider(): array
    {
        return [
            'simple'       => ['application/json', 'application/json'],
            'with charset' => ['application/json; charset=utf-8', 'application/json'],
            'with version' => ['application/vnd.sdmx.data+json;version=1.0.0-wd', 'application/vnd.sdmx.data+json'],
            'uppercase'    => ['Application/JSON', 'application/json'],
            'with spaces'  => [' application/json ; charset=utf-8 ', 'application/json'],
        ];
    }

    /**
     * @dataProvider jsonBasedProvider
     */
    public function testIsJsonBased(string $contentType, bool $expected): void
    {
        self::assertEquals($expected, ContentType::isJsonBased($contentType));
    }

    public function jsonBasedProvider(): array
    {
        return [
            'application/json' => ['application/json', true],
            'vendor +json'     => ['application/vnd.api+json', true],
            'SDMX +json'       => ['application/vnd.sdmx.data+json', true],
            'with parameters'  => ['application/json; charset=utf-8', true],
            'xml'              => ['application/xml', false],
            'form'             => ['application/x-www-form-urlencoded', false],
            'text'             => ['text/plain', false],
        ];
    }

    public function testFilterUnsupported(): void
    {
        $contentTypes = [
            'application/json',
            'text/plain',
            'application/vnd.sdmx.data+json',
            'text/csv',
        ];

        $unsupported = ContentType::filterUnsupported($contentTypes);

        self::assertEquals(['text/plain', 'text/csv'], $unsupported);
    }

    public function testFilterUnsupportedReturnsEmptyArrayWhenAllSupported(): void
    {
        $contentTypes = [
            'application/json',
            'application/xml',
            'application/vnd.api+json',
        ];

        $unsupported = ContentType::filterUnsupported($contentTypes);

        self::assertEquals([], $unsupported);
    }
}
