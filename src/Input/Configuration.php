<?php

namespace DoclerLabs\ApiClientGenerator\Input;

use Webmozart\Assert\Assert;

class Configuration
{
    public const DEFAULT_CODE_STYLE_CONFIG  = __DIR__ . '/../../.php_cs.php';
    public const DEFAULT_TEMPLATE_DIRECTORY = __DIR__ . '/../../template';
    private string $filePath;
    private string $namespace;
    private string $outputDirectory;
    private string $codeStyleConfig;
    private string $packageName;
    private string $phpVersion;
    private string $composerJsonTemplateDir;
    private string $readmeMdTemplateDir;

    public function __construct(
        string $filePath,
        string $namespace,
        string $outputDirectory,
        string $codeStyleConfig,
        string $packageName,
        string $phpVersion,
        string $composerJsonTemplateDir,
        string $readmeMdTemplateDir
    ) {
        Assert::notEmpty($filePath, 'Specification file path is not provided.');
        Assert::notEmpty($namespace, 'Namespace for generated code is not provided.');
        Assert::notEmpty($outputDirectory, 'Output directory is not provided.');
        Assert::notEmpty($codeStyleConfig, 'Code style config is not provided.');
        Assert::notEmpty($packageName, 'Composer package name is not provided.');
        Assert::notEmpty($phpVersion, 'Php version is not provided.');
        Assert::true(is_dir($composerJsonTemplateDir), 'Passed composer.json.twig directory does not exist.');
        Assert::true(is_dir($readmeMdTemplateDir), 'Passed README.md.twig directory does not exist.');

        $this->filePath                = $filePath;
        $this->namespace               = $namespace;
        $this->outputDirectory         = $outputDirectory;
        $this->codeStyleConfig         = $codeStyleConfig;
        $this->packageName             = $packageName;
        $this->phpVersion              = $phpVersion;
        $this->composerJsonTemplateDir = $composerJsonTemplateDir;
        $this->readmeMdTemplateDir     = $readmeMdTemplateDir;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
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
}
