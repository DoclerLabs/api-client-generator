<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Php;

use PhpCsFixer\Console\Command\FixCommand;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PhpCodeStyleFixer
{
    private FixCommand $command;
    private string     $codeStyleConfig;

    public function __construct(FixCommand $command, string $codeStyleConfig)
    {
        $this->command         = $command;
        $this->codeStyleConfig = $codeStyleConfig;
    }

    public function fix(string $file): void
    {
        $returnCode = $this->command
            ->run(
                new ArrayInput(
                    [
                        'path'     => [$file],
                        '--config' => $this->codeStyleConfig,
                    ], $this->command->getDefinition()
                ),
                new NullOutput()
            );

        if ($returnCode !== 0) {
            throw new RuntimeException('Code style fixer execution failed.');
        }
    }
}
