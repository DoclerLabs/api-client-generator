<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Entity;

use DoclerLabs\ApiClientGenerator\Entity\FieldType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Entity\FieldType
 */
class FieldTypeTest extends TestCase
{
    public function testStatic(): void
    {
        self::assertTrue(FieldType::isSpecificationTypeString('string'));
        self::assertTrue(FieldType::isSpecificationTypeMixed(null));
        self::assertTrue(FieldType::isSpecificationTypeArray('array'));
        self::assertTrue(FieldType::isSpecificationTypeFloat('number'));
        self::assertTrue(FieldType::isSpecificationTypeBoolean('boolean'));
        self::assertTrue(FieldType::isSpecificationTypeInteger('integer'));
        self::assertTrue(FieldType::isSpecificationTypeObject('object'));
    }

    public function testIsString(): void
    {
        $sut = new FieldType(FieldType::SPEC_TYPE_STRING);
        self::assertTrue($sut->isString());
        self::assertEquals(FieldType::PHP_TYPE_STRING, $sut->toPhpType());
        self::assertEquals(FieldType::SPEC_TYPE_STRING, $sut->toSpecificationType());
    }

    public function testIsMixed(): void
    {
        $sut = new FieldType(null);
        self::assertTrue($sut->isMixed());
        self::assertEquals('', $sut->toPhpType());
        self::assertEquals(null, $sut->toSpecificationType());
    }

    public function testIsFloat(): void
    {
        $sut = new FieldType(FieldType::SPEC_TYPE_FLOAT);
        self::assertTrue($sut->isFloat());
        self::assertEquals(FieldType::PHP_TYPE_FLOAT, $sut->toPhpType());
        self::assertEquals(FieldType::SPEC_TYPE_FLOAT, $sut->toSpecificationType());
    }

    public function testIsInteger(): void
    {
        $sut = new FieldType(FieldType::SPEC_TYPE_INTEGER);
        self::assertTrue($sut->isInteger());
        self::assertEquals(FieldType::PHP_TYPE_INTEGER, $sut->toPhpType());
        self::assertEquals(FieldType::SPEC_TYPE_INTEGER, $sut->toSpecificationType());
    }

    public function testIsObject(): void
    {
        $sut = new FieldType(FieldType::SPEC_TYPE_OBJECT);
        self::assertTrue($sut->isObject());
        self::assertEquals(FieldType::PHP_TYPE_OBJECT, $sut->toPhpType());
        self::assertEquals(FieldType::SPEC_TYPE_OBJECT, $sut->toSpecificationType());
    }

    public function testIsBoolean(): void
    {
        $sut = new FieldType(FieldType::SPEC_TYPE_BOOLEAN);
        self::assertTrue($sut->isBoolean());
        self::assertEquals(FieldType::PHP_TYPE_BOOLEAN, $sut->toPhpType());
        self::assertEquals(FieldType::SPEC_TYPE_BOOLEAN, $sut->toSpecificationType());
    }

    public function testIsArray(): void
    {
        $sut = new FieldType(FieldType::SPEC_TYPE_ARRAY);
        self::assertTrue($sut->isArray());
        self::assertEquals(FieldType::PHP_TYPE_ARRAY, $sut->toPhpType());
        self::assertEquals(FieldType::SPEC_TYPE_ARRAY, $sut->toSpecificationType());
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FieldType('invalid');
    }
}
