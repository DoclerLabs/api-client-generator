<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Generator;

use DoclerLabs\ApiClientGenerator\Generator\EnumGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\EnumGenerator
 */
class EnumGeneratorTest extends TestCase
{
    /**
     * @dataProvider caseNameProvider
     */
    public function testGetCaseName(string $value, string $expectedCaseName): void
    {
        self::assertSame($expectedCaseName, EnumGenerator::getCaseName($value));
    }

    public function caseNameProvider(): array
    {
        return [
            'word'                        => ['active', 'ACTIVE'],
            'separators'                  => ['in-progress/soon.done now', 'IN_PROGRESS_SOON_DONE_NOW'],
            'integer'                     => ['2', 'V_2'],
            'leading digit'               => ['+7 days', 'V_7_DAYS'],
            'leading digit, no separator' => ['3d', 'V_3D'],
            'decimal'                     => ['1.5', 'V_1_5'],
            'negative number'             => ['-1', '_1'],
            'symbol only'                 => ['+', 'PLUS'],
            'asterisk'                    => ['*', 'ASTERISK'],
            'equals sign'                 => ['=', 'EQUALS'],
            'several symbols'             => ['>=', 'GREATER_THAN_EQUALS'],
            'non-ASCII character only'    => ['€', 'U20AC'],
            'empty string'                => ['', 'EMPTY'],
        ];
    }

    /**
     * @dataProvider caseNamesProvider
     */
    public function testGetCaseNames(array $values, array $expectedCaseNames): void
    {
        self::assertSame($expectedCaseNames, EnumGenerator::getCaseNames($values));
    }

    public function caseNamesProvider(): array
    {
        return [
            'symbol among words'                    => [['=', 'BETWEEN', 'IN'], ['EQUALS', 'BETWEEN', 'IN']],
            'symbolic name taken by a later value'  => [['*', 'asterisk'], ['ASTERISK_2', 'ASTERISK']],
            'symbolic name taken by an early value' => [['equals', '='], ['EQUALS', 'EQUALS_2']],
            'integers'                              => [[1, 2], ['V_1', 'V_2']],
        ];
    }

    public function testGetCaseNameOfValue(): void
    {
        self::assertSame('ASTERISK_2', EnumGenerator::getCaseNameOfValue(['*', 'asterisk'], '*'));
        self::assertSame('ASTERISK', EnumGenerator::getCaseNameOfValue(['*', 'asterisk'], 'asterisk'));
        self::assertSame('V_2', EnumGenerator::getCaseNameOfValue([1, 2], 2));
        self::assertSame('UNKNOWN', EnumGenerator::getCaseNameOfValue(['*'], 'unknown'));
    }
}
