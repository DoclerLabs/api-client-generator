<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Php;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\Php\PhpFile
 */
class PhpFileTest extends TestCase
{
    private const FILE_NAME  = 'test.php';
    private const CLASS_NAME = 'Test\\Test';
    /** @var PhpFile */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new PhpFile(self::FILE_NAME, self::CLASS_NAME, []);
    }

    public function testGetFileName(): void
    {
        self::assertEquals(self::FILE_NAME, $this->sut->getFileName());
    }

    public function testGetFullyQualifiedClassName(): void
    {
        self::assertEquals(self::CLASS_NAME, $this->sut->getFullyQualifiedClassName());
    }

    public function testGetNodes(): void
    {
        self::assertEquals([], $this->sut->getNodes());
    }
}
