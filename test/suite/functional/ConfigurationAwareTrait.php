<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional;

use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use Pimple\Container;

trait ConfigurationAwareTrait
{
    protected function getContainerWith(Configuration $configuration): Container
    {
        $container = new Container();
        $container->register(new ServiceProvider());
        set_error_handler(
            static function (): bool {
                return true;
            },
            E_USER_WARNING
        );
        $container[Configuration::class] = static fn (): Configuration => $configuration;

        return $container;
    }
}
