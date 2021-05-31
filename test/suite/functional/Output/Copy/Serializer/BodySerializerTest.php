<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Output\Copy\Serializer;

use DoclerLabs\ApiClientException\UnexpectedResponseException;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class BodySerializerTest extends TestCase
{
    private BodySerializer $sut;

    protected function setUp(): void
    {
        $this->sut = new BodySerializer();
        $this->sut
            ->add(new JsonContentTypeSerializer())
            ->add(new VdnApiJsonContentTypeSerializer())
            ->add(new FormUrlencodedContentTypeSerializer());
    }

    public function testUnserializeJsonResponseForEmptyBody(): void
    {
        $response = $this->getResponse('application/json', '');

        self::assertEquals([], $this->sut->unserializeResponse($response));
    }

    public function testUnserializeVdnApiJsonResponseForEmptyBody(): void
    {
        $response = $this->getResponse('application/vnd.api+json', '');

        self::assertEquals([], $this->sut->unserializeResponse($response));
    }

    public function testExceptionIsThrownForUnsupportedContentType(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        $response = $this->getResponse('video/h264', 'body content');

        $this->sut->unserializeResponse($response);
    }

    public function testUnserializeFormUrlencodedResponse(): void
    {
        $response = $this->getResponse('application/x-www-form-urlencoded', 'key=value');

        self::assertEquals(['key' => 'value'], $this->sut->unserializeResponse($response));
    }

    /**
     * @dataProvider applicationJsonContentTypeStringsProvider
     */
    public function testUnserializeJsonResponse(string $contentType): void
    {
        $body        = ['key' => 'value'];
        $encodedBody = json_encode($body, JSON_THROW_ON_ERROR);

        $response = $this->getResponse($contentType, $encodedBody);

        self::assertEquals($body, $this->sut->unserializeResponse($response));
    }

    public function applicationJsonContentTypeStringsProvider(): array
    {
        return [
            'default'            => [
                'application/json',
            ],
            'with extra symbols' => [
                'application/json; ',
            ],
            'upper case'         => [
                'applicaTion/JSON',
            ],
            'with charset'       => [
                'application/json; charset=ISO-8859-4',
            ],
        ];
    }

    private function getResponse(string $contentType, string $body): Response
    {
        /** @var Response $response */
        $response = (new Response())->withHeader('Content-type', $contentType);

        if ($body) {
            $resource     = fopen('php://memory', 'wb+');
            $guzzleStream = new Stream($resource);
            $guzzleStream->write($body);
            $response = $response->withBody($guzzleStream);
        }

        return $response;
    }
}
