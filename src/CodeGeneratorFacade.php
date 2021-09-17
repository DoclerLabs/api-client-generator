<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator;

use DoclerLabs\ApiClientGenerator\Generator\GeneratorInterface;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;

class CodeGeneratorFacade implements GeneratorInterface
{
    private array $generators;

    public function add(GeneratorInterface $generator): self
    {
        $this->generators[] = $generator;

        return $this;
    }

    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void
    {
        foreach ($this->generators as $generator) {
            $generator->generate($specification, $fileRegistry);
        }
    }
}
