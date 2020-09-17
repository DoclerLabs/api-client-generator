<?php

namespace Test\Request;

use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Request\RequestInterface;
use Test\Schema\PatchResourceRequestBody;

class PatchResourceRequest implements RequestInterface
{
    /** @var PatchResourceRequestBody */
    private $patchResourceRequestBody;
    /**
     * @param PatchResourceRequestBody $patchResourceRequestBody
    */
    public function __construct(PatchResourceRequestBody $patchResourceRequestBody)
    {
        $this->patchResourceRequestBody = $patchResourceRequestBody;
    }
    /**
     * @return string
    */
    public function getMethod() : string
    {
        return 'PATCH';
    }
    /**
     * @return string
    */
    public function getRoute() : string
    {
        return 'v1/resources/{resourceId}';
    }
    /**
     * @return array
    */
    public function getQueryParameters() : array
    {
        return array();
    }
    /**
     * @return array
    */
    public function getCookies() : array
    {
        return array();
    }
    /**
     * @return array
    */
    public function getHeaders() : array
    {
        return array();
    }
    /**
     * @return PatchResourceRequestBody
    */
    public function getBody() : PatchResourceRequestBody
    {
        return $this->patchResourceRequestBody;
    }
}