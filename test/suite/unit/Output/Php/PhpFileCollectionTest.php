<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Php;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass PhpFileCollection
 */
class PhpFileCollectionTest extends TestCase
{
    private const BASE_DIRECTORY = '/test';
    private const BASE_NAMESPACE = 'Test\\';
    /** @var PhpFileCollection */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new PhpFileCollection(self::BASE_DIRECTORY, self::BASE_NAMESPACE);
    }

    public function testGetBaseNamespace(): void
    {
        $this->assertEquals(self::BASE_NAMESPACE, $this->sut->getBaseNamespace());
    }

    public function testGetBaseDirectory(): void
    {
        $this->assertEquals(self::BASE_DIRECTORY . '/src', $this->sut->getBaseDirectory());
    }

    public function testAddAndGet(): void
    {
        $this->sut->add(new PhpFile('some-file.php', 'SomeClass', []));
        $this->assertNotNull($this->sut->get('SomeClass'));
    }

    public function testGetNonExisting(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->assertNotNull($this->sut->get('NonExistingClass'));
    }

    public function testGetIterator(): void
    {
        $this->sut->add(new PhpFile('some-file.php', 'SomeClass', []));
        $this->sut->add(new PhpFile('another-file.php', 'AnotherClass', []));
        foreach ($this->sut as $file) {
            $this->assertNotNull($file);
        }
    }
}