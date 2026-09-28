<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Input;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\EnumGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationAwareTrait;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Input\ClassNameCollisionResolver
 */
class ClassNameCollisionResolverTest extends TestCase
{
    use ConfigurationAwareTrait;

    private const SPECIFICATION = __DIR__ . '/../Generator/Schema/classNameCollisions.yaml';

    /**
     * @dataProvider phpVersionProvider
     */
    public function testDifferentSchemasGetDistinctClassNames(
        float $phpVersion,
        array $expectedClassNames,
        array $expectedWarnings
    ): void {
        $container = $this->getContainerWith(ConfigurationBuilder::fake()->withPhpVersion($phpVersion)->build());

        $warnings = [];
        set_error_handler(
            static function (int $level, string $message) use (&$warnings): bool {
                if (str_starts_with($message, 'Class name')) {
                    $warnings[] = $message;
                }

                return true;
            },
            E_USER_WARNING
        );

        try {
            $specification = $container[Parser::class]->parse(
                $container[FileReader::class]->read(self::SPECIFICATION),
                self::SPECIFICATION
            );
        } finally {
            restore_error_handler();
        }

        $fileRegistry = new PhpFileCollection();
        $container[EnumGenerator::class]->generate($specification, $fileRegistry);
        $container[SchemaGenerator::class]->generate($specification, $fileRegistry);
        $classNames = array_map(
            static fn (string $className): string => substr($className, strlen('Test\\Schema\\')),
            array_keys(iterator_to_array($fileRegistry))
        );
        sort($classNames);

        self::assertSame($expectedClassNames, $classNames);
        self::assertSame($expectedWarnings, $warnings);
    }

    public function phpVersionProvider(): array
    {
        $hotDealWarning = 'Class name HotDeal is shared by different schemas, HotDeal2 is used for the one in getPage. Consider giving the schemas distinct names.';

        return [
            'Enums and schemas with PHP 8.1' => [
                PhpVersion::VERSION_PHP81,
                [
                    'Call',
                    'CallStatus2Enum',
                    'CallStatusEnum',
                    'CriterionStatusEq2Enum',
                    'CriterionStatusEqEnum',
                    'HotDeal',
                    'HotDeal2',
                    'PageItem',
                    'Section',
                    // identical definitions share their class
                    'StatusEnum',
                ],
                [
                    'Class name CallStatusEnum is shared by different schemas, CallStatus2Enum is used for the one in getCall. Consider giving the schemas distinct names.',
                    'Class name CriterionStatusEqEnum is shared by different schemas, CriterionStatusEq2Enum is used for the one in findHosts. Consider giving the schemas distinct names.',
                    $hotDealWarning,
                ],
            ],
            'Schemas with PHP 7.4, enums are constants' => [
                PhpVersion::VERSION_PHP74,
                ['Call', 'HotDeal', 'HotDeal2', 'PageItem', 'Section'],
                [$hotDealWarning],
            ],
        ];
    }
}
