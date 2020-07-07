<?php

namespace DoclerLabs\ApiClientGenerator\Output\Meta;

use DoclerLabs\ApiClientGenerator\Output\Printer;

class MetaFilePrinter
{
    private Printer $printer;

    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }

    public function createFiles(MetaFileCollection $files): void
    {
        foreach ($files as $file) {
            /** @var MetaFile $file */
            $path = sprintf('%s/%s', $files->getBaseDirectory(), $file->getFilePath());

            $this->printer->print($path, $file->getContent());
        }
    }
}
