<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

class TextFilePrinter
{
    public function __construct(private DirectoryPrinter $directoryPrinter)
    {
    }

    public function print(string $destinationPath, string $data): void
    {
        $this->directoryPrinter->ensureDirectoryExists(dirname($destinationPath));

        file_put_contents($destinationPath, $data);
    }
}
