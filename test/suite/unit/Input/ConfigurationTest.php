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
        string $readmeMdTemplateDir,
        string $httpClient,
        string $httpMessage
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
            $httpClient,
            $httpMessage
        );

        self::assertEquals($openapiFilePath, $sut->getSpecificationFilePath());
        self::assertEquals($namespace, $sut->getBaseNamespace());
        self::assertEquals($outputDirectory, $sut->getOutputDirectory());
        self::assertEquals($codeStyleConfig, $sut->getCodeStyleConfig());
        self::assertEquals($packageName, $sut->getPackageName());
        self::assertEquals($phpVersion, $sut->getPhpVersion());
        self::assertEquals($composerJsonTemplateDir, $sut->getComposerJsonTemplateDir());
        self::assertEquals($readmeMdTemplateDir, $sut->getReadmeMdTemplateDir());
        self::assertEquals($httpClient, $sut->getHttpClient());
        self::assertEquals($httpMessage, $sut->getHttpMessage());
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
                Configuration::DEFAULT_HTTP_CLIENT,
                Configuration::DEFAULT_HTTP_MESSAGE,
            ],
        ];
    }
}