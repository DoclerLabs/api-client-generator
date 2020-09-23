<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpClientImplementation;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementation;
use Webmozart\Assert\Assert;

class Configuration
{
    public const DEFAULT_CODE_STYLE_CONFIG      = __DIR__ . '/../../.php_cs.php';
    public const DEFAULT_TEMPLATE_DIRECTORY     = __DIR__ . '/../../template';
    public const DEFAULT_HTTP_CLIENT            = HttpClientImplementation::HTTP_CLIENT_GUZZLE7;
    public const DEFAULT_HTTP_MESSAGE           = HttpMessageImplementation::HTTP_MESSAGE_GUZZLE;
    public const DEFAULT_PHP_VERSION            = PhpVersion::VERSION_PHP72;
    public const STATIC_PHP_FILE_BASE_NAMESPACE = 'DoclerLabs\\ApiClientGenerator\\Output\\StaticPhp';
    public const STATIC_PHP_FILE_DIRECTORY      = __DIR__ . '/../Output/StaticPhp';
    private string $specificationFilePath;
    private string $baseNamespace;
    private string $outputDirectory;
    private string $codeStyleConfig;
    private string $packageName;
    private string $phpVersion;
    private string $composerJsonTemplateDir;
    private string $readmeMdTemplateDir;
    private string $httpClient;
    private string $httpMessage;

    public function __construct(
        string $specificationFilePath,
        string $baseNamespace,
        string $outputDirectory,
        string $codeStyleConfig,
        string $packageName,
        string $phpVersion,
        string $composerJsonTemplateDir,
        string $readmeMdTemplateDir,
        string $httpClient,
        string $httpMessage
    ) {
        Assert::notEmpty($specificationFilePath, 'Specification file path is not provided.');
        Assert::notEmpty($baseNamespace, 'Namespace for generated code is not provided.');
        Assert::notEmpty($outputDirectory, 'Output directory is not provided.');
        Assert::notEmpty($codeStyleConfig, 'Code style config is not provided.');
        Assert::notEmpty($packageName, 'Composer package name is not provided.');
        Assert::notEmpty($phpVersion, 'Php version is not provided.');
        Assert::true(is_dir($composerJsonTemplateDir), 'Passed composer.json.twig directory does not exist.');
        Assert::true(is_dir($readmeMdTemplateDir), 'Passed README.md.twig directory does not exist.');
        Assert::notEmpty($httpClient, 'Http client implementation(PSR-18) is not provided.');
        Assert::notEmpty($httpMessage, 'Http message implementation(PSR-7) is not provided.');

        $this->specificationFilePath   = $specificationFilePath;
        $this->baseNamespace           = $baseNamespace;
        $this->outputDirectory         = $outputDirectory;
        $this->codeStyleConfig         = $codeStyleConfig;
        $this->packageName             = $packageName;
        $this->phpVersion              = $phpVersion;
        $this->composerJsonTemplateDir = $composerJsonTemplateDir;
        $this->readmeMdTemplateDir     = $readmeMdTemplateDir;
        $this->httpClient              = $httpClient;
        $this->httpMessage             = $httpMessage;
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

    public function getHttpClient(): string
    {
        return $this->httpClient;
    }

    public function getHttpMessage(): string
    {
        return $this->httpMessage;
    }
}
