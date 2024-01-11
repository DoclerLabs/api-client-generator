<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Request;

use Test\Schema\PatchResourceRequestBody;
use Test\Schema\SerializableInterface;

class PatchResourceRequest implements RequestInterface
{
    public const ACCEPT_APPLICATION_JSON = 'application/json';

    public const ACCEPT_APPLICATION_XML = 'application/xml';

    private string $accept;

    private PatchResourceRequestBody $patchResourceRequestBody;

    private string $contentType = 'application/json';

    private string $apiKey;

    public function __construct(PatchResourceRequestBody $patchResourceRequestBody, string $apiKey, string $accept = 'application/json')
    {
        $this->accept                   = $accept;
        $this->patchResourceRequestBody = $patchResourceRequestBody;
        $this->apiKey                   = $apiKey;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getMethod(): string
    {
        return 'PATCH';
    }

    public function getRoute(): string
    {
        return 'v1/resources/{resourceId}';
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
        return array_merge(['X-API-KEY' => $this->apiKey, 'Content-Type' => $this->contentType], array_map(static function ($value) {
            return $value instanceof SerializableInterface ? $value->toArray() : $value;
        }, array_filter(['Accept' => $this->accept], static function ($value) {
            return null !== $value;
        })));
    }

    /**
     * @return PatchResourceRequestBody
     */
    public function getBody()
    {
        return $this->patchResourceRequestBody;
    }
}
