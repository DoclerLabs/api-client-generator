<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit;

use DoclerLabs\ApiClientGenerator\CodeGeneratorFacade;
use DoclerLabs\ApiClientGenerator\Generator\GeneratorInterface;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Output\Php\PhpFileCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\CodeGeneratorFacade
 */
class CodeGeneratorFacadeTest extends TestCase
{
    /** @var CodeGeneratorFacade */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new CodeGeneratorFacade();
    }

    public function testFacade()
    {
        $specification  = $this->createMock(Specification::class);
        $fileCollection = $this->createMock(PhpFileCollection::class);

        $childGeneratorOne = $this->createMock(GeneratorInterface::class);
        $childGeneratorOne->expects(self::once())
            ->method('generate')
            ->with($specification, $fileCollection);
        $childGeneratorTwo = $this->createMock(GeneratorInterface::class);
        $childGeneratorTwo->expects(self::once())
            ->method('generate')
            ->with($specification, $fileCollection);

        $this->sut->add($childGeneratorOne);
        $this->sut->add($childGeneratorTwo);

        $this->sut->generate($specification, $fileCollection);
    }
}
