<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

class TextFilePrinter
{
    private DirectoryPrinter $directoryPrinter;

    public function __construct(DirectoryPrinter $directoryPrinter)
    {
        $this->directoryPrinter = $directoryPrinter;
    }

    public function print(string $destinationPath, string $data): void
    {
        $this->directoryPrinter->print(dirname($destinationPath));

        file_put_contents($destinationPath, $data, FILE_TEXT);
    }
}
