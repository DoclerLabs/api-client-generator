<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use Symfony\Component\Yaml\Yaml;

class FileReader
{
    public function read(string $fileName): array
    {
        if (!is_readable($fileName)) {
            throw new InvalidSpecificationException('File does not exist or not readable: ' . $fileName);
        }

        $ext      = pathinfo($fileName, PATHINFO_EXTENSION);
        $contents = file_get_contents($fileName);
        switch ($ext) {
            case 'yaml':
            case 'yml':
                return Yaml::parse($contents);
                break;
            case 'json':
                return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
                break;
            default:
                throw new InvalidSpecificationException(
                    sprintf('Unknown specification file extension: %s. Supported: yaml, yml, json', $ext)
                );
        }
    }
}
