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
        float $phpVersion,
        string $generatorVersion,
        string $composerJsonTemplateDir,
        string $readmeMdTemplateDir,
        string $httpMessage,
        string $container,
        array $includeTags,
        array $excludeTags
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
            $container,
            $includeTags,
            $excludeTags
        );

        self::assertEquals($openapiFilePath, $sut->specificationFilePath);
        self::assertEquals($namespace, $sut->baseNamespace);
        self::assertEquals($outputDirectory, $sut->outputDirectory);
        self::assertEquals($sourceDirectory, $sut->sourceDirectory);
        self::assertEquals($codeStyleConfig, $sut->codeStyleConfig);
        self::assertEquals($packageName, $sut->packageName);
        self::assertEquals($phpVersion, $sut->phpVersion);
        self::assertEquals($generatorVersion, $sut->generatorVersion);
        self::assertEquals($composerJsonTemplateDir, $sut->composerJsonTemplateDir);
        self::assertEquals($readmeMdTemplateDir, $sut->readmeMdTemplateDir);
        self::assertEquals($httpMessage, $sut->httpMessage);
        self::assertEquals($container, $sut->container);
        self::assertEquals($includeTags, $sut->includeTags);
        self::assertEquals($excludeTags, $sut->excludeTags);
    }

    public function validConfigurationOptions(): array
    {
        return [
            [
                '/dir/path/openapi.yaml',
                'Test',
                '/dir/output',
                'another-dir',
                '/dir/.php-cs-fixer.php',
                'test/test-api-client',
                7.1,
                '5.6.0',
                __DIR__,
                __DIR__,
                Configuration::DEFAULT_HTTP_MESSAGE,
                Configuration::DEFAULT_CONTAINER,
                ['tag1', 'tag2'],
                ['tag3', 'tag4'],
            ],
        ];
    }
}
