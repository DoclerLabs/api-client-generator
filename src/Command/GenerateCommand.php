<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Command;

use DoclerLabs\ApiClientGenerator\CodeGeneratorFacade;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\MetaTemplateFacade;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use DoclerLabs\ApiClientGenerator\Output\MetaFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Output\PhpFilePrinter;
use DoclerLabs\ApiClientGenerator\Output\StaticPhpFilePrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateCommand extends Command
{
    private Configuration        $configuration;
    private CodeGeneratorFacade  $codeGenerator;
    private FileReader           $fileReader;
    private Parser               $parser;
    private PhpFilePrinter       $phpPrinter;
    private MetaTemplateFacade   $metaTemplate;
    private MetaFilePrinter      $templatePrinter;
    private Finder               $fileFinder;
    private StaticPhpFilePrinter $staticPhpPrinter;

    public function __construct(
        Configuration $configuration,
        FileReader $fileReader,
        Parser $parser,
        CodeGeneratorFacade $codeGenerator,
        PhpFilePrinter $phpPrinter,
        MetaTemplateFacade $metaTemplate,
        MetaFilePrinter $templatePrinter,
        Finder $fileFinder,
        StaticPhpFilePrinter $staticPhpCopier
    ) {
        parent::__construct();
        $this->configuration    = $configuration;
        $this->fileReader       = $fileReader;
        $this->parser           = $parser;
        $this->codeGenerator    = $codeGenerator;
        $this->phpPrinter       = $phpPrinter;
        $this->metaTemplate     = $metaTemplate;
        $this->templatePrinter  = $templatePrinter;
        $this->fileFinder       = $fileFinder;
        $this->staticPhpPrinter = $staticPhpCopier;
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
        $specificationFilePath = $this->configuration->getSpecificationFilePath();

        $specification = $this->parser->parse(
            $this->fileReader->read($specificationFilePath),
            $specificationFilePath
        );

        $this->generatePhpFiles($output, $specification);
        $this->generateMetaFiles($output, $specification);
        $this->copyStaticPhpFiles($output);

        return Command::SUCCESS;
    }

    private function generatePhpFiles(OutputInterface $output, Specification $specification): void
    {
        $phpFiles = new PhpFileCollection();
        $this->codeGenerator->generate($specification, $phpFiles);

        $output->writeln(sprintf('<info>AST generated for %d PHP files.</info>', $phpFiles->count()));
        $output->writeln(sprintf('Write PHP files to %s:', $this->configuration->getOutputDirectory()));

        $progressBar = new ProgressBar($output, $phpFiles->count());
        $progressBar->start();
        foreach ($phpFiles as $phpFile) {
            $this->phpPrinter->print(
                sprintf('%s/%s', $this->configuration->getOutputDirectory(), $phpFile->getFileName()),
                $phpFile
            );
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    private function generateMetaFiles(OutputInterface $output, Specification $specification): void
    {
        $metaFiles = new MetaFileCollection();
        $this->metaTemplate->render($specification, $metaFiles);

        $output->writeln(sprintf('<info>Templates rendered for %d meta files.</info>', $metaFiles->count()));
        $output->writeln(sprintf('Write meta files to %s:', $this->configuration->getOutputDirectory()));

        $progressBar = new ProgressBar($output, $metaFiles->count());
        $progressBar->start();
        foreach ($metaFiles as $metaFile) {
            $this->templatePrinter->print(
                sprintf('%s/%s', $this->configuration->getOutputDirectory(), $metaFile->getFilePath()),
                $metaFile
            );
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    private function copyStaticPhpFiles(OutputInterface $output): void
    {
        $originalFiles = $this->fileFinder
            ->files()
            ->name('*.php')
            ->in(Configuration::STATIC_PHP_FILE_DIRECTORY);

        $output->writeln(sprintf('<info>Collected %d static PHP files.</info>', $originalFiles->count()));
        $output->writeln(sprintf('Copy static PHP files to %s:', $this->configuration->getOutputDirectory()));

        $progressBar = new ProgressBar($output, $originalFiles->count());
        $progressBar->start();
        foreach ($originalFiles as $originalFile) {
            $destinationPath = sprintf(
                '%s/%s',
                $this->configuration->getOutputDirectory(),
                $originalFile->getRelativePathname()
            );

            $this->staticPhpPrinter->print(
                $destinationPath,
                $originalFile
            );

            $progressBar->advance();
        }
        $progressBar->finish();
    }
}
