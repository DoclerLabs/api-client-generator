<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;

interface TemplateInterface
{
    public function render(Specification $specification, MetaFileCollection $fileRegistry): void;

    public function getOutputFilePath(): string;
}
