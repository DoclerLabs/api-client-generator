<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Entity\Operation as EntityOperation;
use DoclerLabs\ApiClientGenerator\Input\Specification;

class ApiKeyInQuerySecurityStrategy extends ApiKeyBasedSecurityStrategyAbstract
{
    public function getSecurityQueryParameters(EntityOperation $operation, Specification $specification): array
    {
        $queryParameters = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $queryParameterName = $this->getSecurityScheme($operation, $specification)->name;

            $queryParameters[$queryParameterName] = $this->builder->localPropertyFetch(self::PROPERTY_API_KEY);
        }

        return $queryParameters;
    }

    protected function matches(SecurityScheme $securityScheme): bool
    {
        return parent::matches($securityScheme)
            && $securityScheme->in === 'query';
    }
}
