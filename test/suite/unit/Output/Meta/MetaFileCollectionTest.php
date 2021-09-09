<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Meta;

use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection
 */
class MetaFileCollectionTest extends TestCase
{
    /** @var MetaFileCollection */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new MetaFileCollection();
    }

    public function testAddAndGet(): void
    {
        $this->sut->add(new MetaFile('some-file.php', 'content'));
        self::assertNotNull($this->sut->get('some-file.php'));
    }

    public function testGetNonExisting(): void
    {
        $this->expectException(InvalidArgumentException::class);
        self::assertNotNull($this->sut->get('non-existing.file'));
    }

    public function testGetIterator(): void
    {
        $this->sut->add(new MetaFile('some-file.php', 'content'));
        $this->sut->add(new MetaFile('another-file.php', 'content'));
        foreach ($this->sut as $file) {
            self::assertNotNull($file);
        }
    }
}
