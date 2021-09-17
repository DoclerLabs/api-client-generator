<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Naming;

use DoclerLabs\ApiClientGenerator\Input\Configuration;

class CopiedNamespace
{
    public static function getImport(string $baseNamespace, string $staticClassFqdn): string
    {
        return str_replace(Configuration::STATIC_PHP_FILE_BASE_NAMESPACE, $baseNamespace, $staticClassFqdn);
    }
}
