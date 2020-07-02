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

    public function createFiles(MetaFileCollection $templates): void
    {
        foreach ($templates as $template) {
            $path = sprintf('%s/%s', $templates->getBaseDirectory(), $template->getOutputFilename());

            $this->printer->print($path, $template->render());
        }
    }
}
