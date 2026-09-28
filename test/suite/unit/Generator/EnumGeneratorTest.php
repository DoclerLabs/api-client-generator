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
            'invalid characters only'     => ['+', ''],
        ];
    }
}
