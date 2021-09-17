<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

use PhpCsFixer\Console\Command\FixCommand;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PhpCodeStyleFixer
{
    private const CS_FIXER_ERROR_PREFIX = 'Files that were not fixed';
    private FixCommand $command;
    private string     $codeStyleConfig;

    public function __construct(FixCommand $command, string $codeStyleConfig)
    {
        $this->command         = $command;
        $this->codeStyleConfig = $codeStyleConfig;
    }

    public function fix(string $file): void
    {
        $output     = new BufferedOutput();
        $returnCode = $this->command
            ->run(
                new ArrayInput(
                    [
                        'path'     => [$file],
                        '--config' => $this->codeStyleConfig,
                    ],
                    $this->command->getDefinition()
                ),
                $output
            );

        $bufferedOutput       = $output->fetch();
        $errorMessagePosition = strpos($bufferedOutput, self::CS_FIXER_ERROR_PREFIX);
        if ($errorMessagePosition !== false) {
            throw new RuntimeException(substr($bufferedOutput, $errorMessagePosition));
        }

        if ($returnCode !== 0) {
            throw new RuntimeException('Code style fixer execution failed.');
        }
    }
}
