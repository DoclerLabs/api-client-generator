<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Input;

use DoclerLabs\ApiClientGenerator\Input\PhpNameValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Input\PhpNameValidator
 */
class PhpNameValidatorTest extends TestCase
{
    /** @var PhpNameValidator */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new PhpNameValidator();
    }

    /**
     * @dataProvider classNameProvider
     *
     * @param string $input
     * @param bool   $expectedResult
     */
    public function testIsValidClassName(string $input, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, $this->sut->isValidClassName($input));
    }

    /**
     * @dataProvider variableNameProvider
     *
     * @param string $input
     * @param bool   $expectedResult
     */
    public function testIsValidVariableName(string $input, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, $this->sut->isValidVariableName($input));
    }

    public function classNameProvider(): array
    {
        return [
            [
                'Class',
                true,
            ],
            [
                'C_l_a_s_s',
                true,
            ],
            [
                'C-l-a-s-s',
                false,
            ],
            [
                'class',
                false,
            ],
            [
                '9class',
                false,
            ],
            [
                '_class',
                false,
            ],
            [
                '?class',
                false,
            ],
            [
                ' class',
                false,
            ],
        ];
    }

    public function variableNameProvider(): array
    {
        return [
            [
                'variable',
                true,
            ],
            [
                'Variable',
                true,
            ],
            [
                'variaBle',
                true,
            ],
            [
                '0variable',
                false,
            ],
            [
                '_variable',
                true,
            ],
            [
                'cdsf_ds-fdsc_sdf',
                false,
            ],
            [
                'variab-le',
                false,
            ],
            [
                ' variable',
                false,
            ],
        ];
    }
}
