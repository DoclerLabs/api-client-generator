<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Test\Unit\Entity;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Entity\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoclerLabs\ApiClientGenerator\Entity\Operation
 */
class OperationTest extends TestCase
{
    private const NAME = 'operation';
    private const DESCRIPTION = 'Very important operation';
    private const TAGS = ['one', 'two'];

    private Operation $sut;
    private array $errorResponses;
    /** @var Request|MockObject */
    private $request;
    /** @var Response[]|MockObject[] */
    private array $succesfulResponses;

    protected function setUp(): void
    {
        $this->request            = $this->createMock(Request::class);
        $this->succesfulResponses = [$this->createMock(Response::class)];
        $this->errorResponses     = [
            $this->createMock(Response::class),
            $this->createMock(Response::class),
        ];

        $this->sut = new Operation(
            self::NAME,
            self::DESCRIPTION,
            $this->request,
            $this->succesfulResponses,
            $this->errorResponses,
            self::TAGS,
        );
    }

    public function testGetSuccessfulResponses(): void
    {
        self::assertEquals($this->succesfulResponses, $this->sut->successfulResponses);
    }

    public function testGetTags(): void
    {
        self::assertEquals(self::TAGS, $this->sut->tags);
    }

    public function testGetName(): void
    {
        self::assertEquals(self::NAME, $this->sut->name);
    }

    public function testGetRequest(): void
    {
        self::assertEquals($this->request, $this->sut->request);
    }

    public function testGetErrorResponses(): void
    {
        self::assertEquals($this->errorResponses, $this->sut->errorResponses);
    }

    public function testGetDescription(): void
    {
        self::assertEquals(self::DESCRIPTION, $this->sut->description);
    }
}
