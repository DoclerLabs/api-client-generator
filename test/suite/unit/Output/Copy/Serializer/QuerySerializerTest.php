<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\QuerySerializer;
use JsonSerializable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\QuerySerializer
 */
class QuerySerializerTest extends TestCase
{
    private QuerySerializer $sut;

    protected function setUp(): void
    {
        $this->sut = new QuerySerializer();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSerialization(array $rawQueryParameters, array $expected): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects(self::once())
                ->method('getRawQueryParameters')
                ->willReturn($rawQueryParameters);

        parse_str($this->sut->serializeRequest($request), $actual);
        self::assertSame(
            $expected,
            $actual
        );
    }

    public function dataProvider(): array
    {
        $serializable = $this->createMock(SerializableInterface::class);
        $serializable->expects(self::once())
                     ->method('toArray')
                     ->willReturn($serialized = ['foo' => 'bar']);

        $jsonSerializable = $this->createMock(JsonSerializable::class);
        $jsonSerializable->expects(self::once())
                         ->method('jsonSerialize')
                         ->willReturn($jsonSerialized = ['bar' => 'foo']);

        return [
            'DateTime'                   => [
                [
                    'dateTime' => $dateTime = new DateTimeImmutable(
                        '2020-11-24 01:02:03',
                        new DateTimeZone('Europe/Luxembourg')
                    ),
                ],
                [
                    'dateTime' => $dateTime->format(DateTimeInterface::RFC3339),
                ],
            ],
            'Nullable'                   => [
                [
                    'nullable' => null,
                ],
                [],
            ],
            'Integer'                    => [
                [
                    'integer' => $integer = 11,
                ],
                [
                    'integer' => (string)$integer,
                ],
            ],
            'String'                     => [
                [
                    'string' => $string = 'simple string',
                ],
                [
                    'string' => $string,
                ],
            ],
            'Float'                      => [
                [
                    'float' => $float = 42.13,
                ],
                [
                    'float' => (string)$float,
                ],
            ],
            'Boolean'                    => [
                [
                    'booleanTrue'  => $booleanTrue = true,
                    'booleanFalse' => $booleanFalse = false,
                ],
                [
                    'booleanTrue'  => (string)(int)$booleanTrue,
                    'booleanFalse' => (string)(int)$booleanFalse,
                ],
            ],
            'Serializable interface'     => [
                [
                    'serializable' => $serializable,
                ],
                [
                    'serializable' => $serialized,
                ],
            ],
            'JsonSerializable interface' => [
                [
                    'jsonSerializable' => $jsonSerializable,
                ],
                [
                    'jsonSerializable' => $jsonSerialized,
                ],
            ],
        ];
    }
}
