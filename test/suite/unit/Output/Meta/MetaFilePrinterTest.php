<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Meta;

use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\MetaFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\TextFilePrinter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\MetaFilePrinter
 */
class MetaFilePrinterTest extends TestCase
{
    /** @var MetaFilePrinter */
    private $sut;
    /** @var TextFilePrinter|MockObject */
    private $printer;

    protected function setUp(): void
    {
        $this->printer = $this->createMock(TextFilePrinter::class);

        $this->sut = new MetaFilePrinter($this->printer);
    }

    public function testCreateFiles()
    {
        $file = $this->createMock(MetaFile::class);
        $file->expects(self::once())
            ->method('getContent');

        $this->printer->expects(self::once())
            ->method('print');

        $this->sut->print('nowhere', $file);
    }
}
