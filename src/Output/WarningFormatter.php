<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

class WarningFormatter
{
    public function __construct(private SymfonyStyle $output)
    {
    }

    public function __invoke(int $code, string $message): bool
    {
        $this->output->warning($message);

        return true;
    }
}
