<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Request\Mapper;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;

interface RequestMapperInterface
{
    public function map(RequestInterface $request): PsrRequestInterface;
}
