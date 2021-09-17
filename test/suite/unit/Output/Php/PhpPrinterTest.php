<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output\Php;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpCodeStyleFixer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use DoclerLabs\ApiClientGenerator\Output\PhpFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\TextFilePrinter;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Output\PhpFilePrinter
 */
class PhpPrinterTest extends TestCase
{
    /** @var PhpFilePrinter */
    private $sut;
    /** @var PrettyPrinterAbstract|MockObject */
    private $marshaler;
    /** @var TextFilePrinter|MockObject */
    private $printer;
    /** @var PhpCodeStyleFixer|MockObject */
    private $fixer;

    protected function setUp(): void
    {
        $this->marshaler = $this->createMock(PrettyPrinterAbstract::class);
        $this->printer   = $this->createMock(TextFilePrinter::class);
        $this->fixer     = $this->createMock(PhpCodeStyleFixer::class);

        $this->sut = new PhpFilePrinter($this->marshaler, $this->printer, $this->fixer);
    }

    public function testCreateFiles()
    {
        $file = $this->createMock(PhpFile::class);
        $file->expects(self::once())
            ->method('getNodes');

        $this->marshaler->expects(self::once())
            ->method('prettyPrintFile');
        $this->printer->expects(self::once())
            ->method('print');
        $this->fixer->expects(self::once())
            ->method('fix');

        $this->sut->print('nowhere', $file);
    }
}
