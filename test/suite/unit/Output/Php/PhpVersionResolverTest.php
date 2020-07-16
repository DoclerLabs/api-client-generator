<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Php;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpVersionResolver;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @coversDefaultClass PhpVersionResolver
 */
class PhpVersionResolverTest extends TestCase
{
    public function testInvalidVersion()
    {
        $this->expectException(UnexpectedValueException::class);
        new PhpVersionResolver('8.0');
    }

    public function testIsParameterTypeHintSupported()
    {
        self::assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isParameterTypeHintSupported());
        self::assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isParameterTypeHintSupported());
        self::assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isParameterTypeHintSupported());
    }

    public function testIsVoidTypeHintSupported()
    {
        self::assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isVoidTypeHintSupported());
        self::assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isVoidTypeHintSupported());
        self::assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isVoidTypeHintSupported());
    }

    public function testIsNullableTypeHintSupported()
    {
        self::assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isNullableTypeHintSupported());
        self::assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isNullableTypeHintSupported());
        self::assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isNullableTypeHintSupported());
    }

    public function testIsClassConstantVisibilitySupported()
    {
        self::assertFalse(
            (new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isClassConstantVisibilitySupported()
        );
        self::assertTrue(
            (new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isClassConstantVisibilitySupported()
        );
        self::assertTrue(
            (new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isClassConstantVisibilitySupported()
        );
    }
}