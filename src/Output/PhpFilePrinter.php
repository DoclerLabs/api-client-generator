<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use DoclerLabs\ApiClientGenerator\Output\Php\PhpCodeStyleFixer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFile;
use PhpParser\PrettyPrinterAbstract;

class PhpFilePrinter
{
    public function __construct(
        private PrettyPrinterAbstract $marshaller,
        private TextFilePrinter $textPrinter,
        private PhpCodeStyleFixer $codeStyleFixer
    ) {
    }

    public function print(string $destinationPath, PhpFile $file): void
    {
        $this->textPrinter->print($destinationPath, $this->marshaller->prettyPrintFile($file->nodes));
        $this->codeStyleFixer->fix($destinationPath);
    }
}
