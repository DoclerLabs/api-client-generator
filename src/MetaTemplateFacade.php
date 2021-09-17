<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator;

use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Meta\TemplateInterface;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;

class MetaTemplateFacade
{
    private array $templates;

    public function add(TemplateInterface $template): self
    {
        $this->templates[] = $template;

        return $this;
    }

    public function render(Specification $specification, MetaFileCollection $fileRegistry): void
    {
        foreach ($this->templates as $template) {
            $template->render($specification, $fileRegistry);
        }
    }
}
