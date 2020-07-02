<?php

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use Twig\Environment;

class ReadmeMdTemplate implements TemplateInterface
{
    private Environment $renderer;
    private string      $templatePath;

    public function __construct(Environment $renderer, string $templatePath)
    {
        $this->renderer     = $renderer;
        $this->templatePath = $templatePath;
    }

    public function getOutputFilePath(): string
    {
        return 'README.md';
    }

    public function render(Specification $specification, MetaFileCollection $fileRegistry): void
    {
        $content = $this->renderer->render($this->templatePath, ['apiClientName' => $specification->getTitle()]);

        $fileRegistry->add(new MetaFile($this->getOutputFilePath(), $content));
    }
}
