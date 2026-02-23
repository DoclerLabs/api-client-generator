<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Serializer;

use DoclerLabs\ApiClientException\UnexpectedResponseException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer
 */
class BodySerializerTest extends TestCase
{
    private BodySerializer $sut;

    protected function setUp(): void
    {
        $this->sut = new BodySerializer();
        $this->sut->add(new JsonContentTypeSerializer());
    }

    public function testUnserializeResponseWithStandardJsonContentType(): void
    {
        $response = $this->createResponseMock('application/json', '{"key":"value"}');

        $result = $this->sut->unserializeResponse($response);

        self::assertEquals(['key' => 'value'], $result);
    }

    public function testUnserializeResponseWithJsonContentTypeAndCharset(): void
    {
        $response = $this->createResponseMock('application/json; charset=utf-8', '{"key":"value"}');

        $result = $this->sut->unserializeResponse($response);

        self::assertEquals(['key' => 'value'], $result);
    }

    /**
     * @dataProvider jsonSuffixContentTypesProvider
     */
    public function testUnserializeResponseWithRfc6839JsonSuffixFallsBackToJsonSerializer(
        string $contentType
    ): void {
        $response = $this->createResponseMock($contentType, '{"data":"test"}');

        $result = $this->sut->unserializeResponse($response);

        self::assertEquals(['data' => 'test'], $result);
    }

    public function jsonSuffixContentTypesProvider(): array
    {
        return [
            'SDMX JSON'              => ['application/vnd.sdmx.data+json'],
            'SDMX JSON with version' => ['application/vnd.sdmx.data+json;version=1.0.0-wd'],
            'HAL JSON'               => ['application/hal+json'],
            'JSON-LD'                => ['application/ld+json'],
            'Problem JSON'           => ['application/problem+json'],
            'Custom vendor JSON'     => ['application/vnd.custom.api+json'],
            'With charset'           => ['application/vnd.example+json; charset=utf-8'],
        ];
    }

    public function testUnserializeResponseWithUnsupportedContentTypeThrowsException(): void
    {
        $response = $this->createResponseMock('text/plain', 'plain text');

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('Serializer for `text/plain` is not found.');

        $this->sut->unserializeResponse($response);
    }

    public function testUnserializeResponseWithEmptyBodyReturnsEmptyArray(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(0);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);

        $result = $this->sut->unserializeResponse($response);

        self::assertEquals([], $result);
    }

    public function testJsonSuffixWithoutJsonSerializerThrowsException(): void
    {
        $serializerWithoutJson = new BodySerializer();
        $response              = $this->createResponseMock('application/vnd.custom+json', '{}');

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('Serializer for `application/vnd.custom+json` is not found.');

        $serializerWithoutJson->unserializeResponse($response);
    }

    private function createResponseMock(string $contentType, string $bodyContent): ResponseInterface
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('getSize')->willReturn(strlen($bodyContent));
        $body->method('getContents')->willReturn($bodyContent);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);
        $response->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn($contentType);

        return $response;
    }
}
