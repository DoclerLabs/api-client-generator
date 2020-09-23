<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional;

use DoclerLabs\ApiClientGenerator\Input\Configuration;

class ConfigurationBuilder
{
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

    private function __construct()
    {
        $this->specificationFilePath   = '/dir/path/openapi.yaml';
        $this->baseNamespace           = 'Test';
        $this->outputDirectory         = '/dir/output';
        $this->codeStyleConfig         = __DIR__ . '/../../../.php_cs.php';
        $this->packageName             = 'test/test-api-client';
        $this->phpVersion              = Configuration::DEFAULT_PHP_VERSION;
        $this->composerJsonTemplateDir = Configuration::DEFAULT_TEMPLATE_DIRECTORY;
        $this->readmeMdTemplateDir     = Configuration::DEFAULT_TEMPLATE_DIRECTORY;
        $this->httpClient              = Configuration::DEFAULT_HTTP_CLIENT;
        $this->httpMessage             = Configuration::DEFAULT_HTTP_MESSAGE;
    }

    public static function fake(): self
    {
        return new self();
    }

    public function withSpecificationFilePath(string $specificationFilePath): self
    {
        $this->specificationFilePath = $specificationFilePath;

        return $this;
    }

    public function withBaseNamespace(string $baseNamespace): self
    {
        $this->baseNamespace = $baseNamespace;

        return $this;
    }

    public function withOutputDirectory(string $outputDirectory): self
    {
        $this->outputDirectory = $outputDirectory;

        return $this;
    }

    public function withCodeStyleConfig(string $codeStyleConfig): self
    {
        $this->codeStyleConfig = $codeStyleConfig;

        return $this;
    }

    public function withPackageName(string $packageName): self
    {
        $this->packageName = $packageName;

        return $this;
    }

    public function withPhpVersion(string $phpVersion): self
    {
        $this->phpVersion = $phpVersion;

        return $this;
    }

    public function withComposerJsonTemplateDir(string $composerJsonTemplateDir): self
    {
        $this->composerJsonTemplateDir = $composerJsonTemplateDir;

        return $this;
    }

    public function withReadmeMdTemplateDir(string $readmeMdTemplateDir): self
    {
        $this->readmeMdTemplateDir = $readmeMdTemplateDir;

        return $this;
    }

    public function withHttpClient(string $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function withHttpMessage(string $httpMessage): self
    {
        $this->httpMessage = $httpMessage;

        return $this;
    }

    public function build(): Configuration
    {
        return new Configuration(
            $this->specificationFilePath,
            $this->baseNamespace,
            $this->outputDirectory,
            $this->codeStyleConfig,
            $this->packageName,
            $this->phpVersion,
            $this->composerJsonTemplateDir,
            $this->readmeMdTemplateDir,
            $this->httpClient,
            $this->httpMessage
        );
    }
}