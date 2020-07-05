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
        $this->assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isParameterTypeHintSupported());
        $this->assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isParameterTypeHintSupported());
        $this->assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isParameterTypeHintSupported());
    }

    public function testIsVoidTypeHintSupported()
    {
        $this->assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isVoidTypeHintSupported());
        $this->assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isVoidTypeHintSupported());
        $this->assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isVoidTypeHintSupported());
    }

    public function testIsNullableTypeHintSupported()
    {
        $this->assertFalse((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isNullableTypeHintSupported());
        $this->assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isNullableTypeHintSupported());
        $this->assertTrue((new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isNullableTypeHintSupported());
    }

    public function testIsClassConstantVisibilitySupported()
    {
        $this->assertFalse(
            (new PhpVersionResolver(PhpVersionResolver::VERSION_PHP70))->isClassConstantVisibilitySupported()
        );
        $this->assertTrue(
            (new PhpVersionResolver(PhpVersionResolver::VERSION_PHP71))->isClassConstantVisibilitySupported()
        );
        $this->assertTrue(
            (new PhpVersionResolver(PhpVersionResolver::VERSION_PHP74))->isClassConstantVisibilitySupported()
        );
    }
}