<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Meta;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFile;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use Twig\Environment;

class ReadmeMdTemplate implements TemplateInterface
{
    private const NO_TAG_PLACEHOLDER = '[No tag]';
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
        return 'README.md';
    }

    public function render(Specification $specification, MetaFileCollection $fileRegistry): void
    {
        $content = $this->renderer->render(
            'README.md.twig',
            [
                'specification'           => $specification,
                'packageName'             => $this->configuration->getPackageName(),
                'phpVersion'              => $this->configuration->getPhpVersion(),
                'generatorVersion'        => $this->configuration->getGeneratorVersion(),
                'namespace'               => $this->configuration->getBaseNamespace(),
                'exampleOperation'        => $this->pickExampleOperation($specification),
                'operationsGroupedByTags' => $this->groupOperationsByTags($specification),
            ]
        );

        $fileRegistry->add(new MetaFile($this->getOutputFilePath(), $content));
    }

    private function pickExampleOperation(Specification $specification): Operation
    {
        return $specification->getOperations()->toArray()[0];
    }

    private function groupOperationsByTags(Specification $specification): array
    {
        $operationsGroupedByTags = [];
        foreach ($specification->getOperations() as $operation) {
            foreach ($operation->getTags() as $tag) {
                $operationsGroupedByTags[$tag][] = $operation;
            }
            if (empty($operation->getTags())) {
                $operationsGroupedByTags[self::NO_TAG_PLACEHOLDER][] = $operation;
            }
        }

        return $operationsGroupedByTags;
    }
}
