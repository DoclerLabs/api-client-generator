<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Output\Copy\Request\Body;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

class EncodedRequestBody implements StreamInterface
{
    use StreamDecoratorTrait;

    public function decode(): array
    {
        // TODO: Implement getContents() method.
    }
}