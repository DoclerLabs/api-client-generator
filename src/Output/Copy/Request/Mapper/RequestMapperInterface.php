<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Request\Mapper;

use DoclerLabs\ApiClientGenerator\Output\Copy\Request\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RequestMapperInterface
{
    public function map(RequestInterface $request): ServerRequestInterface;
}
