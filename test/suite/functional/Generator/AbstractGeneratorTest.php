<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Generator;

use DoclerLabs\ApiClientGenerator\Generator\GeneratorInterface;
use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

abstract class AbstractGeneratorTest extends TestCase
{
    public const BASE_NAMESPACE = 'Test';
    protected GeneratorInterface $sut;
    protected FileReader             $specificationReader;
    protected Parser             $specificationParser;
    protected PhpFileCollection  $fileRegistry;
    protected Standard           $printer;

    public function setUp(): void
    {
        $container = new Container();
        $container->register(new ServiceProvider());

        set_error_handler(
            static function (int $code, string $message) {
            },
            E_USER_WARNING
        );

        $this->sut                 = $container[$this->generatorClassName()];
        $this->specificationReader = $container[FileReader::class];
        $this->specificationParser = $container[Parser::class];
        $this->fileRegistry        = new PhpFileCollection('', self::BASE_NAMESPACE);
        $this->printer             = new Standard();
    }

    /**
     * @dataProvider exampleProvider
     */
    public function testGenerate(
        string $specificationFilePath,
        string $expectedResultFilePath,
        string $resultClassName
    ): void {
        $absoluteSpecificationPath  = __DIR__ . $specificationFilePath;
        $absoluteExpectedResultPath = __DIR__ . $expectedResultFilePath;
        $this->assertFileExists($absoluteSpecificationPath);
        $this->assertFileExists($absoluteExpectedResultPath);

        $data          = $this->specificationReader->read($absoluteSpecificationPath);
        $specification = $this->specificationParser->parse($data, $absoluteSpecificationPath);

        $this->sut->generate($specification, $this->fileRegistry);

        $result = $this->printer->prettyPrintFile($this->fileRegistry->get($resultClassName)->getNodes());

        $this->assertStringEqualsFile($absoluteExpectedResultPath, $result);
    }

    abstract public function exampleProvider(): array;

    abstract protected function generatorClassName(): string;
}
