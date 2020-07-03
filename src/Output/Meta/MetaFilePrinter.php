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
        foreach ($files as $template) {
            $path = sprintf('%s/%s', $files->getBaseDirectory(), $template->getFilePath());

            $this->printer->print($path, $template->getContent());
        }
    }
}
