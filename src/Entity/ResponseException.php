<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Naming\ResponseExceptionNaming;

class ResponseException
{
    private int $statusCode;

    public function __construct(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getPhpExceptionName(): string
    {
        return ResponseExceptionNaming::getResponseExceptionName($this->statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
