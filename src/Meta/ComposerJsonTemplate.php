<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use Twig\Environment;

class ComposerJsonTemplate implements TemplateInterface
{
    private Environment   $renderer;
    private Configuration $configuration;

    public function __construct(
        Environment $renderer,
        Configuration $configuration
    ) {
        $this->renderer      = $renderer;
        $this->configuration = $configuration;
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
                'specification' => $specification,
                'packageName'   => $this->configuration->getPackageName(),
                'phpVersion'    => $this->configuration->getPhpVersion(),
                'namespace'     => $this->configuration->getNamespace(),
            ]
        );

        $fileRegistry->add(new MetaFile($this->getOutputFilePath(), $content));
    }
}
