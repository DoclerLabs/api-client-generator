<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator;

use DoclerLabs\ApiClientGenerator\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Command\GenerateCommand;
use DoclerLabs\ApiClientGenerator\Generator\ClientFactoryGenerator;
use DoclerLabs\ApiClientGenerator\Generator\ClientGenerator;
use DoclerLabs\ApiClientGenerator\Generator\RequestGenerator;
use DoclerLabs\ApiClientGenerator\Generator\ResponseMapperGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaCollectionGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\Factory\FieldFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\FieldStructureFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\OperationCollectionFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\OperationFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\RequestFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\ResponseFactory;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\Parser as OpenApiParser;
use DoclerLabs\ApiClientGenerator\Input\PhpNameValidator;
use DoclerLabs\ApiClientGenerator\Meta\ComposerJsonTemplate;
use DoclerLabs\ApiClientGenerator\Meta\ReadmeMdTemplate;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpCodeStyleFixer;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpPrinter;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpVersionResolver;
use DoclerLabs\ApiClientGenerator\Output\Printer;
use DoclerLabs\ApiClientGenerator\Output\WarningFormatter;
use PhpParser\PrettyPrinter\Standard;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple[Configuration::class] =
            static fn() => new Configuration(
                getenv('OPENAPI') ?: '',
                getenv('NAMESPACE') ?: '',
                getenv('OUTPUT_DIR') ?: '',
                getenv('CODE_STYLE') ?: '',
                getenv('PACKAGE') ?: '',
                getenv('PHP_VERSION') ?: PhpVersionResolver::VERSION_PHP70,
                getenv('COMPOSER_JSON_TEMPLATE_DIR') ?: Configuration::DEFAULT_TEMPLATE_DIRECTORY,
                getenv('README_MD_TEMPLATE_DIR') ?: Configuration::DEFAULT_TEMPLATE_DIRECTORY,
            );

        $pimple[GenerateCommand::class] =
            static fn(Container $container) => new GenerateCommand(
                $container[Configuration::class],
                $container[FileReader::class],
                $container[OpenApiParser::class],
                $container[CodeGeneratorFacade::class],
                $container[PhpPrinter::class],
                $container[MetaTemplateFacade::class],
                new MetaFilePrinter(new Printer())
            );

        $pimple[CodeGeneratorFacade::class] =
            static fn(Container $container) => (new CodeGeneratorFacade())
                ->add($container[SchemaCollectionGenerator::class])
                ->add($container[SchemaGenerator::class])
                ->add($container[ResponseMapperGenerator::class])
                ->add($container[ClientFactoryGenerator::class])
                ->add($container[RequestGenerator::class])
                ->add($container[ClientGenerator::class]);

        $pimple[MetaTemplateFacade::class] =
            static fn(Container $container) => (new MetaTemplateFacade())
                ->add($container[ComposerJsonTemplate::class])
                ->add($container[ReadmeMdTemplate::class]);

        $pimple[FileReader::class] =
            static fn(Container $container) => new FileReader();

        $pimple[OpenApiParser::class] =
            static fn(Container $container) => new OpenApiParser($container[OperationCollectionFactory::class]);

        $pimple[ClientGenerator::class] =
            static fn(Container $container) => new ClientGenerator($container[CodeBuilder::class]);

        $pimple[CodeBuilder::class] =
            static fn(Container $container) => new CodeBuilder($container[PhpVersionResolver::class]);

        $pimple[PhpVersionResolver::class] =
            static fn(Container $container) => new PhpVersionResolver(
                $container[Configuration::class]->getPhpVersion()
            );

        $pimple[RequestGenerator::class] =
            static fn(Container $container) => new RequestGenerator($container[CodeBuilder::class]);

        $pimple[ResponseMapperGenerator::class] =
            static fn(Container $container) => new ResponseMapperGenerator($container[CodeBuilder::class]);

        $pimple[SchemaCollectionGenerator::class] =
            static fn(Container $container) => new SchemaCollectionGenerator($container[CodeBuilder::class]);

        $pimple[SchemaGenerator::class] =
            static fn(Container $container) => new SchemaGenerator($container[CodeBuilder::class]);

        $pimple[PhpPrinter::class] =
            static fn(Container $container) => new PhpPrinter(
                new Standard(),
                new Printer(),
                $container[PhpCodeStyleFixer::class]
            );

        $pimple[PhpCodeStyleFixer::class] =
            static fn(Container $container) => new PhpCodeStyleFixer(
                $container[Configuration::class]->getCodeStyleConfig()
            );

        $pimple[OperationCollectionFactory::class] =
            static fn(Container $container) => new OperationCollectionFactory($container[OperationFactory::class]);

        $pimple[OperationFactory::class] =
            static fn(Container $container) => new OperationFactory(
                $container[RequestFactory::class],
                $container[ResponseFactory::class]
            );

        $pimple[RequestFactory::class] =
            static fn(Container $container) => new RequestFactory($container[FieldFactory::class]);

        $pimple[ResponseFactory::class] =
            static fn(Container $container) => new ResponseFactory($container[FieldFactory::class]);

        $pimple[FieldFactory::class] =
            static fn() => new FieldFactory(new FieldStructureFactory(), new PhpNameValidator());

        $pimple[ClientFactoryGenerator::class] =
            static fn(Container $container) => new ClientFactoryGenerator($container[CodeBuilder::class]);

        $pimple[Environment::class] =
            static fn(Container $container) => new Environment(
                new FilesystemLoader(
                    [
                        $container[Configuration::class]->getComposerJsonTemplateDir(),
                        $container[Configuration::class]->getReadmeMdTemplateDir(),
                    ], '/'
                )
            );

        $pimple[ComposerJsonTemplate::class] =
            static fn(Container $container) => new ComposerJsonTemplate(
                $container[Environment::class],
                $container[Configuration::class]->getPackageName(),
                $container[Configuration::class]->getNamespace(),
                $container[Configuration::class]->getPhpVersion(),
            );

        $pimple[ReadmeMdTemplate::class] =
            static fn(Container $container) => new ReadmeMdTemplate(
                $container[Environment::class]
            );

        $pimple[WarningFormatter::class] =
            static fn() => new WarningFormatter(new SymfonyStyle(new ArgvInput(), new ConsoleOutput()));
    }
}
