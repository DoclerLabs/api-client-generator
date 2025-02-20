<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Input;

use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationAwareTrait;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Input\Parser
 */
class SpecificationTest extends TestCase
{
    use ConfigurationAwareTrait;

    protected Parser $sut;

    /**
     * @dataProvider contentTypesTestProvider
     */
    public function testAllContentTypesArrayPopulatedCorrectly(array $data, array $expectedResult): void
    {
        $specification = $this->sut->parse($data, '/openapi.yaml');

        static::assertSame($expectedResult, $specification->getAllContentTypes());
    }

    public function contentTypesTestProvider(): array
    {
        return [
            'No serializers required' => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths' => [
                        '/users/{userId}' => [
                            'parameters' => [
                                [
                                    'in'       => 'path',
                                    'required' => true,
                                    'name'     => 'userId',
                                    'schema'   => [
                                        'type' => 'integer',
                                    ],
                                ],
                            ],
                            'delete' => [
                                'operationId' => 'deleteUser',
                                'responses'   => [
                                    '204' => [
                                        'description' => 'OK',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [],
            ],
            'Serializer specified in request' => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths' => [
                        '/users' => [
                            'post' => [
                                'operationId' => 'createUser',
                                'requestBody' => [
                                    'required' => true,
                                    'content'  => [
                                        'application/x-www-form-urlencoded' => [
                                            'schema' => [
                                                'type'       => 'object',
                                                'properties' => [
                                                    'name' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'application/xml' => [
                                            'schema' => [
                                                'type'       => 'object',
                                                'properties' => [
                                                    'name' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'responses' => [
                                    '201' => [
                                        'description' => 'Created',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'application/xml',
                    'application/x-www-form-urlencoded',
                ],
            ],
            'Serializer specified in response' => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths' => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'createUser',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'Array of users',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type'       => 'object',
                                                    'properties' => [
                                                        'name' => [
                                                            'type' => 'string',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            'application/xml' => [
                                                'schema' => [
                                                    'type'       => 'object',
                                                    'properties' => [
                                                        'name' => [
                                                            'type' => 'string',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'application/xml',
                    'application/json',
                ],
            ],
            'Serializer specified in request and response' => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths' => [
                        '/users/{userId}' => [
                            'parameters' => [
                                [
                                    'in'       => 'path',
                                    'required' => true,
                                    'name'     => 'userId',
                                    'schema'   => [
                                        'type' => 'integer',
                                    ],
                                ],
                            ],
                            'patch' => [
                                'operationId' => 'createUser',
                                'requestBody' => [
                                    'required' => true,
                                    'content'  => [
                                        'application/x-www-form-urlencoded' => [
                                            'schema' => [
                                                'type'       => 'object',
                                                'properties' => [
                                                    'name' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'application/xml' => [
                                            'schema' => [
                                                'type'       => 'object',
                                                'properties' => [
                                                    'name' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'responses' => [
                                    '200' => [
                                        'description' => 'Modified user',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type'       => 'object',
                                                    'properties' => [
                                                        'name' => [
                                                            'type' => 'string',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'application/xml',
                    'application/x-www-form-urlencoded',
                    'application/json',
                ],
            ],
        ];
    }

    public function testGetFromInfo(): void
    {
        $data = [
            'openapi' => '3.0.0',
            'info'    => [
                'title'   => 'Sample API',
                'version' => '1.2.3',
            ],
            'paths' => [
                '/foo/bar' => [
                    'get' => [
                        'operationId' => 'getFooBar',
                        'responses'   => [
                            '200' => [
                                'description' => 'Ge',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $specification = $this->sut->parse($data, '/openapi.yaml');
        static::assertSame('1.2.3', $specification->getVersion());
        static::assertSame('Sample API', $specification->getTitle());
        static::assertSame(false, $specification->hasLicense());

        $data['info']['license']['name'] = 'License name';

        $specificationWithLicense = $this->sut->parse($data, '/openapi.yaml');
        static::assertSame(true, $specificationWithLicense->hasLicense());
        static::assertSame('License name', $specificationWithLicense->getLicenseName());
    }

    protected function setUp(): void
    {
        $container = $this->getContainerWith(ConfigurationBuilder::fake()->build());

        $this->sut = $container[Parser::class];
    }
}
