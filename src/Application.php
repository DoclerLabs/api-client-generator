<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator;

use DoclerLabs\ApiClientGenerator\Command\GenerateCommand;
use DoclerLabs\ApiClientGenerator\Output\WarningFormatter;
use Pimple\Container;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    protected const NAME    = 'OpenApi client generator';
    protected const VERSION = '1.0.0';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $container = new Container();
        $container->register(new ServiceProvider());

        set_error_handler($container[WarningFormatter::class], E_USER_WARNING);

        $this->add($container[GenerateCommand::class]);
    }
}
