<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Meta;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
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
            'Default composer.json + PHP 7.4' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_default74.json',
                'composer.json',
                ConfigurationBuilder::fake()->build(),
            ],
            'Default composer.json + PHP 8.0' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_default80.json',
                'composer.json',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Default composer.json + PHP 8.1' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_default81.json',
                'composer.json',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP81)->build(),
            ],
            'composer.json with intl + PHP 7.4' => [
                '/ComposerJson/petstore_with_intl_requirement.yaml',
                '/ComposerJson/composer_with_intl_requirement74.json',
                'composer.json',
                ConfigurationBuilder::fake()->build(),
            ],
            'composer.json with intl + PHP 8.0' => [
                '/ComposerJson/petstore_with_intl_requirement.yaml',
                '/ComposerJson/composer_with_intl_requirement80.json',
                'composer.json',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
            'Guzzle message composer.json + PHP 7.4' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_guzzle_message74.json',
                'composer.json',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE)
                    ->build(),
            ],
            'Guzzle message composer.json + PHP 8.0' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_guzzle_message80.json',
                'composer.json',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE)
                    ->withPhpVersion(PhpVersion::VERSION_PHP80)
                    ->build(),
            ],
            'Nyholm message composer.json + PHP 7.4' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_nyholm_message74.json',
                'composer.json',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->build(),
            ],
            'Nyholm message composer.json + PHP 8.0' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer_nyholm_message80.json',
                'composer.json',
                ConfigurationBuilder::fake()
                    ->withHttpMessage(HttpMessageImplementationStrategy::HTTP_MESSAGE_NYHOLM)
                    ->withPhpVersion(PhpVersion::VERSION_PHP80)
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
            new HttpMessageImplementationStrategy($configuration->httpMessage, $codeBuilder),
            new ContainerImplementationStrategy(
                $configuration->container,
                $configuration->baseNamespace,
                $codeBuilder
            )
        );
    }
}
