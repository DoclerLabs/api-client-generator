<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Meta;

use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Meta\ComposerJsonTemplate;
use DoclerLabs\ApiClientGenerator\Meta\ReadmeMdTemplate;
use DoclerLabs\ApiClientGenerator\Meta\TemplateInterface;
use Pimple\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @coversDefaultClass SchemaGenerator
 */
class ReadmeMdTemplateTest extends AbstractTemplateTest
{
    public function exampleProvider(): array
    {
        return [
            'Basic README.md'                => [
                '/ReadmeMd/petstore.yaml',
                '/ReadmeMd/README.md',
                'README.md',
            ],
        ];
    }

    protected function sutTemplate(Container $container): TemplateInterface
    {
        return new ReadmeMdTemplate(
            new Environment(
                new FilesystemLoader(['template'], getcwd() . DIRECTORY_SEPARATOR)
            ),
        );
    }
}
