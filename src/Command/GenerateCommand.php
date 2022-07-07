<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Command;

use DoclerLabs\ApiClientGenerator\CodeGeneratorFacade;
use DoclerLabs\ApiClientGenerator\Generator\Security\BasicAuthenticationSecurityStrategy;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\MetaTemplateFacade;
use DoclerLabs\ApiClientGenerator\Output\Copy\Request\AuthenticationCredentials;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\DirectoryPrinter;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use DoclerLabs\ApiClientGenerator\Output\MetaFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Output\PhpFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\StaticPhpFileCopier;
use DoclerLabs\ApiClientGenerator\Output\WarningFormatter;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Throwable;

class GenerateCommand extends Command
{
    private Configuration       $configuration;
    private CodeGeneratorFacade $codeGenerator;
    private FileReader          $fileReader;
    private Parser              $parser;
    private PhpFilePrinter      $phpPrinter;
    private DirectoryPrinter    $directoryPrinter;
    private MetaTemplateFacade  $metaTemplate;
    private MetaFilePrinter     $templatePrinter;
    private Finder              $fileFinder;
    private StaticPhpFileCopier $staticPhpPrinter;
    private Filesystem          $filesystem;
    private WarningFormatter    $warningFormatter;

    public function __construct(
        Configuration $configuration,
        FileReader $fileReader,
        Parser $parser,
        CodeGeneratorFacade $codeGenerator,
        PhpFilePrinter $phpPrinter,
        DirectoryPrinter $directoryPrinter,
        MetaTemplateFacade $metaTemplate,
        MetaFilePrinter $templatePrinter,
        Finder $fileFinder,
        StaticPhpFileCopier $staticPhpCopier,
        Filesystem $filesystem,
        WarningFormatter $warningFormatter
    ) {
        parent::__construct();
        $this->configuration    = $configuration;
        $this->fileReader       = $fileReader;
        $this->parser           = $parser;
        $this->codeGenerator    = $codeGenerator;
        $this->phpPrinter       = $phpPrinter;
        $this->directoryPrinter = $directoryPrinter;
        $this->metaTemplate     = $metaTemplate;
        $this->templatePrinter  = $templatePrinter;
        $this->fileFinder       = $fileFinder;
        $this->staticPhpPrinter = $staticPhpCopier;
        $this->filesystem       = $filesystem;
        $this->warningFormatter = $warningFormatter;
    }

