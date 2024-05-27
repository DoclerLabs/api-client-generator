<?php

declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Request;

use Test\Schema\PatchYetAnotherSubResourceRequestBody;
use Test\Schema\SerializableInterface;
use Test\Schema\SubResourceFilter;

class PatchYetAnotherSubResourceRequest implements RequestInterface
{
    private ?SubResourceFilter $filter = null;

    private string $contentType = 'application/json';

    public function __construct(private readonly PatchYetAnotherSubResourceRequestBody $patchYetAnotherSubResourceRequestBody, private readonly string $apiKey)
    {
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setFilter(SubResourceFilter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getMethod(): string
    {
        return 'PATCH';
    }

    public function getRoute(): string
    {
        return 'v1/resources/sub/sub-resource/{resourceId}';
    }

    public function getQueryParameters(): array
    {
        return array_merge(array_map(static function ($value) {
            return $value instanceof SerializableInterface ? $value->toArray() : $value;
        }, array_filter(['filter' => $this->filter], static function ($value) {
            return null !== $value;
        })), ['apikey' => $this->apiKey]);
    }

    public function getRawQueryParameters(): array
    {
        return ['filter' => $this->filter, 'apikey' => $this->apiKey];
    }

    public function getCookies(): array
    {
        return [];
    }

    public function getHeaders(): array
    {
        return ['Content-Type' => $this->contentType];
    }

    /**
     * @return PatchYetAnotherSubResourceRequestBody
     */
    public function getBody()
    {
        return $this->patchYetAnotherSubResourceRequestBody;
    }
}
