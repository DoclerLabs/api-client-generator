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
    private const NAME        = 'operation';
    private const DESCRIPTION = 'Very important operation';
    private const TAGS        = ['one', 'two'];
    private Operation $sut;
    private array     $errorResponses;
    /** @var Request|MockObject */
    private $request;
    /** @var Response|MockObject */
    private $succesfulResponse;

    protected function setUp(): void
    {
        $this->request           = $this->createMock(Request::class);
        $this->succesfulResponse = $this->createMock(Response::class);
        $this->errorResponses    = [
            $this->createMock(Response::class),
            $this->createMock(Response::class),
        ];

        $this->sut = new Operation(
            self::NAME,
            self::DESCRIPTION,
            $this->request,
            $this->succesfulResponse,
            $this->errorResponses,
            self::TAGS,
        );
    }

    public function testGetSuccessfulResponse(): void
    {
        self::assertEquals($this->succesfulResponse, $this->sut->getSuccessfulResponse());
    }

    public function testGetTags(): void
    {
        self::assertEquals(self::TAGS, $this->sut->getTags());
    }

    public function testGetName(): void
    {
        self::assertEquals(self::NAME, $this->sut->getName());
    }

    public function testGetRequest(): void
    {
        self::assertEquals($this->request, $this->sut->getRequest());
    }

    public function testGetErrorResponses(): void
    {
        self::assertEquals($this->errorResponses, $this->sut->getErrorResponses());
    }

    public function testGetDescription(): void
    {
        self::assertEquals(self::DESCRIPTION, $this->sut->getDescription());
    }
}
