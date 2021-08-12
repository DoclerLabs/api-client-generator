<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Input;

use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * \DoclerLabs\ApiClientGenerator\Input\Parser::getAllContentTypes
 */
class SpecificationTest extends TestCase
{
    protected Parser $sut;

    /**
     * @dataProvider contentTypesTestProvider
     */
    public function testAllContentTypesArrayPopulatedCorrectly(array $data, array $expectedResult): void
    {
        $specificaton = $this->sut->parse($data, '/openapi.yaml');
        static::assertSame($specificaton->getAllContentTypes(), $expectedResult);
    }

    public function contentTypesTestProvider(): array
    {
        return [
            'No serializers required'         => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
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
                            'delete'     => [
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
                    'paths'   => [
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
                                'responses'   => [
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
            'Serializer specified in pesponse' => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'createUser',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'Array of users',
                                        'content' => [
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
        ];
    }

    protected function setUp(): void
    {
        $container = new Container();
        $container->register(new ServiceProvider());

        set_error_handler(
            static function (int $code, string $message) {
            },
            E_USER_WARNING
        );

        $this->sut = $container[Parser::class];
    }
}
