<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Entity;

use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;

class Response
{
    public function __construct(
        public readonly int $statusCode,
        public readonly ?Field $body = null,
        public readonly array $bodyContentTypes = []
    ) {
        $unsupportedContentTypes = ContentType::filterUnsupported($bodyContentTypes);

        if (!empty($unsupportedContentTypes)) {
            throw new InvalidSpecificationException(
                sprintf('Response content-type %s is not currently supported.', json_encode($unsupportedContentTypes))
            );
        }
    }
}
