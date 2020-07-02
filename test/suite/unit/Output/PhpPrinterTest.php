<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Output;

use ArrayIterator;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpCodeStyleFixer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpPrinter;
use DoclerLabs\ApiClientGenerator\Output\Printer;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass PhpPrinter
 */
class PhpPrinterTest extends TestCase
{
    /** @var PhpPrinter */
    private $sut;

    /** @var PrettyPrinterAbstract|MockObject */
    private $marshaler;

    /** @var Printer|MockObject */
    private $printer;

    /** @var PhpCodeStyleFixer|MockObject */
    private $fixer;

    public function setUp(): void
    {
        $this->marshaler = $this->createMock(PrettyPrinterAbstract::class);
        $this->printer   = $this->createMock(Printer::class);
        $this->fixer     = $this->createMock(PhpCodeStyleFixer::class);

        $this->sut = new PhpPrinter($this->marshaler, $this->printer, $this->fixer);
    }

    public function testCreateFiles()
    {
        $fileRegistry = $this->createMock(PhpFileCollection::class);
        $file         = $this->createMock(PhpFile::class);
        $file->expects($this->once())
            ->method('getNodes');

        $fileRegistry
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([$file]));

        $this->marshaler->expects($this->once())
            ->method('prettyPrintFile');
        $this->printer->expects($this->once())
            ->method('print');
        $this->fixer->expects($this->once())
            ->method('fix');

        $this->sut->createFiles($fileRegistry);
    }
}