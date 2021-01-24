<?php
declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\GeneratorInterface;
use DoclerLabs\ApiClientGenerator\Input\Configuration;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\Output\PhpFilePrinter;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

abstract class AbstractGeneratorTest extends TestCase
{
    public const BASE_NAMESPACE = 'Test';
    protected GeneratorInterface $sut;
    protected FileReader         $specificationReader;
    protected Parser             $specificationParser;
    protected PhpFileCollection  $fileRegistry;
    protected PhpFilePrinter     $printer;

    /**
     * @dataProvider exampleProvider
     */
    public function testGenerate(
        string $specificationFilePath,
        string $expectedResultFilePath,
        string $resultClassName,
        Configuration $configuration
    ): void {
        $this->setUpContainer($configuration);
        $specificationPath  = __DIR__ . $specificationFilePath;
        $expectedResultPath = __DIR__ . $expectedResultFilePath;
        self::assertFileExists($specificationPath);
        self::assertFileExists($expectedResultPath);

        $data          = $this->specificationReader->read($specificationPath);
        $specification = $this->specificationParser->parse($data, $specificationPath);

        $this->sut->generate($specification, $this->fileRegistry);

        $actualResultPath = sprintf('%s/_temp.php', sys_get_temp_dir());
        $this->printer->print($actualResultPath, $this->fileRegistry->get($resultClassName));

        self::assertFileEquals($expectedResultPath, $actualResultPath);
    }

    abstract public function exampleProvider(): array;

    protected function setUpContainer(Configuration $configuration): void
    {
        $container = new Container();
        $container->register(new ServiceProvider());
        set_error_handler(
            static function (): bool {
                return true;
            },
            E_USER_WARNING
        );
        $container[Configuration::class] = static function () use ($configuration) {
            return $configuration;
        };

        $this->sut                 = $container[$this->generatorClassName()];
        $this->specificationReader = $container[FileReader::class];
        $this->specificationParser = $container[Parser::class];
        $this->fileRegistry        = new PhpFileCollection();
        $this->printer             = $container[PhpFilePrinter::class];
    }

    abstract protected function generatorClassName(): string;
}
