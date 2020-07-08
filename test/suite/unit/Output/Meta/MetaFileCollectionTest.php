<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Meta;

use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass MetaFileCollection
 */
class MetaFileCollectionTest extends TestCase
{
    private const BASE_DIRECTORY = '/test';
    /** @var MetaFileCollection */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new MetaFileCollection(self::BASE_DIRECTORY);
    }

    public function testGetBaseDirectory(): void
    {
        $this->assertEquals(self::BASE_DIRECTORY, $this->sut->getBaseDirectory());
    }

    public function testAddAndGet(): void
    {
        $this->sut->add(new MetaFile('some-file.php', 'content'));
        $this->assertNotNull($this->sut->get('some-file.php'));
    }

    public function testGetNonExisting(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->assertNotNull($this->sut->get('non-existing.file'));
    }

    public function testGetIterator(): void
    {
        $this->sut->add(new MetaFile('some-file.php', 'content'));
        $this->sut->add(new MetaFile('another-file.php', 'content'));
        foreach ($this->sut as $file) {
            $this->assertNotNull($file);
        }
    }
}