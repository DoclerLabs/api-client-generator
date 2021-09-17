<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Naming;

use DoclerLabs\ApiClientGenerator\Entity\Operation;

class RequestNaming
{
    private const FILE_SUFFIX = 'Request';

    public static function getClassName(Operation $operation): string
    {
        return sprintf('%s%s', CaseCaster::toPascal($operation->getName()), self::FILE_SUFFIX);
    }
}
