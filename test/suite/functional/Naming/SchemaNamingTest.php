<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Naming;

use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class SchemaNamingTest extends TestCase
{
    private FileReader $specificationReader;
    private Parser     $specificationParser;

    protected function setUp(): void
    {
        $container = new Container();
        $container->register(new ServiceProvider());

        set_error_handler(
            static function (int $code, string $message) {
            },
            E_USER_WARNING
        );

        $this->specificationReader = $container[FileReader::class];
        $this->specificationParser = $container[Parser::class];
    }

    public function testSchemaNoDuplicates()
    {
        $absoluteSpecificationPath = __DIR__ . '/schemaNoDuplicates.yaml';

        self::assertFileExists($absoluteSpecificationPath);

        $data             = $this->specificationReader->read($absoluteSpecificationPath);
        $specification    = $this->specificationParser->parse($data, $absoluteSpecificationPath);
        $uniqueClassNames = array_values(array_map(
            static function (Field $field) {
                return $field->getPhpClassName();
            },
            $specification->getCompositeFields()->getUniqueByPhpClassName()
        ));

        self::assertEquals(
            [
                'GetOrderResponseBody',
                'Order',
                'FindShippingResponseBody',
                'ShippingCollection',
                'Shipping',
                'ShippingOrder',
            ],
            $uniqueClassNames
        );
    }
}
