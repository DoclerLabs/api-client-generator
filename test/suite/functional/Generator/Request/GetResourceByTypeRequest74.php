<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Request;

class GetResourceByTypeRequest implements RequestInterface
{
    public const RESOURCE_TYPE_ONE = 'one';

    public const RESOURCE_TYPE_TWO = 'two';

    private string $resourceType;

    private string $contentType = '';

    public function __construct(string $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getMethod(): string
    {
        return 'GET';
    }

    public function getRoute(): string
    {
        return strtr('v1/{resource-type}/resource', ['{resource-type}' => $this->resourceType]);
    }

    public function getQueryParameters(): array
    {
        return [];
    }

    public function getRawQueryParameters(): array
    {
        return [];
    }

    public function getCookies(): array
    {
        return [];
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function getBody()
    {
        return null;
    }
}