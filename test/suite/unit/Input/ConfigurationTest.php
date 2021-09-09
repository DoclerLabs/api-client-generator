<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Input;

use DoclerLabs\ApiClientGenerator\Input\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Input\Configuration
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
        string $sourceDirectory,
        string $codeStyleConfig,
        string $packageName,
        string $phpVersion,
        string $generatorVersion,
        string $composerJsonTemplateDir,
        string $readmeMdTemplateDir,
        string $httpMessage,
        string $container
    ): void {
        $sut = new Configuration(
            $openapiFilePath,
            $namespace,
            $outputDirectory,
            $sourceDirectory,
            $codeStyleConfig,
            $packageName,
            $phpVersion,
            $generatorVersion,
            $composerJsonTemplateDir,
            $readmeMdTemplateDir,
            $httpMessage,
            $container
        );

        self::assertEquals($openapiFilePath, $sut->getSpecificationFilePath());
        self::assertEquals($namespace, $sut->getBaseNamespace());
        self::assertEquals($outputDirectory, $sut->getOutputDirectory());
        self::assertEquals($sourceDirectory, $sut->getSourceDirectory());
        self::assertEquals($codeStyleConfig, $sut->getCodeStyleConfig());
        self::assertEquals($packageName, $sut->getPackageName());
        self::assertEquals($phpVersion, $sut->getPhpVersion());
        self::assertEquals($generatorVersion, $sut->getGeneratorVersion());
        self::assertEquals($composerJsonTemplateDir, $sut->getComposerJsonTemplateDir());
        self::assertEquals($readmeMdTemplateDir, $sut->getReadmeMdTemplateDir());
        self::assertEquals($httpMessage, $sut->getHttpMessage());
        self::assertEquals($container, $sut->getContainer());
    }

    public function validConfigurationOptions(): array
    {
        return [
            [
                '/dir/path/openapi.yaml',
                'Test',
                '/dir/output',
                'another-dir',
                '/dir/.php_cs.php',
                'test/test-api-client',
                '7.1',
                '5.6.0',
                __DIR__,
                __DIR__,
                Configuration::DEFAULT_HTTP_MESSAGE,
                Configuration::DEFAULT_CONTAINER,
            ],
        ];
    }
}
