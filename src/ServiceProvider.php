<?php
declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator;

use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Ast\Visitor\NamespaceSubstituteVisitor;
use DoclerLabs\ApiClientGenerator\Command\GenerateCommand;
use DoclerLabs\ApiClientGenerator\Generator\ClientFactoryGenerator;
use DoclerLabs\ApiClientGenerator\Generator\ClientGenerator;
use DoclerLabs\ApiClientGenerator\Generator\FreeFormSchemaGenerator;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\RequestGenerator;
use DoclerLabs\ApiClientGenerator\Generator\RequestMapperGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaCollectionGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaGenerator;
use DoclerLabs\ApiClientGenerator\Generator\SchemaMapperGenerator;
use DoclerLabs\ApiClientGenerator\Generator\Security\BasicAuthenticationSecurityStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Security\BearerAuthenticationSecurityStrategy;
use DoclerLabs\ApiClientGenerator\Generator\ServiceProviderGenerator;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\Factory\FieldFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\OperationCollectionFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\OperationFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\RequestFactory;
use DoclerLabs\ApiClientGenerator\Input\Factory\ResponseFactory;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\Parser as OpenApiParser;
use DoclerLabs\ApiClientGenerator\Input\PhpNameValidator;
use DoclerLabs\ApiClientGenerator\Meta\ComposerJsonTemplate;
use DoclerLabs\ApiClientGenerator\Meta\ReadmeMdTemplate;
use DoclerLabs\ApiClientGenerator\Meta\Template\TwigExtension;
use DoclerLabs\ApiClientGenerator\Output\DirectoryPrinter;
use DoclerLabs\ApiClientGenerator\Output\MetaFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpCodeStyleFixer;
use DoclerLabs\ApiClientGenerator\Output\PhpFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\StaticPhpFileCopier;
use DoclerLabs\ApiClientGenerator\Output\TextFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\WarningFormatter;
use PhpCsFixer\Console\Command\FixCommand;
use PhpCsFixer\ToolInfo;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\Parser\Php7 as PhpParser;
use PhpParser\PrettyPrinter\Standard;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple[Environment::class] = static function (Container $container) {
            $twig = new Environment(
                new FilesystemLoader(
                    [
                        $container[Configuration::class]->getComposerJsonTemplateDir(),
                        $container[Configuration::class]->getReadmeMdTemplateDir(),
                    ],
                    '/'
                )
            );

            $twig->addExtension($container[TwigExtension::class]);

            return $twig;
        };

        $pimple[Configuration::class] = static fn () => new Configuration(
            getenv('OPENAPI') ?: '',
            getenv('NAMESPACE') ?: '',
            getenv('OUTPUT_DIR') ?: '',
            getenv('SOURCE_DIR') ?: Configuration::DEFAULT_SOURCE_DIRECTORY,
            getenv('CODE_STYLE') ?: Configuration::DEFAULT_CODE_STYLE_CONFIG,
            getenv('PACKAGE') ?: '',
            getenv('CLIENT_PHP_VERSION') ?: Configuration::DEFAULT_PHP_VERSION,
            getenv('API_CLIENT_GENERATOR_VERSION') ?: null,
            getenv('COMPOSER_JSON_TEMPLATE_DIR') ?: Configuration::DEFAULT_TEMPLATE_DIRECTORY,
            getenv('README_MD_TEMPLATE_DIR') ?: Configuration::DEFAULT_TEMPLATE_DIRECTORY,
            getenv('HTTP_MESSAGE') ?: Configuration::DEFAULT_HTTP_MESSAGE,
            getenv('CONTAINER') ?: Configuration::DEFAULT_CONTAINER,
        );

        $pimple[GenerateCommand::class] = static fn (Container $container) => new GenerateCommand(
            $container[Configuration::class],
            $container[FileReader::class],
            $container[OpenApiParser::class],
            $container[CodeGeneratorFacade::class],
            $container[PhpFilePrinter::class],
            $container[MetaTemplateFacade::class],
            $container[MetaFilePrinter::class],
            $container[Finder::class],
            $container[StaticPhpFileCopier::class],
            $container[Filesystem::class],
            $container[WarningFormatter::class]
        );

        $pimple[WarningFormatter::class] = static fn () => new WarningFormatter(
            new SymfonyStyle(
                new ArgvInput(),
                new ConsoleOutput()
            )
        );

        $pimple[Finder::class] = static fn () => new Finder();

        $pimple[Filesystem::class] = static fn () => new Filesystem();

        $pimple[CodeGeneratorFacade::class] = static fn (Container $container) => (new CodeGeneratorFacade())
            ->add($container[ClientFactoryGenerator::class])
            ->add($container[ClientGenerator::class])
            ->add($container[RequestGenerator::class])
            ->add($container[RequestMapperGenerator::class])
            ->add($container[SchemaMapperGenerator::class])
            ->add($container[SchemaCollectionGenerator::class])
            ->add($container[SchemaGenerator::class])
            ->add($container[FreeFormSchemaGenerator::class])
            ->add($container[ServiceProviderGenerator::class]);

        $pimple[MetaTemplateFacade::class] = static fn (Container $container) => (new MetaTemplateFacade())
            ->add($container[ComposerJsonTemplate::class])
            ->add($container[ReadmeMdTemplate::class]);

        $pimple[FileReader::class] = static fn () => new FileReader();

        $pimple[OpenApiParser::class] = static fn (Container $container) => new OpenApiParser(
            $container[OperationCollectionFactory::class]
        );

        $pimple[CodeBuilder::class] = static fn (Container $container) => new CodeBuilder(
            $container[PhpVersion::class]
        );

        $pimple[PhpVersion::class] = static fn (Container $container) => new PhpVersion(
            $container[Configuration::class]->getPhpVersion()
        );

        $pimple[RequestGenerator::class] = static fn (Container $container) => new RequestGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class],
            new BearerAuthenticationSecurityStrategy($container[CodeBuilder::class]),
            new BasicAuthenticationSecurityStrategy($container[CodeBuilder::class])
        );

        $pimple[RequestMapperGenerator::class] = static fn (Container $container) => new RequestMapperGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class],
            $container[HttpMessageImplementationStrategy::class]
        );

        $pimple[SchemaMapperGenerator::class] = static fn (Container $container) => new SchemaMapperGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class]
        );

        $pimple[SchemaCollectionGenerator::class] = static fn (Container $container) => new SchemaCollectionGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class]
        );

        $pimple[ClientGenerator::class] = static fn (Container $container) => new ClientGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class]
        );

        $pimple[SchemaGenerator::class] = static fn (Container $container) => new SchemaGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class]
        );

        $pimple[FreeFormSchemaGenerator::class] = static fn (Container $container) => new FreeFormSchemaGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class]
        );

        $pimple[ClientFactoryGenerator::class] = static fn (Container $container) => new ClientFactoryGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class],
            $container[ContainerImplementationStrategy::class]
        );

        $pimple[ServiceProviderGenerator::class] = static fn (Container $container) => new ServiceProviderGenerator(
            $container[Configuration::class]->getBaseNamespace(),
            $container[CodeBuilder::class],
            $container[ContainerImplementationStrategy::class],
            $container[HttpMessageImplementationStrategy::class],
        );

        $pimple[HttpMessageImplementationStrategy::class] =
            static fn (Container $container) => new HttpMessageImplementationStrategy(
                $container[Configuration::class]->getHttpMessage(),
                $container[CodeBuilder::class]
            );

        $pimple[ContainerImplementationStrategy::class] =
            static fn (Container $container) => new ContainerImplementationStrategy(
                $container[Configuration::class]->getContainer(),
                $container[Configuration::class]->getBaseNamespace(),
                $container[CodeBuilder::class]
            );

        $pimple[PhpFilePrinter::class] = static fn (Container $container) => new PhpFilePrinter(
            new Standard(),
            $container[TextFilePrinter::class],
            $container[PhpCodeStyleFixer::class]
        );

        $pimple[PhpCodeStyleFixer::class] = static fn (Container $container) => new PhpCodeStyleFixer(
            new FixCommand(new ToolInfo()),
            $container[Configuration::class]->getCodeStyleConfig()
        );

        $pimple[OperationCollectionFactory::class] = static fn (Container $container) => new OperationCollectionFactory(
            $container[OperationFactory::class]
        );

        $pimple[OperationFactory::class] = static fn (Container $container) => new OperationFactory(
            $container[RequestFactory::class],
            $container[ResponseFactory::class]
        );

        $pimple[RequestFactory::class] = static fn (Container $container) => new RequestFactory(
            $container[FieldFactory::class]
        );

        $pimple[ResponseFactory::class] = static fn (Container $container) => new ResponseFactory(
            $container[FieldFactory::class]
        );

        $pimple[FieldFactory::class] = static fn () => new FieldFactory(new PhpNameValidator());

        $pimple[TwigExtension::class] = static fn () => new TwigExtension();

        $pimple[ComposerJsonTemplate::class] = static fn (Container $container) => new ComposerJsonTemplate(
            $container[Environment::class],
            $container[Configuration::class],
            $container[HttpMessageImplementationStrategy::class],
            $container[ContainerImplementationStrategy::class]
        );

        $pimple[ReadmeMdTemplate::class] = static fn (Container $container) => new ReadmeMdTemplate(
            $container[Environment::class],
            $container[Configuration::class]
        );

        $pimple[WarningFormatter::class] = static fn () => new WarningFormatter(
            new SymfonyStyle(
                new ArgvInput(),
                new ConsoleOutput()
            )
        );

        $pimple[Emulative::class] = static fn () => new Emulative(
            [
                'usedAttributes' => [
                    'comments',
                    'startLine',
                    'endLine',
                    'startTokenPos',
                    'endTokenPos',
                ],
            ]
        );

        $pimple[PhpParser::class] = static fn (Container $container) => new PhpParser(
            $container[Emulative::class],
            [
                'useIdentifierNodes'         => true,
                'useConsistentVariableNodes' => true,
                'useExpressionStatements'    => true,
                'useNopStatements'           => false,
            ]
        );

        $pimple[DirectoryPrinter::class] = static fn () => new DirectoryPrinter();

        $pimple[StaticPhpFileCopier::class] = static function (Container $container) {
            $traverser = new NodeTraverser();
            $traverser->addVisitor(
                new NamespaceSubstituteVisitor(
                    Configuration::STATIC_PHP_FILE_BASE_NAMESPACE,
                    $container[Configuration::class]->getBaseNamespace()
                )
            );

            return new StaticPhpFileCopier(
                $container[PhpParser::class],
                $container[PhpFilePrinter::class],
                $traverser
            );
        };

        $pimple[TextFilePrinter::class] = static fn (Container $container) => new TextFilePrinter(
            $container[DirectoryPrinter::class]
        );

        $pimple[MetaFilePrinter::class] = static fn (Container $container) => new MetaFilePrinter(
            $container[TextFilePrinter::class]
        );
    }
}
