<?php

namespace DoclerLabs\ApiClientGenerator\Input;

class Configuration
{
    private string $filePath;
    private string $namespace;
    private string $directory;
    private string $codeStyleConfig;
    private string $packageName;
    private string $composerJsonTemplatePath;
    private string $readmeMdTemplatePath;

    public function __construct(
        string $filePath,
        string $namespace,
        string $directory,
        string $codeStyleConfig,
        string $packageName,
        string $composerJsonTemplatePath,
        string $readmeMdTemplatePath
    ) {
        $this->filePath                 = $filePath;
        $this->namespace                = $namespace;
        $this->directory                = $directory;
        $this->codeStyleConfig          = $codeStyleConfig;
        $this->packageName              = $packageName;
        $this->composerJsonTemplatePath = $composerJsonTemplatePath;
        $this->readmeMdTemplatePath     = $readmeMdTemplatePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getCodeStyleConfig(): string
    {
        return $this->codeStyleConfig;
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getComposerJsonTemplatePath(): string
    {
        return $this->composerJsonTemplatePath;
    }

    public function getReadmeMdTemplatePath(): string
    {
        return $this->readmeMdTemplatePath;
    }
}
