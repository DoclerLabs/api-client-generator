<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Naming;

use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Naming\SchemaNaming
 */
class SchemaNamingTest extends TestCase
{
    /**
     * @dataProvider schemaClassNameProvider
     */
    public function testGetSchemaClassName(string $schemaName, bool $isReserved, string $expectedClassName): void
    {
        self::assertSame($isReserved, SchemaNaming::isReservedClassName($schemaName));
        self::assertSame($expectedClassName, SchemaNaming::getSchemaClassName($schemaName));
    }

    public function schemaClassNameProvider(): array
    {
        return [
            'keyword'             => ['Match', true, 'MatchSchema'],
            'keyword in capitals' => ['LIST', true, 'LISTSchema'],
            'reserved type name'  => ['Mixed', true, 'MixedSchema'],
            'reserved class name' => ['Self', true, 'SelfSchema'],
            'soft reserved word'  => ['Enum', false, 'Enum'],
            'contains a keyword'  => ['MatchResult', false, 'MatchResult'],
            'regular name'        => ['Player', false, 'Player'],
        ];
    }

    public function testGetUniqueName(): void
    {
        self::assertSame('ASTERISK', SchemaNaming::getUniqueName('ASTERISK', ['EQUALS']));
        self::assertSame('ASTERISK_2', SchemaNaming::getUniqueName('ASTERISK', ['ASTERISK']));
        self::assertSame('ASTERISK_3', SchemaNaming::getUniqueName('ASTERISK', ['ASTERISK', 'ASTERISK_2']));
        self::assertSame('Foo2', SchemaNaming::getUniqueName('Foo', ['Foo'], ''));
    }
}
