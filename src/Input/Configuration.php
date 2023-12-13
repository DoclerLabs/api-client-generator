<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\ContainerImplementationStrategy;
use DoclerLabs\ApiClientGenerator\Generator\Implementation\HttpMessageImplementationStrategy;
use Webmozart\Assert\Assert;

class Configuration
{
    public const DEFAULT_CODE_STYLE_CONFIG      = __DIR__ . '/../../.php-cs-fixer.php';
    public const DEFAULT_TEMPLATE_DIRECTORY     = __DIR__ . '/../../template';
    public const DEFAULT_PHP_VERSION            = PhpVersion::VERSION_PHP74;
    public const DEFAULT_SOURCE_DIRECTORY       = 'src';
    public const DEFAULT_HTTP_MESSAGE           = HttpMessageImplementationStrategy::HTTP_MESSAGE_GUZZLE;
    public const DEFAULT_CONTAINER              = ContainerImplementationStrategy::CONTAINER_PIMPLE;
    public const STATIC_PHP_FILE_BASE_NAMESPACE = 'DoclerLabs\\ApiClientGenerator\\Output\\Copy';
    public const STATIC_PHP_FILE_DIRECTORY      = __DIR__ . '/../Output/Copy';

    public function __construct(
        public readonly string $specificationFilePath,
        public readonly string $baseNamespace,
        public readonly string $outputDirectory,
        public readonly string $sourceDirectory,
        public readonly string $codeStyleConfig,
        public readonly string $packageName,
        public readonly float $phpVersion,
        public readonly ?string $generatorVersion,
        public readonly string $composerJsonTemplateDir,
        public readonly string $readmeMdTemplateDir,
        public readonly string $httpMessage,
        public readonly string $container
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
    }
}
