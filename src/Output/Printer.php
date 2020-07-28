<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use RuntimeException;

class Printer
{
    public function print(string $path, string $data): void
    {
        $baseDir = dirname($path);
        if (!$this->isDirectoryExist($baseDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $baseDir));
        }

        file_put_contents($path, $data, FILE_TEXT);
    }

    private function isDirectoryExist(string $path): bool
    {
        return is_dir($path)
               || mkdir($path, 0755, true)
               || is_dir($path);
    }
}
