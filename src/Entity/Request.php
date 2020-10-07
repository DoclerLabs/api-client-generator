<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

class Request
{
    public const GET             = 'GET';
    public const POST            = 'POST';
    public const PUT             = 'PUT';
    public const PATCH           = 'PATCH';
    public const OPTIONS         = 'OPTIONS';
    public const DELETE          = 'DELETE';
    public const HEAD            = 'HEAD';
    public const ALLOWED_METHODS = [
        self::GET,
        self::POST,
        self::PUT,
        self::PATCH,
        self::OPTIONS,
        self::DELETE,
        self::HEAD,
    ];
    private string               $path;
    private string               $method;
    private RequestFieldRegistry $fields;
    private string               $contentType;

    public function __construct(
        string $path,
        string $method,
        RequestFieldRegistry $fieldCollection,
        string $contentType
    ) {
        $this->path        = $this->toRelativePath($path);
        $this->method      = $method;
        $this->fields      = $fieldCollection;
        $this->contentType = $contentType;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getFields(): RequestFieldRegistry
    {
        return $this->fields;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    private function toRelativePath(string $path): string
    {
        return ltrim($path, '/');
    }
}
