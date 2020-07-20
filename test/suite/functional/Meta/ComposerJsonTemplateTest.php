<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Meta;

use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Meta\ComposerJsonTemplate;
use DoclerLabs\ApiClientGenerator\Meta\ReadmeMdTemplate;
use DoclerLabs\ApiClientGenerator\Meta\Template\TwigExtension;
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
            'Basic composer.json' => [
                '/ComposerJson/petstore.yaml',
                '/ComposerJson/composer.json',
                'composer.json',
            ],
        ];
    }

    protected function sutTemplate(Container $container): TemplateInterface
    {
        $configuration = $this->createMock(Configuration::class);

        $configuration
            ->expects(self::once())
            ->method('getNamespace')
            ->willReturn('Test\\PerstoreApi');

        $configuration
            ->expects(self::once())
            ->method('getPackageName')
            ->willReturn('test/petstore-api');

        $configuration
            ->expects(self::once())
            ->method('getPhpVersion')
            ->willReturn('7.2');

        $twig = new Environment(
            new FilesystemLoader(['template'], getcwd() . DIRECTORY_SEPARATOR)
        );

        $twig->addExtension(new TwigExtension());

        return new ComposerJsonTemplate($twig, $configuration);
    }
}
