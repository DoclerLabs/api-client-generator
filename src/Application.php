<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator;

use DoclerLabs\ApiClientGenerator\Command\GenerateCommand;
use Pimple\Container;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

class Application extends BaseApplication
{
    protected const NAME = 'OpenApi client generator';

    protected const VERSION = '1.0.0';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $container = new Container();
        $container->register(new ServiceProvider());

        /** @var Command $command */
        $command = $container[GenerateCommand::class];

        $this->add($command);
    }
}
