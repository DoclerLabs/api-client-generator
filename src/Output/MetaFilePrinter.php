<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;

class MetaFilePrinter
{
    public function __construct(private TextFilePrinter $textPrinter)
    {
    }

    public function print(string $destinationPath, MetaFile $file): void
    {
        $this->textPrinter->print($destinationPath, $file->content);
    }
}
