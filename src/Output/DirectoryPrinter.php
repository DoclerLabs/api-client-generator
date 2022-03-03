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

    public function move(string $destinationPath, string $sourcePath): void
    {
        $this->ensureDirectoryExists($sourcePath);
        $this->delete($destinationPath);

        rename($sourcePath, $destinationPath);
    }

    public function delete(string $path): bool
    {
        if (!file_exists($path)) {
            return true;
        }

        if (!is_dir($path)) {
            return unlink($path);
        }

        $directoryContent = scandir($path);
        if ($directoryContent === false) {
            return true;
        }

        foreach ($directoryContent as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!$this->delete($path . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($path);
    }
}
