<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Response\Mapper;

use Psr\Http\Message\ResponseInterface;

interface ResponseMapperInterface
{
    public function map(ResponseInterface $response);
}
