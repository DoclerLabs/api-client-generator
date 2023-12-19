<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use Twig\Environment;

class ComposerJsonTemplate implements TemplateInterface
{
    public function __construct(
        private Environment $renderer,
        private Configuration $configuration,
        private HttpMessageImplementationStrategy $messageImplementation,
        private ContainerImplementationStrategy $containerImplementation
    ) {
    }

    public function getOutputFilePath(): string
    {
        return 'composer.json';
    }

    public function render(Specification $specification, MetaFileCollection $fileRegistry): void
    {
        $packages = array_merge(
            $this->getCommonPackages(),
            $this->getPackagesForSpecification($specification),
            $this->messageImplementation->getPackages(),
            $this->containerImplementation->getPackages()
        );
        ksort($packages);

        $content = $this->renderer->render(
            'composer.json.twig',
            [
                'specification' => $specification,
                'packageName'   => $this->configuration->packageName,
                'phpVersion'    => sprintf('%.1f', $this->configuration->phpVersion),
                'namespace'     => $this->configuration->baseNamespace,
                'packages'      => $packages,
            ]
        );

        $fileRegistry->add(new MetaFile($this->getOutputFilePath(), $content));
    }

    private function getCommonPackages(): array
    {
        return [
            'docler-labs/api-client-exception' => '^1.0 || ^2.0',
            'psr/container'                    => '^1.0 || ^2.0',
            'psr/http-client'                  => '^1.0',
        ];
    }

    private function getPackagesForSpecification(Specification $specification): array
    {
        $packages = [];

        if ($specification->requiresIntlExtension()) {
            $packages['ext-intl'] = '*';
        }

        if (in_array(JsonContentTypeSerializer::MIME_TYPE, $specification->getAllContentTypes(), true)) {
            $packages['ext-json'] = '*';
        }

        if (in_array(XmlContentTypeSerializer::MIME_TYPE, $specification->getAllContentTypes(), true)) {
            $packages['ext-dom'] = '*';
        }

        return $packages;
    }
}
