<?php declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test\Request;

use Test\Schema\PatchResourceRequestBody;

class PatchResourceRequest implements RequestInterface
{
    private PatchResourceRequestBody $patchResourceRequestBody;

    public function __construct(PatchResourceRequestBody $patchResourceRequestBody)
    {
        $this->patchResourceRequestBody = $patchResourceRequestBody;
    }

    public function getContentType(): string
    {
        return 'application/json';
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
        return ['Content-Type' => 'application/json'];
    }

    /**
     * @return PatchResourceRequestBody
     */
    public function getBody()
    {
        return $this->patchResourceRequestBody;
    }
}
