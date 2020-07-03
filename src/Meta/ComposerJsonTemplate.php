<?php

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use Twig\Environment;

class ComposerJsonTemplate implements TemplateInterface
{
    private Environment $renderer;
    private string      $packageName;
    private string      $namespace;
    private string      $phpVersion;

    public function __construct(
        Environment $renderer,
        string $packageName,
        string $namespace,
        string $phpVersion = '7.0'
    ) {
        $this->renderer    = $renderer;
        $this->packageName = $packageName;
        $this->namespace   = addslashes($namespace . '\\');
        $this->phpVersion  = $phpVersion;
    }

    public function getOutputFilePath(): string
    {
        return 'composer.json';
    }

    public function render(Specification $specification, MetaFileCollection $fileRegistry): void
    {
        $content = $this->renderer->render(
            'composer.json.twig',
            [
                'packageName' => $this->packageName,
                'description' => $specification->getDescription(),
                'phpVersion'  => $this->phpVersion,
                'namespace'   => $this->namespace,
            ]
        );

        $fileRegistry->add(new MetaFile($this->getOutputFilePath(), $content));
    }
}