    public function configure(): void
    {
        $this->setName('generate');
        $this->setDescription('Generate an api client based on a given OpenApi specification');
        $this->addUsage(
            'OPENAPI={path}/swagger.yaml NAMESPACE=Group\SomeApiClient PACKAGE=dh-group/some-api-client OUTPUT_DIR={path}/generated CODE_STYLE={path}/.php_cs.php ./bin/api-client-generator generate'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initWarningPrinting($input);
        $specificationFilePath = $this->configuration->getSpecificationFilePath();

        $specification = $this->parser->parse(
            $this->fileReader->read($specificationFilePath),
            $specificationFilePath
        );

        $ss = new SymfonyStyle($input, $output);

        $this->backup($ss);

        try {
            $this->generatePhpFiles($ss, $specification);
            $this->copyStaticPhpFiles($ss, $specification);
            $this->copySpecification($ss);
            $this->generateMetaFiles($ss, $specification);
        } catch (Throwable $throwable) {
            $this->restoreBackup($ss);
            trigger_error($throwable->getMessage(), E_USER_WARNING);

            return Command::FAILURE;
        }

        $this->removeBackup($ss);

        return Command::SUCCESS;
    }

    private function generatePhpFiles(StyleInterface $ss, Specification $specification): void
    {
        $phpFiles = new PhpFileCollection();
        $this->codeGenerator->generate($specification, $phpFiles);

        $ss->text(sprintf('<info>AST generated for %d PHP files.</info>', $phpFiles->count()));
        $ss->text(sprintf('Write PHP files to %s:', $this->configuration->getOutputDirectory()));

        $ss->progressStart($phpFiles->count());
        foreach ($phpFiles as $phpFile) {
            $this->phpPrinter->print(
                sprintf(
                    '%s/%s/%s',
                    $this->configuration->getOutputDirectory(),
                    $this->configuration->getSourceDirectory(),
                    $phpFile->getFileName()
                ),
                $phpFile
            );
            $ss->progressAdvance();
        }
        $ss->progressFinish();
    }

    private function generateMetaFiles(StyleInterface $ss, Specification $specification): void
    {
        $metaFiles = new MetaFileCollection();
        $this->metaTemplate->render($specification, $metaFiles);

        $ss->text(sprintf('<info>Templates rendered for %d meta files.</info>', $metaFiles->count()));
        $ss->text(sprintf('Write meta files to %s:', $this->configuration->getOutputDirectory()));

        $ss->progressStart($metaFiles->count());
        foreach ($metaFiles as $metaFile) {
            $this->templatePrinter->print(
                sprintf('%s/%s', $this->configuration->getOutputDirectory(), $metaFile->getFilePath()),
                $metaFile
            );
            $ss->progressAdvance();
        }
        $ss->progressFinish();
    }

    private function copyStaticPhpFiles(StyleInterface $ss, Specification $specification): void
    {
        $blacklistedFiles = $this->getBlacklistedFiles($specification);
        $originalFiles    = $this->fileFinder
            ->files()
            ->name('*.php')
            ->in(Configuration::STATIC_PHP_FILE_DIRECTORY);

        $ss->text(sprintf('<info>Collected %d static PHP files.</info>', $originalFiles->count()));
        $ss->text(sprintf('Copy static PHP files to %s:', $this->configuration->getOutputDirectory()));

        $ss->progressStart($originalFiles->count());
        foreach ($originalFiles as $originalFile) {
            if (!in_array($originalFile->getBasename(), $blacklistedFiles, true)) {
                $destinationPath = sprintf(
                    '%s/%s/%s',
                    $this->configuration->getOutputDirectory(),
                    $this->configuration->getSourceDirectory(),
                    $originalFile->getRelativePathname()
                );

                $this->staticPhpPrinter->copy(
                    $destinationPath,
                    $originalFile
                );
            }

            $ss->progressAdvance();
        }
        $ss->progressFinish();
    }

    private function copySpecification(StyleInterface $ss): void
    {
        $destinationPath = sprintf(
            '%s/doc/%s',
            $this->configuration->getOutputDirectory(),
            basename($this->configuration->getSpecificationFilePath())
        );

        $ss->text(sprintf('Copy specification file to %s.', $destinationPath));

        $this->filesystem->copy(
            $this->configuration->getSpecificationFilePath(),
            $destinationPath
        );
    }

    private function backup(StyleInterface $ss): void
    {
        $ss->text('<info>Backup original source.</info>');

        $originalPath = sprintf(
            '%s/%s',
            $this->configuration->getOutputDirectory(),
            $this->configuration->getSourceDirectory()
        );

        $backupPath = $originalPath . '_old';

        $this->directoryPrinter->move(
            $backupPath,
            $originalPath
        );
    }

    private function restoreBackup(StyleInterface $ss): void
    {
        $ss->text('<error>Restore original source from backup.</error>');

        $originalPath = sprintf(
            '%s/%s',
            $this->configuration->getOutputDirectory(),
            $this->configuration->getSourceDirectory()
        );

        $backupPath = $originalPath . '_old';

        $this->directoryPrinter->move(
            $originalPath,
            $backupPath
        );
    }

    private function removeBackup(StyleInterface $ss): void
    {
        $ss->text('<info>Delete backup.</info>');

        $backupPath = sprintf(
            '%s/%s_old',
            $this->configuration->getOutputDirectory(),
            $this->configuration->getSourceDirectory()
        );

        $this->directoryPrinter->delete($backupPath);
    }

    private function initWarningPrinting(InputInterface $input): void
    {
        if ($input->getOption('quiet')) {
            set_error_handler(
                static function (): bool {
                    return true;
                },
                E_USER_WARNING
            );
        } else {
            set_error_handler($this->warningFormatter, E_USER_WARNING);
        }
    }

    private function getUnusedSerializers(Specification $specification): array
    {
        $contentTypeMapping = [
            XmlContentTypeSerializer::MIME_TYPE            => XmlContentTypeSerializer::class,
            FormUrlencodedContentTypeSerializer::MIME_TYPE => FormUrlencodedContentTypeSerializer::class,
            JsonContentTypeSerializer::MIME_TYPE           => JsonContentTypeSerializer::class,
            VdnApiJsonContentTypeSerializer::MIME_TYPE     => VdnApiJsonContentTypeSerializer::class,
        ];

        $allContentTypes = $specification->getAllContentTypes();

        return array_values(
            array_filter(
                $contentTypeMapping,
                static fn (string $key) => !in_array($key, $allContentTypes, true),
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    /**
     * @return string[]
     */
    private function getUnusedValueObjects(Specification $specification): array
    {
        $unusedClasses = [];

        if (!$specification->isSecuritySchemeEnabled(BasicAuthenticationSecurityStrategy::SCHEME)) {
            $unusedClasses[] = AuthenticationCredentials::class;
        }

        return $unusedClasses;
    }

    /**
     * @return string[]
     */
    private function getBlacklistedFiles(Specification $specification): array
    {
        return array_map(
            static fn ($class) => basename((string)(new ReflectionClass($class))->getFileName()),
            array_merge(
                $this->getUnusedSerializers($specification),
                $this->getUnusedValueObjects($specification)
            )
        );
    }
}
