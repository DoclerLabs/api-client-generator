<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use RuntimeException;

class DirectoryPrinter
{
    public function ensureDirectoryExists(string $directoryPath): void
    {
        $isSuccessful = is_dir($directoryPath)
                        || mkdir($directoryPath, 0755, true)
                        || is_dir($directoryPath);

        if (!$isSuccessful) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created', $directoryPath));
        }
    }
}
