<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

class Response
{
    private const ROOT_KEY = 'data';
    private int    $statusCode;
    private ?Field $body;

    public function __construct(int $statusCode, Field $body = null)
    {
        $this->statusCode = $statusCode;
        $this->body       = $body;
    }

    public function getBody(): ?Field
    {
        return $this->body;
    }
}
