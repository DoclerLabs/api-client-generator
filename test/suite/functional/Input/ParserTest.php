<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Input;

use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

/**
 * @coversDefaultClass Parser
 */
class ParserTest extends TestCase
{
    protected Parser             $sut;

    public function setUp(): void
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
     * @dataProvider validFileProvider
     */
    public function testParseValidFile(string $filePath): void
    {
        $this->assertNotNull($this->sut->parseFile($filePath));
    }

    /**
     * @dataProvider invalidFileProvider
     */
    public function testParseInvalidFile(string $filePath): void
    {
        $this->expectException(InvalidSpecificationException::class);
        $this->sut->parseFile($filePath);
    }

    /**
     * @dataProvider validSpecificationProvider
     */
    public function testParseValidSpecification(array $data): void
    {
        $this->assertNotNull($this->sut->parse($data));
    }

    /**
     * @dataProvider invalidSpecificationProvider
     */
    public function testParseInvalidSpecification(array $data): void
    {
        $this->expectException(InvalidSpecificationException::class);
        $this->sut->parse($data);
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
                    'paths'   => [],
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
                            'get' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function validFileProvider()
    {
        return [
            [__DIR__ . '/openapi.yaml'],
            [__DIR__ . '/openapi.yml'],
            [__DIR__ . '/openapi.json'],
        ];
    }

    public function invalidFileProvider()
    {
        return [
            [__DIR__ . '/non_existing'],
            [__DIR__ . '/openapi.php'],
        ];
    }
}
