<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;

interface GeneratorInterface
{
    public function generate(Specification $specification, PhpFileCollection $fileRegistry): void;
}
