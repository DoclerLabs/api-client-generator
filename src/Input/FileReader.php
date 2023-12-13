<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use Symfony\Component\Yaml\Yaml;

class FileReader
{
    public function read(string $fileName): array
    {
        if (!is_readable($fileName)) {
            throw new InvalidSpecificationException('Specification file does not exist or not readable: ' . $fileName);
        }

        $ext      = pathinfo($fileName, PATHINFO_EXTENSION);
        $contents = file_get_contents($fileName);
        if ($contents === false) {
            throw new InvalidSpecificationException('Specification file is empty.');
        }

        return match ($ext) {
            'yaml', 'yml' => Yaml::parse($contents),
            'json'  => json_decode($contents, true, 512, JSON_THROW_ON_ERROR),
            default => throw new InvalidSpecificationException(sprintf('Unknown specification file extension: %s. Supported: yaml, yml, json', $ext)),
        };
    }
}
