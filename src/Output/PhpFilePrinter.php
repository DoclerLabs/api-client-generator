<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpCodeStyleFixer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use PhpParser\PrettyPrinterAbstract;

class PhpFilePrinter
{
    private PrettyPrinterAbstract $marshaller;
    private TextFilePrinter       $textPrinter;
    private PhpCodeStyleFixer     $codeStyleFixer;

    public function __construct(
        PrettyPrinterAbstract $marshaller,
        TextFilePrinter $textPrinter,
        PhpCodeStyleFixer $codeStyleFixer
    ) {
        $this->marshaller     = $marshaller;
        $this->textPrinter    = $textPrinter;
        $this->codeStyleFixer = $codeStyleFixer;
    }

    public function print(string $destinationPath, PhpFile $file): void
    {
        $this->textPrinter->print($destinationPath, $this->marshaller->prettyPrintFile($file->getNodes()));
        $this->codeStyleFixer->fix($destinationPath);
    }
}
