<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Input;

use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Input\Parser
 */
class ParserTest extends TestCase
{
    protected Parser $sut;

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

    /**
     * @dataProvider validSpecificationProvider
     */
    public function testParseValidSpecification(array $data): void
    {
        self::assertNotNull($this->sut->parse($data, '/openapi.yaml'));
    }

    /**
     * @dataProvider invalidSpecificationProvider
     */
    public function testParseInvalidSpecification(array $data): void
    {
        $this->expectException(InvalidSpecificationException::class);
        $this->sut->parse($data, '/openapi.yaml');
    }

    public function validSpecificationProvider()
    {
        return [
            'All mandatory fields are in place' => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getUsers',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function invalidSpecificationProvider()
    {
        return [
            'Empty specification file'                        => [
                [],
            ],
            'No paths'                                        => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [],
                ],
            ],
            'Swagger specification version is lower than 3.0' => [
                [
                    'swagger'  => '2.0',
                    'info'     => [
                        'title'       => 'Sample API',
                        'description' => 'API description.',
                        'version'     => '1.0.0',
                    ],
                    'host'     => 'api.example.com',
                    'basePath' => '/v1',
                    'schemes'  => ['https'],
                    'paths'    => [],
                ],
            ],
            'Paths field is missing'                          => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                ],
            ],
            'Responses field is missing'                      => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getUsers',
                            ],
                        ],
                    ],
                ],
            ],
            'Unsupported operation'                           => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'flurp' => [
                                'operationId' => 'flurpThem',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Response without description'                    => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getUsers',
                                'responses'   => [
                                    '200' => [
                                        'content' => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type' => 'integer',
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
            'OneOf keyword is not supported'                          => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'responses' => [
                                    '200' => [
                                        'description' => 'OK',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'oneOf' => [],
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
            'Reference to non-existing schema'                => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getUsers',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type'  => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/User',
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
            'Array schema without items'                      => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getUsers',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type' => 'array',
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
            'Invalid field name'                              => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getUsers',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type'       => 'object',
                                                    'properties' => [
                                                        '4code' => [
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
            ],
            'Invalid reference name'                          => [
                [
                    'openapi'    => '3.0.0',
                    'info'       => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'      => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getItems',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    '$ref' => '#/components/schemas/7Item',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'components' => [
                        'schemas' => [
                            '7Item' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name' =>
                                        [
                                            'type' => 'string',
                                        ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Invalid array item reference name'               => [
                [
                    'openapi'    => '3.0.0',
                    'info'       => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'      => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getItems',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type'  => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/7Item',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'components' => [
                        'schemas' => [
                            '7Item' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name' =>
                                        [
                                            'type' => 'string',
                                        ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Incomplete parameter'                            => [
                [
                    'openapi'    => '3.0.0',
                    'info'       => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'      => [
                        '/users' => [
                            'get' => [
                                'parameters'  => [
                                    ['$ref' => '#/components/parameters/ItemName',],
                                ],
                                'operationId' => 'getItems',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'components' => [
                        'parameters' => [
                            'ItemName' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ],
            'Usage of parameter inside a schema'              => [
                [
                    'openapi'    => '3.0.0',
                    'info'       => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'      => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getItems',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                        'content'     => [
                                            'application/json' => [
                                                'schema' => [
                                                    'type'  => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/parameters/ItemName',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'components' => [
                        'parameters' => [
                            'ItemName' => [
                                'name'   => 'itemName',
                                'in'     => 'query',
                                'schema' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Duplicated operation id'                         => [
                [
                    'openapi' => '3.0.0',
                    'info'    => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'   => [
                        '/users' => [
                            'get' => [
                                'operationId' => 'getUsers',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                    ],
                                ],
                            ],
                        ],
                        '/items' => [
                            'get' => [
                                'operationId' => 'getUsers',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Parameter with invalid origin'                   => [
                [
                    'openapi'    => '3.0.0',
                    'info'       => [
                        'title'   => 'Sample API',
                        'version' => '1.0.0',
                    ],
                    'paths'      => [
                        '/users' => [
                            'get' => [
                                'parameters'  => [
                                    ['$ref' => '#/components/parameters/ItemName',],
                                ],
                                'operationId' => 'getItems',
                                'responses'   => [
                                    '200' => [
                                        'description' => 'OK',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'components' => [
                        'parameters' => [
                            'ItemName' => [
                                'name'   => 'itemName',
                                'in'     => 'somewhere',
                                'schema' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
