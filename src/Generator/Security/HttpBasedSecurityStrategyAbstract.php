<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use PhpParser\Node\Expr;

abstract class HttpBasedSecurityStrategyAbstract extends SecurityStrategyAbstract
{
    abstract protected function getScheme(): string;

    abstract protected function getAuthorizationHeader(): Expr;

    public function getType(): string
    {
        return 'http';
    }

    public function getSecurityHeaders(Operation $operation, Specification $specification): array
    {
        $headers = [];

        if ($this->isAuthenticationAvailable($operation, $specification)) {
            $headers['Authorization'] = $this->getAuthorizationHeader();
        }

        return $headers;
    }

    protected function matches(SecurityScheme $securityScheme): bool
    {
        return parent::matches($securityScheme)
            && $securityScheme->scheme === $this->getScheme();
    }
}
