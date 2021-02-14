<?php

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Copy\Serializer\ContentType;

use DoclerLabs\ApiClientGenerator\Output\Copy\Schema\SerializableInterface;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer
 */
class XmlContentTypeSerializerTest extends TestCase
{
    private XmlContentTypeSerializer $sut;

    protected function setUp(): void
    {
        $this->sut = new XmlContentTypeSerializer();
    }

    /**
     * @dataProvider validCasesProvider
     */
    public function testEncode(array $input, string $expectedResult): void
    {
        $serializable = $this->getMockBuilder(SerializableInterface::class)
                             ->setMockClassName('Test')
                             ->getMock();
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
        self::assertEquals(XmlContentTypeSerializer::MIME_TYPE, $this->sut->getMimeType());
    }

    public function validCasesProvider(): array
    {
        return [
            [
                ['numbers' => ['value' => [1, 2, 3]]],
                '<?xml version="1.0" encoding="UTF-8"?>
<Test><numbers><value>1</value><value>2</value><value>3</value></numbers></Test>
'
            ],
            [
                [
                    'employees' => [
                        'employee' => [
                            ['name' => 'Shyam', 'email' => 'shyamjaiswal@gmail.com'],
                            ['name' => 'Bob', 'email' => 'bob32@gmail.com'],
                            ['name' => 'Jai', 'email' => 'jai87@gmail.com'],
                        ],
                    ]
                ],
                '<?xml version="1.0" encoding="UTF-8"?>
<Test><employees><employee><name>Shyam</name><email>shyamjaiswal@gmail.com</email></employee><employee><name>Bob</name><email>bob32@gmail.com</email></employee><employee><name>Jai</name><email>jai87@gmail.com</email></employee></employees></Test>
'
            ],
            [
                [
                    'root_node' => [
                        'tag'                    => 'Example tag',
                        'attribute_tag'          => [
                            '@value'      => 'Another tag with attributes',
                            '@attributes' => [
                                'description' => 'This is a tag with attribute'
                            ]
                        ],
                        'cdata_section'          => [
                            '@cdata' => 'This is CDATA section'
                        ],
                        'tag_with_subtag'        => [
                            'sub_tag' => ['Sub tag 1', 'Sub tag 2']
                        ],
                        'mixed_section'          => [
                            '@value'  => 'Hello',
                            '@cdata'  => 'This is another CDATA section',
                            'section' => [
                                [
                                    '@value'      => 'Section number 1',
                                    '@attributes' => [
                                        'id' => 'sec_1'
                                    ]
                                ],
                                [
                                    '@value'      => 'Section number 2',
                                    '@attributes' => [
                                        'id' => 'sec_2'
                                    ]
                                ],
                                [
                                    '@value'      => 'Section number 3',
                                    '@attributes' => [
                                        'id' => 'sec_3'
                                    ]
                                ]
                            ]
                        ],
                        'example:with_namespace' => [
                            'example:sub' => 'Content'
                        ],
                        '@attributes'            => [
                            'xmlns:example' => 'http://example.com'
                        ]
                    ]
                ],
                '<?xml version="1.0" encoding="UTF-8"?>
<Test><root_node xmlns:example="http://example.com"><tag>Example tag</tag><attribute_tag description="This is a tag with attribute">Another tag with attributes</attribute_tag><cdata_section><![CDATA[This is CDATA section]]></cdata_section><tag_with_subtag><sub_tag>Sub tag 1</sub_tag><sub_tag>Sub tag 2</sub_tag></tag_with_subtag><mixed_section>Hello<![CDATA[This is another CDATA section]]><section id="sec_1">Section number 1</section><section id="sec_2">Section number 2</section><section id="sec_3">Section number 3</section></mixed_section><example:with_namespace><example:sub>Content</example:sub></example:with_namespace></root_node></Test>
'
            ],
        ];
    }
}
