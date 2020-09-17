<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Mapper;

use Psr\Http\Message\ResponseInterface;

interface ResponseMapperInterface
{
    public function map(ResponseInterface $response);
}
