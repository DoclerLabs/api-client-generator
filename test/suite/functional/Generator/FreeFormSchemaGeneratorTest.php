<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\FreeFormSchemaGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\FreeFormSchemaGenerator
 */
class FreeFormSchemaGeneratorTest extends AbstractGeneratorTest
{
    public function exampleProvider(): array
    {
        $yaml = '/FreeFormSchema/item.yaml';
        $className = self::BASE_NAMESPACE . SchemaGenerator::NAMESPACE_SUBPATH . '\\Item';

        return [
            'With PHP 7.0' => [
                $yaml,
                '/FreeFormSchema/ItemPhp70.php',
                $className,
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP70)->build(),
            ],
            'With PHP 7.2' => [
                $yaml,
                '/FreeFormSchema/ItemPhp72.php',
                $className,
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP72)->build(),
            ],
            'With PHP 7.4' => [
                $yaml,
                '/FreeFormSchema/ItemPhp74.php',
                $className,
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP74)->build(),
            ],
        ];
    }

    protected function generatorClassName(): string
    {
        return FreeFormSchemaGenerator::class;
    }
}
