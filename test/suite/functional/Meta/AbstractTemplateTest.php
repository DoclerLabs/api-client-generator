<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Functional\Meta;

use DoclerLabs\ApiClientGenerator\Input\Parser;
use DoclerLabs\ApiClientGenerator\Input\FileReader;
use DoclerLabs\ApiClientGenerator\Meta\TemplateInterface;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use DoclerLabs\ApiClientGenerator\ServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

abstract class AbstractTemplateTest extends TestCase
{
    protected TemplateInterface  $sut;
    protected FileReader             $specificationReader;
    protected Parser             $specificationParser;
    protected MetaFileCollection $fileRegistry;

    public function setUp(): void
    {
        $container = new Container();
        $container->register(new ServiceProvider());

        set_error_handler(
            static function (int $code, string $message) {
            },
            E_USER_WARNING
        );

        $this->sut                 = $this->sutTemplate($container);
        $this->specificationReader = $container[FileReader::class];
        $this->specificationParser = $container[Parser::class];
        $this->fileRegistry        = new MetaFileCollection('');
    }

    /**
     * @dataProvider exampleProvider
     */
    public function testGenerate(
        string $specificationFilePath,
        string $expectedResultFilePath,
        string $resultFileName
    ): void {
        $absoluteSpecificationPath  = __DIR__ . $specificationFilePath;
        $absoluteExpectedResultPath = __DIR__ . $expectedResultFilePath;
        $this->assertFileExists($absoluteSpecificationPath);
        $this->assertFileExists($absoluteExpectedResultPath);

        $data          = $this->specificationReader->read($absoluteSpecificationPath);
        $specification = $this->specificationParser->parse($data, $absoluteSpecificationPath);

        $this->sut->render($specification, $this->fileRegistry);

        $result = $this->fileRegistry->get($resultFileName)->getContent();

        $this->assertStringEqualsFile($absoluteExpectedResultPath, $result);
    }

    abstract public function exampleProvider(): array;

    abstract protected function sutTemplate(Container $container): TemplateInterface;
}
