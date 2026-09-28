<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Request;

use DateTimeImmutable;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\LiteralRequestBody;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\BodySerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\ContentTypeSerializerInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Copy\Request\LiteralRequestBody
 */
class LiteralRequestBodyTest extends TestCase
{
    /**
     * @dataProvider literalValuesProvider
     */
    public function testToArrayWrapsTheValueUnderTheLiteralKey($value, $expectedValue): void
    {
        $body = new LiteralRequestBody($value);

        self::assertSame([ContentTypeSerializerInterface::LITERAL_VALUE_KEY => $expectedValue], $body->toArray());
    }

    public function literalValuesProvider(): array
    {
        $date = new DateTimeImmutable('2026-09-28T10:11:12+02:00');

        return [
            'integer'         => [42, 42],
            'float'           => [1.5, 1.5],
            'string'          => ['text', 'text'],
            'boolean'         => [false, false],
            'null'            => [null, null],
            'date-time'       => [$date, '2026-09-28T10:11:12+02:00'],
            'array of scalar' => [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2]],
            'array of dates'  => [[$date], ['2026-09-28T10:11:12+02:00']],
        ];
    }

    /**
     * @dataProvider serializedBodiesProvider
     */
    public function testBodySerializerEncodesTheLiteralValue(string $contentType, $value, string $expectedBody): void
    {
        $serializer = (new BodySerializer())
            ->add(new JsonContentTypeSerializer())
            ->add(new VdnApiJsonContentTypeSerializer());

        $request = $this->createMock(RequestInterface::class);
        $request->method('getContentType')->willReturn($contentType);
        $request->method('getBody')->willReturn(new LiteralRequestBody($value));

        self::assertSame($expectedBody, $serializer->serializeRequest($request));
    }

    public function serializedBodiesProvider(): array
    {
        return [
            'integer as json'     => ['application/json', 123, '123'],
            'string as json'      => ['application/json', 'text', '"text"'],
            'boolean as json'     => ['application/json', true, 'true'],
            'null as json'        => ['application/json', null, 'null'],
            'list as json'        => ['application/json', [1, 2], '[1,2]'],
            'integer as json api' => ['application/vnd.api+json', 123, '123'],
            'integer as +json'    => ['application/problem+json', 123, '123'],
            'date-time as json'   => ['application/json', new DateTimeImmutable('2026-09-28T10:11:12Z'), '"2026-09-28T10:11:12+00:00"'],
        ];
    }
}
