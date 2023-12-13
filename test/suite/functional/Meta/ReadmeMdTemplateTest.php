<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Meta;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Meta\ReadmeMdTemplate;
use DoclerLabs\ApiClientGenerator\Meta\Template\TwigExtension;
use DoclerLabs\ApiClientGenerator\Meta\TemplateInterface;
use DoclerLabs\ApiClientGenerator\Test\Functional\ConfigurationBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator
 */
class ReadmeMdTemplateTest extends AbstractTemplateTest
{
    public function exampleProvider(): array
    {
        return [
            'Basic README.md + PHP 7.4' => [
                '/ReadmeMd/petstore.yaml',
                '/ReadmeMd/README74.md',
                'README.md',
                ConfigurationBuilder::fake()->build()
            ],
            'Basic README.md + PHP 8.0' => [
                '/ReadmeMd/petstore.yaml',
                '/ReadmeMd/README80.md',
                'README.md',
                ConfigurationBuilder::fake()->withPhpVersion(PhpVersion::VERSION_PHP80)->build(),
            ],
        ];
    }

    protected function sutTemplate(Configuration $configuration): TemplateInterface
    {
        $twig = new Environment(
            new FilesystemLoader(['template'], getcwd() . DIRECTORY_SEPARATOR)
        );

        $twig->addExtension(new TwigExtension());

        return new ReadmeMdTemplate($twig, $configuration);
    }
}
