<?php

namespace DoclerLabs\ApiClientGenerator\Entity;

class Operation
{
    private string   $name;
    private Request  $request;
    private Response $successfulResponse;
    private array    $errorResponses;

    public function __construct(
        string $name,
        Request $request,
        Response $successfulResponse,
        array $errorResponses = []
    ) {
        $this->name               = $name;
        $this->request            = $request;
        $this->successfulResponse = $successfulResponse;
        $this->errorResponses     = $errorResponses;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSuccessfulResponse(): Response
    {
        return $this->successfulResponse;
    }

    public function getErrorResponses(): array
    {
        return $this->errorResponses;
    }
}
