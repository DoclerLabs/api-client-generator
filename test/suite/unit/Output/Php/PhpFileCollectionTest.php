<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Php;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection
 */
class PhpFileCollectionTest extends TestCase
{
    /** @var PhpFileCollection */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new PhpFileCollection();
    }

    public function testAddAndGet(): void
    {
        $this->sut->add(new PhpFile('some-file.php', 'SomeClass', []));
        self::assertNotNull($this->sut->get('SomeClass'));
    }

    public function testGetNonExisting(): void
    {
        $this->expectException(InvalidArgumentException::class);
        self::assertNotNull($this->sut->get('NonExistingClass'));
    }

    public function testGetIterator(): void
    {
        $this->sut->add(new PhpFile('some-file.php', 'SomeClass', []));
        $this->sut->add(new PhpFile('another-file.php', 'AnotherClass', []));
        foreach ($this->sut as $file) {
            self::assertNotNull($file);
        }
    }
}
