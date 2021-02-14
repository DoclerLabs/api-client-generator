<?php

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer
 */
class JsonContentTypeSerializerTest extends TestCase
{
    private JsonContentTypeSerializer $sut;

    protected function setUp(): void
    {
        $this->sut = new JsonContentTypeSerializer();
    }

    /**
     * @dataProvider validCasesProvider
     */
    public function testEncode(array $input, string $expectedResult): void
    {
        $serializable = $this->createMock(SerializableInterface::class);
        $serializable->expects(self::once())
                     ->method('toArray')
                     ->willReturn($input);

        self::assertEquals($expectedResult, $this->sut->encode($serializable));
    }

    /**
     * @dataProvider validCasesProvider
     * @dataProvider literalCasesProvider
     */
    public function testDecode(array $expectedResult, string $input): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->expects(self::once())
             ->method('getContents')
             ->willReturn($input);

        self::assertEquals($expectedResult, $this->sut->decode($body));
    }

    public function testMimeType(): void
    {
        self::assertEquals(JsonContentTypeSerializer::MIME_TYPE, $this->sut->getMimeType());
    }

    public function validCasesProvider(): array
    {
        return [
            [
                [
                    'employees' => [
                        ['name' => 'Shyam', 'email' => 'shyamjaiswal@gmail.com'],
                        ['name' => 'Bob', 'email' => 'bob32@gmail.com'],
                        ['name' => 'Jai', 'email' => 'jai87@gmail.com'],
                    ]
                ],
                '{"employees":[{"name":"Shyam","email":"shyamjaiswal@gmail.com"},{"name":"Bob","email":"bob32@gmail.com"},{"name":"Jai","email":"jai87@gmail.com"}]}'
            ],
            [
                [
                    'menu' => [
                        'id'    => 'file',
                        'value' => 'File',
                        'popup' => [
                            'menuitem' => [
                                ['value' => 'New', 'onclick' => 'CreateDoc()'],
                                ['value' => 'Open', 'onclick' => 'OpenDoc()'],
                                ['value' => 'Save', 'onclick' => 'SaveDoc()'],
                            ],
                        ],
                    ]
                ],
                '{"menu":{"id":"file","value":"File","popup":{"menuitem":[{"value":"New","onclick":"CreateDoc()"},{"value":"Open","onclick":"OpenDoc()"},{"value":"Save","onclick":"SaveDoc()"}]}}}'
            ],
            [
                [1, 2, 3],
                '[1,2,3]'
            ],
        ];
    }

    public function literalCasesProvider(): array
    {
        return [
            [
                [
                    '__literalResponseValue' => null
                ],
                'null'
            ],
            [
                [
                    '__literalResponseValue' => false
                ],
                'false'
            ],
            [
                [
                    '__literalResponseValue' => 0
                ],
                '0'
            ],
            [
                [
                    '__literalResponseValue' => 'asd'
                ],
                '"asd"'
            ],
        ];
    }
}
