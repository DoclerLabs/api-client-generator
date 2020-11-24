<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\QuerySerializer;
use JsonSerializable;
use PHPUnit\Framework\TestCase;

class QuerySerializerTest extends TestCase
{
    private QuerySerializer $sut;

    public function setUp(): void
    {
        $this->sut = new QuerySerializer();
    }

    public function testSerialization(): void
    {
        $serializable = $this->createMock(SerializableInterface::class);
        $serializable->expects($this->once())
            ->method('toArray')
            ->willReturn(['foo' => 'bar']);

        $jsonSerializable = $this->createMock(JsonSerializable::class);
        $jsonSerializable->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(['bar' => 'foo']);

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())
            ->method('getRawQueryParameters')
            ->willReturn(
                [
                    'dateTime'         => new DateTimeImmutable(
                        '2020-11-24 01:02:03',
                        new DateTimeZone('Europe/Luxembourg')
                    ),
                    'nullable'         => null,
                    'integer'          => 11,
                    'string'           => 'simple string',
                    'float'            => 42.13,
                    'booleanTrue'      => true,
                    'booleanFalse'     => false,
                    'serializable'     => $serializable,
                    'jsonSerializable' => $jsonSerializable,
                ]
            );

        $this->assertEquals(
            'dateTime=2020-11-24T01%3A02%3A03%2B01%3A00&integer=11&string=simple%20string&float=42.13&booleanTrue=1&booleanFalse=0&serializable%5Bfoo%5D=bar&jsonSerializable%5Bbar%5D=foo',
            $this->sut->serializeRequest($request)
        );
    }
}
