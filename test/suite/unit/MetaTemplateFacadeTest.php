<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit;

use DoclerLabs\ApiClientGenerator\Input\Specification;
use DoclerLabs\ApiClientGenerator\Meta\TemplateInterface;
use DoclerLabs\ApiClientGenerator\MetaTemplateFacade;
use DoclerLabs\ApiClientGenerator\Output\Meta\MetaFileCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\MetaTemplateFacade
 */
class MetaTemplateFacadeTest extends TestCase
{
    /** @var MetaTemplateFacade */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new MetaTemplateFacade();
    }

    public function testFacade(): void
    {
        $specification  = $this->createMock(Specification::class);
        $fileCollection = $this->createMock(MetaFileCollection::class);

        $childGeneratorOne = $this->createMock(TemplateInterface::class);
        $childGeneratorOne->expects(self::once())
            ->method('render')
            ->with($specification, $fileCollection);
        $childGeneratorTwo = $this->createMock(TemplateInterface::class);
        $childGeneratorTwo->expects(self::once())
            ->method('render')
            ->with($specification, $fileCollection);

        $this->sut->add($childGeneratorOne);
        $this->sut->add($childGeneratorTwo);

        $this->sut->render($specification, $fileCollection);
    }
}
