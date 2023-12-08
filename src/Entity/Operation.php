<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

class Operation
{
    private string $name;
    private string $description;
    private Request $request;
    private array $successfulResponses;
    private array $errorResponses;
    private array $tags;
    private array $security;

    public function __construct(
        string $name,
        string $description,
        Request $request,
        array $successfulResponses,
        array $errorResponses = [],
        array $tags = [],
        array $security = []
    ) {
        $this->name                = $name;
        $this->description         = $description;
        $this->request             = $request;
        $this->successfulResponses = $successfulResponses;
        $this->errorResponses      = $errorResponses;
        $this->tags                = $tags;
        $this->security            = $security;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Response[]
     */
    public function getSuccessfulResponses(): array
    {
        return $this->successfulResponses;
    }

    public function getErrorResponses(): array
    {
        return $this->errorResponses;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getSecurity(): array
    {
        return $this->security;
    }
}
