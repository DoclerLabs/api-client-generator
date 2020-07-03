<?php

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use Twig\Environment;

class ReadmeMdTemplate implements TemplateInterface
{
    private Environment $renderer;

    public function __construct(Environment $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getOutputFilePath(): string
    {
        return 'README.md';
    }

    public function render(Specification $specification, MetaFileCollection $fileRegistry): void
    {
        $content = $this->renderer->render('README.md.twig', ['apiClientName' => $specification->getTitle()]);

        $fileRegistry->add(new MetaFile($this->getOutputFilePath(), $content));
    }
}
