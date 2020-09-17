<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Php;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @coversDefaultClass \DoclerLabs\ApiClientGenerator\Ast\Resolver\PhpVersion
 */
class PhpVersionResolverTest extends TestCase
{
    public function testInvalidVersion()
    {
        $this->expectException(UnexpectedValueException::class);
        new \DoclerLabs\ApiClientGenerator\Ast\PhpVersion('8.0');
    }

    public function testIsParameterTypeHintSupported()
    {
        self::assertFalse((new PhpVersion(PhpVersion::VERSION_PHP70))->isParameterTypeHintSupported());
        self::assertFalse(
            (new \DoclerLabs\ApiClientGenerator\Ast\PhpVersion(
                PhpVersion::VERSION_PHP71
            ))->isParameterTypeHintSupported()
        );
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP74))->isParameterTypeHintSupported());
    }

    public function testIsVoidTypeHintSupported()
    {
        self::assertFalse((new PhpVersion(PhpVersion::VERSION_PHP70))->isVoidTypeHintSupported());
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP71))->isVoidTypeHintSupported());
        self::assertTrue((new PhpVersion(PhpVersion::VERSION_PHP74))->isVoidTypeHintSupported());
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
            (new \DoclerLabs\ApiClientGenerator\Ast\PhpVersion(
                PhpVersion::VERSION_PHP70
            ))->isClassConstantVisibilitySupported()
        );
        self::assertTrue(
            (new PhpVersion(PhpVersion::VERSION_PHP71))->isClassConstantVisibilitySupported()
        );
        self::assertTrue(
            (new PhpVersion(
                \DoclerLabs\ApiClientGenerator\Ast\PhpVersion::VERSION_PHP74
            ))->isClassConstantVisibilitySupported()
        );
    }
}