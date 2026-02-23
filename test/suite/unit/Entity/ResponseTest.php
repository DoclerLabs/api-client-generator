<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Entity;

use DoclerLabs\ApiClientGenerator\Entity\Response;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Entity\Response
 */
class ResponseTest extends TestCase
{
    public function testStandardJsonContentType(): void
    {
        $response = new Response(200, null, ['application/json']);
        self::assertEquals(['application/json'], $response->bodyContentTypes);
    }

    public function testVndApiJsonContentType(): void
    {
        $response = new Response(200, null, ['application/vnd.api+json']);
        self::assertEquals(['application/vnd.api+json'], $response->bodyContentTypes);
    }

    /**
     * @dataProvider validJsonSuffixContentTypesProvider
     */
    public function testRfc6839JsonSuffixContentTypes(string $contentType): void
    {
        $response = new Response(200, null, [$contentType]);
        self::assertEquals([$contentType], $response->bodyContentTypes);
    }

    public function validJsonSuffixContentTypesProvider(): array
    {
        return [
            'SDMX JSON'                => ['application/vnd.sdmx.data+json'],
            'SDMX JSON with version'   => ['application/vnd.sdmx.data+json;version=1.0.0-wd'],
            'HAL JSON'                 => ['application/hal+json'],
            'JSON-LD'                  => ['application/ld+json'],
            'Problem JSON (RFC 7807)'  => ['application/problem+json'],
            'Custom vendor JSON'       => ['application/vnd.custom.api+json'],
            'JSON with charset'        => ['application/json; charset=utf-8'],
            'Vendor JSON with charset' => ['application/vnd.api+json; charset=utf-8'],
        ];
    }

    public function testUnsupportedContentTypeThrowsException(): void
    {
        $this->expectException(InvalidSpecificationException::class);
        $this->expectExceptionMessageMatches('/Response content-type .* is not currently supported/');

        new Response(200, null, ['text/plain']);
    }

    public function testMultipleContentTypesWithUnsupportedThrowsException(): void
    {
        $this->expectException(InvalidSpecificationException::class);
        $this->expectExceptionMessageMatches('/Response content-type .* is not currently supported/');

        new Response(200, null, ['application/json', 'text/csv']);
    }

    public function testMixedValidContentTypes(): void
    {
        $contentTypes = ['application/json', 'application/vnd.sdmx.data+json', 'application/xml'];
        $response     = new Response(200, null, $contentTypes);
        self::assertEquals($contentTypes, $response->bodyContentTypes);
    }

    public function testEmptyContentTypesIsValid(): void
    {
        $response = new Response(204, null, []);
        self::assertEquals([], $response->bodyContentTypes);
    }
}
