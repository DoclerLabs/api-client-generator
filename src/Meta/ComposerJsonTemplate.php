<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClientImplementation;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementation;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use Twig\Environment;

class ComposerJsonTemplate implements TemplateInterface
{
    private Environment               $renderer;
    private Configuration             $configuration;
    private HttpClientImplementation  $clientImplementation;
    private HttpMessageImplementation $messageImplementation;

    public function __construct(
        Environment $renderer,
        Configuration $configuration,
        HttpClientImplementation $clientImplementation,
        HttpMessageImplementation $messageImplementation
    ) {
        $this->renderer              = $renderer;
        $this->configuration         = $configuration;
        $this->clientImplementation  = $clientImplementation;
        $this->messageImplementation = $messageImplementation;
    }

    public function getOutputFilePath(): string
    {
        return 'composer.json';
    }

    public function render(Specification $specification, MetaFileCollection $fileRegistry): void
    {
        $packages = array_merge(
            $this->getCommonPackages(),
            $this->clientImplementation->getPackages(),
            $this->messageImplementation->getPackages(),
        );
        ksort($packages);

        $content = $this->renderer->render(
            'composer.json.twig',
            [
                'specification' => $specification,
                'packageName'   => $this->configuration->getPackageName(),
                'phpVersion'    => $this->configuration->getPhpVersion(),
                'namespace'     => $this->configuration->getBaseNamespace(),
                'packages'      => $packages,
            ]
        );

        $fileRegistry->add(new MetaFile($this->getOutputFilePath(), $content));
    }

    private function getCommonPackages(): array
    {
        return [
            'docler-labs/api-client-exception' => '^1.0',
            'psr/http-client-implementation'   => '^1.0',
            'psr/http-client'                  => '^1.0',
            'psr/http-factory'                 => '^1.0',
        ];
    }
}
