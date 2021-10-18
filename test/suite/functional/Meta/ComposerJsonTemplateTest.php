<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Meta;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Meta\ComposerJsonTemplate;
use DoclerLabs\ApiClientGenerator\Meta\Template\TwigExtension;
use DoclerLabs\ApiClientGenerator\Meta\TemplateInterface;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Meta\ComposerJsonTemplate
 */
class ComposerJsonTemplateTest extends AbstractTemplateTest
{
    public function exampleProvider(): array
    {
        return [
            'Default composer.json'         => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_default.json',
                'composer.json',
                ConfigurationBuilder::fake()->build(),
            ],
            'composer.json with intl'         => [
                '/ComposerJson/petstore_with_intl_requirement.yaml',
                '/ComposerJson/composer_with_intl_requirement.json',
                'composer.json',
                ConfigurationBuilder::fake()->build(),
            ],
            'Guzzle message composer.json'  => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_guzzle_message.json',
                'composer.json',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE)
                    ->build(),
            ],
            'Nyholm message composer.json'  => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_nyholm_message.json',
                'composer.json',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->build(),
            ],
        ];
    }

    protected function sutTemplate(Configuration $configuration): TemplateInterface
    {
        $twig = new Environment(new FilesystemLoader(['template'], getcwd() . DIRECTORY_SEPARATOR));

        $twig->addExtension(new TwigExtension());

        /** @var CodeBuilder|MockObject */
        $codeBuilder = $this->createMock(CodeBuilder::class);

        return new ComposerJsonTemplate(
            $twig,
            $configuration,
            new HttpMessageImplementationStrategy($configuration->getHttpMessage(), $codeBuilder),
            new ContainerImplementationStrategy(
                $configuration->getContainer(),
                $configuration->getBaseNamespace(),
                $codeBuilder
            )
        );
    }
}
