<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Naming;

use DoclerLabs\ApiClientGenerator\Input\Specification;

class ClientNaming
{
    private const CLIENT_SUFFIX  = 'Client';
    private const FACTORY_SUFFIX = 'ClientFactory';

    public static function getClassName(Specification $specification): string
    {
        return sprintf('%s%s', CaseCaster::toPascal($specification->getTitle()), static::CLIENT_SUFFIX);
    }

    public static function getFactoryClassName(Specification $specification): string
    {
        return sprintf('%s%s', CaseCaster::toPascal($specification->getTitle()), static::FACTORY_SUFFIX);
    }
}
