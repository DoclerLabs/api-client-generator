<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Meta;

use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Meta\ComposerJsonTemplate;
use DoclerLabs\ApiClientGenerator\Meta\TemplateInterface;
use Pimple\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @coversDefaultClass SchemaGenerator
 */
class ComposerJsonTemplateTest extends AbstractTemplateTest
{
    public function exampleProvider(): array
    {
        return [
            'Basic composer.json'                => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer.json',
                'composer.json',
            ],
        ];
    }

    protected function sutTemplate(Container $container): TemplateInterface
    {
        return new ComposerJsonTemplate(
            new Environment(
                new FilesystemLoader([dirname('template/composer.json')])
            ),
            'composer.json.twig',
            'test/petstore-api',
            'Test\\PerstoreApi',
            '7.0',
        );
    }
}
