<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;

class MetaFilePrinter
{
    private TextFilePrinter $textPrinter;

    public function __construct(TextFilePrinter $textPrinter)
    {
        $this->textPrinter = $textPrinter;
    }

    public function print(string $destinationPath, MetaFile $file): void
    {
        $this->textPrinter->print($destinationPath, $file->getContent());
    }
}
