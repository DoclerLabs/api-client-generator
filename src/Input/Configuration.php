<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use Webmozart\Assert\Assert;

class Configuration
{
    public const DEFAULT_CODE_STYLE_CONFIG      = __DIR__ . '/../../.php_cs.php';
    public const DEFAULT_TEMPLATE_DIRECTORY     = __DIR__ . '/../../template';
    public const DEFAULT_PHP_VERSION            = PhpVersion::VERSION_PHP74;
    public const DEFAULT_SOURCE_DIRECTORY       = 'src';
    public const DEFAULT_HTTP_MESSAGE           = HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE;
    public const DEFAULT_CONTAINER              = ContainerImplementationStrategy::CONTAINER_PIMPLE;
    public const STATIC_PHP_FILE_BASE_NAMESPACE = 'DoclerLabs\\ApiClientGenerator\\Output\\Copy';
    public const STATIC_PHP_FILE_DIRECTORY      = __DIR__ . '/../Output/Copy';
    private string $specificationFilePath;
    private string $baseNamespace;
    private string $outputDirectory;
    private string $sourceDirectory;
    private string $codeStyleConfig;
    private string $packageName;
    private string $phpVersion;
    private ?string $generatorVersion;
    private string $composerJsonTemplateDir;
    private string $readmeMdTemplateDir;
    private string $httpMessage;
    private string $container;

    public function __construct(
        string $specificationFilePath,
        string $baseNamespace,
        string $outputDirectory,
        string $sourceDirectory,
        string $codeStyleConfig,
        string $packageName,
        string $phpVersion,
        ?string $generatorVersion,
        string $composerJsonTemplateDir,
        string $readmeMdTemplateDir,
        string $httpMessage,
        string $container
    ) {
        Assert::notEmpty($specificationFilePath, 'Specification file path is not provided.');
        Assert::notEmpty($baseNamespace, 'Namespace for generated code is not provided.');
        Assert::notEmpty($outputDirectory, 'Output directory is not provided.');
        Assert::notEmpty($sourceDirectory, 'Source directory is not provided.');
        Assert::notEmpty($codeStyleConfig, 'Code style config is not provided.');
        Assert::notEmpty($packageName, 'Composer package name is not provided.');
        Assert::notEmpty($phpVersion, 'Php version is not provided.');
        Assert::true(is_dir($composerJsonTemplateDir), 'Passed composer.json.twig directory does not exist.');
        Assert::true(is_dir($readmeMdTemplateDir), 'Passed README.md.twig directory does not exist.');
        Assert::notEmpty($httpMessage, 'Http message implementation(PSR-7) is not provided.');
        Assert::notEmpty($container, 'Container implementation(PSR-11) is not provided.');

        $this->specificationFilePath   = $specificationFilePath;
        $this->baseNamespace           = $baseNamespace;
        $this->outputDirectory         = $outputDirectory;
        $this->sourceDirectory         = $sourceDirectory;
        $this->codeStyleConfig         = $codeStyleConfig;
        $this->packageName             = $packageName;
        $this->phpVersion              = $phpVersion;
        $this->generatorVersion        = $generatorVersion;
        $this->composerJsonTemplateDir = $composerJsonTemplateDir;
        $this->readmeMdTemplateDir     = $readmeMdTemplateDir;
        $this->httpMessage             = $httpMessage;
        $this->container               = $container;
    }

    public function getSpecificationFilePath(): string
    {
        return $this->specificationFilePath;
    }

    public function getBaseNamespace(): string
    {
        return $this->baseNamespace;
    }

    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }

    public function getSourceDirectory(): string
    {
        return $this->sourceDirectory;
    }

    public function getCodeStyleConfig(): string
    {
        return $this->codeStyleConfig;
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getGeneratorVersion(): ?string
    {
        return $this->generatorVersion;
    }

    public function getComposerJsonTemplateDir(): string
    {
        return $this->composerJsonTemplateDir;
    }

    public function getReadmeMdTemplateDir(): string
    {
        return $this->readmeMdTemplateDir;
    }

    public function getStaticPhpFilesBaseNamespace(): string
    {
        return self::STATIC_PHP_FILE_BASE_NAMESPACE;
    }

    public function getStaticPhpFilesDirectory(): string
    {
        return self::STATIC_PHP_FILE_DIRECTORY;
    }

    public function getHttpMessage(): string
    {
        return $this->httpMessage;
    }

    public function getContainer(): string
    {
        return $this->container;
    }
}
