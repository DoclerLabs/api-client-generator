<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Input;

use DoclerLabs\ApiClientGenerator\Input\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Configuration
 */
class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider validConfigurationOptions
     */
    public function testValidConfiguration(
        string $openapiFilePath,
        string $namespace,
        string $outputDirectory,
        string $codeStyleConfig,
        string $packageName,
        string $phpVersion,
        string $composerJsonTemplateDir,
        string $readmeMdTemplateDir
    ): void {
        $sut = new Configuration(
            $openapiFilePath,
            $namespace,
            $outputDirectory,
            $codeStyleConfig,
            $packageName,
            $phpVersion,
            $composerJsonTemplateDir,
            $readmeMdTemplateDir,
        );

        $this->assertEquals($openapiFilePath, $sut->getFilePath());
        $this->assertEquals($namespace, $sut->getNamespace());
        $this->assertEquals($outputDirectory, $sut->getOutputDirectory());
        $this->assertEquals($codeStyleConfig, $sut->getCodeStyleConfig());
        $this->assertEquals($packageName, $sut->getPackageName());
        $this->assertEquals($phpVersion, $sut->getPhpVersion());
        $this->assertEquals($composerJsonTemplateDir, $sut->getComposerJsonTemplateDir());
        $this->assertEquals($readmeMdTemplateDir, $sut->getReadmeMdTemplateDir());
    }

    public function validConfigurationOptions(): array
    {
        return [
            [
                '/dir/path/openapi.yaml',
                'Test',
                '/dir/output',
                '/dir/.php_cs.php',
                'test/test-api-client',
                '7.1',
                __DIR__,
                __DIR__,
            ],
        ];
    }
}