<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Entity\Operation as EntityOperation;
use DoclerLabs\ApiClientGenerator\Input\Specification;

class ApiKeyInCookieSecurityStrategy extends ApiKeyBasedSecurityStrategyAbstract
{
    public function getSecurityCookies(EntityOperation $operation, Specification $specification): array
    {
        $cookies = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $cookieName = $this->getSecurityScheme($operation, $specification)->name;

            $cookies[$cookieName] = $this->builder->localPropertyFetch(self::PROPERTY_API_KEY);
        }

        return $cookies;
    }

    protected function matches(SecurityScheme $securityScheme): bool
    {
        return parent::matches($securityScheme)
            && $securityScheme->in === 'cookie';
    }
}
