<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Entity\Operation as EntityOperation;
use DoclerLabs\ApiClientGenerator\Input\Specification;

class ApiKeyInHeaderSecurityStrategy extends ApiKeyBasedSecurityStrategyAbstract
{
    public function getSecurityHeaders(EntityOperation $operation, Specification $specification): array
    {
        $headers = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $headerName = $this->getSecurityScheme($operation, $specification)->name;

            $headers[$headerName] = $this->builder->localPropertyFetch(self::PROPERTY_API_KEY);
        }

        return $headers;
    }

    protected function matches(SecurityScheme $securityScheme): bool
    {
        return parent::matches($securityScheme)
            && $securityScheme->in === 'header';
    }
}
