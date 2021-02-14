<?php

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer
 */
class FormEncodedContentTypeSerializerTest extends TestCase
{
    private FormUrlencodedContentTypeSerializer $sut;

    protected function setUp(): void
    {
        $this->sut = new FormUrlencodedContentTypeSerializer();
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
        self::assertEquals(FormUrlencodedContentTypeSerializer::MIME_TYPE, $this->sut->getMimeType());
    }

    public function validCasesProvider(): array
    {
        return [
            [
                [1, 2, 3],
                '0=1&1=2&2=3'
            ],
            [
                [
                    'employees' => [
                        ['name' => 'Shyam', 'email' => 'shyamjaiswal@gmail.com'],
                        ['name' => 'Bob', 'email' => 'bob32@gmail.com'],
                        ['name' => 'Jai', 'email' => 'jai87@gmail.com'],
                    ]
                ],
                'employees%5B0%5D%5Bname%5D=Shyam&employees%5B0%5D%5Bemail%5D=shyamjaiswal%40gmail.com&employees%5B1%5D%5Bname%5D=Bob&employees%5B1%5D%5Bemail%5D=bob32%40gmail.com&employees%5B2%5D%5Bname%5D=Jai&employees%5B2%5D%5Bemail%5D=jai87%40gmail.com'
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
                'menu%5Bid%5D=file&menu%5Bvalue%5D=File&menu%5Bpopup%5D%5Bmenuitem%5D%5B0%5D%5Bvalue%5D=New&menu%5Bpopup%5D%5Bmenuitem%5D%5B0%5D%5Bonclick%5D=CreateDoc%28%29&menu%5Bpopup%5D%5Bmenuitem%5D%5B1%5D%5Bvalue%5D=Open&menu%5Bpopup%5D%5Bmenuitem%5D%5B1%5D%5Bonclick%5D=OpenDoc%28%29&menu%5Bpopup%5D%5Bmenuitem%5D%5B2%5D%5Bvalue%5D=Save&menu%5Bpopup%5D%5Bmenuitem%5D%5B2%5D%5Bonclick%5D=SaveDoc%28%29'
            ],
        ];
    }
}
