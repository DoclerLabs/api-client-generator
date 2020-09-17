<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Meta;

use ArrayIterator;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use DoclerLabs\ApiClientGenerator\Output\MetaFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\TextFilePrinter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass MetaFilePrinter
 */
class MetaFilePrinterTest extends TestCase
{
    /** @var MetaFilePrinter */
    private $sut;
    /** @var TextFilePrinter|MockObject */
    private $printer;

    public function setUp(): void
    {
        $this->printer = $this->createMock(TextFilePrinter::class);

        $this->sut = new MetaFilePrinter($this->printer);
    }

    public function testCreateFiles()
    {
        $fileRegistry = $this->createMock(MetaFileCollection::class);
        $file         = $this->createMock(MetaFile::class);
        $file->expects(self::once())
            ->method('getContent');

        $fileRegistry
            ->expects(self::once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$file]));

        $this->printer->expects(self::once())
            ->method('print');

        $this->sut->print($fileRegistry);
    }
}