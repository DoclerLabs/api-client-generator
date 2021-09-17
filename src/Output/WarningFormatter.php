<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

class WarningFormatter
{
    private SymfonyStyle $output;

    public function __construct(SymfonyStyle $output)
    {
        $this->output = $output;
    }

    public function __invoke(int $code, string $message): bool
    {
        $this->output->warning($message);

        return true;
    }
}
