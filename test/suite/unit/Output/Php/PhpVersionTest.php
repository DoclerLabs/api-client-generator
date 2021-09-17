<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Php;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Ast\PhpVersion
 */
class PhpVersionTest extends TestCase
{
    public function testInvalidVersion()
    {
        $this->expectException(UnexpectedValueException::class);
        new PhpVersion('8.0');
    }

    public function testIsParameterTypeHintSupported()
    {
        self::assertFalse((new PhpVersion(PhpVersion::VERSION_PHP70))->isPropertyTypeHintSupported());
        self::assertFalse(
            (new PhpVersion(
                PhpVersion::VERSION_PHP71
            ))->isPropertyTypeHintSupported()
        );
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP74))->isPropertyTypeHintSupported());
    }

    public function testIsVoidTypeHintSupported()
    {
        self::assertFalse((new PhpVersion(PhpVersion::VERSION_PHP70))->isVoidReturnTypeSupported());
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP71))->isVoidReturnTypeSupported());
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP74))->isVoidReturnTypeSupported());
    }

    public function testIsNullableTypeHintSupported()
    {
        self::assertFalse((new PhpVersion(PhpVersion::VERSION_PHP70))->isNullableTypeHintSupported());
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP71))->isNullableTypeHintSupported());
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP74))->isNullableTypeHintSupported());
    }

    public function testIsClassConstantVisibilitySupported()
    {
        self::assertFalse(
            (new PhpVersion(
                PhpVersion::VERSION_PHP70
            ))->isClassConstantVisibilitySupported()
        );
        self::assertTrue(
            (new PhpVersion(PhpVersion::VERSION_PHP71))->isClassConstantVisibilitySupported()
        );
        self::assertTrue(
            (new PhpVersion(
                PhpVersion::VERSION_PHP74
            ))->isClassConstantVisibilitySupported()
        );
    }
}
