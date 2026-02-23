<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Entity;

use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Entity\RequestFieldRegistry;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Entity\Request
 */
class RequestTest extends TestCase
{
    private RequestFieldRegistry $fieldRegistry;

    protected function setUp(): void
    {
        $this->fieldRegistry = $this->createMock(RequestFieldRegistry::class);
    }

    public function testStandardJsonContentType(): void
    {
        $request = new Request('/test', Request::GET, $this->fieldRegistry, ['application/json']);
        self::assertEquals(['application/json'], $request->bodyContentTypes);
    }

    public function testVndApiJsonContentType(): void
    {
        $request = new Request('/test', Request::POST, $this->fieldRegistry, ['application/vnd.api+json']);
        self::assertEquals(['application/vnd.api+json'], $request->bodyContentTypes);
    }

    /**
     * @dataProvider validJsonSuffixContentTypesProvider
     */
    public function testRfc6839JsonSuffixContentTypes(string $contentType): void
    {
        $request = new Request('/test', Request::POST, $this->fieldRegistry, [$contentType]);
        self::assertEquals([$contentType], $request->bodyContentTypes);
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
        $this->expectExceptionMessageMatches('/Request content-type .* is not currently supported/');

        new Request('/test', Request::POST, $this->fieldRegistry, ['text/plain']);
    }

    public function testMultipleContentTypesWithUnsupportedThrowsException(): void
    {
        $this->expectException(InvalidSpecificationException::class);
        $this->expectExceptionMessageMatches('/Request content-type .* is not currently supported/');

        new Request('/test', Request::POST, $this->fieldRegistry, ['application/json', 'text/csv']);
    }

    public function testMixedValidContentTypes(): void
    {
        $contentTypes = ['application/json', 'application/vnd.sdmx.data+json', 'application/xml'];
        $request      = new Request('/test', Request::POST, $this->fieldRegistry, $contentTypes);
        self::assertEquals($contentTypes, $request->bodyContentTypes);
    }
}
