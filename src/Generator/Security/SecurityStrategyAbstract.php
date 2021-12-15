<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Entity\ImportCollection;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use PhpParser\Node\Expr;

abstract class SecurityStrategyAbstract
{
    abstract protected function getScheme(): string;

    abstract protected function getType(): string;

    abstract protected function getAuthorizationHeader(): Expr;

    public function getImports(string $baseNamespace): ImportCollection
    {
        return new ImportCollection();
    }

    public function getSecurityHeaders(Operation $operation, Specification $specification): array
    {
        $headers = [];

        foreach ($this->loopSecuritySchemes($operation, $specification) as $securityScheme) {
            /** @var SecurityScheme $securityScheme */
            if (
                $securityScheme->scheme === $this->getScheme()
                && $securityScheme->type === $this->getType()
            ) {
                $headers['Authorization'] = $this->getAuthorizationHeader();
            }
        }

        return $headers;
    }

    protected function isAuthenticationAvailable(Operation $operation, Specification $specification): bool
    {
        foreach ($this->loopSecuritySchemes($operation, $specification) as $securityScheme) {
            /** @var SecurityScheme $securityScheme */
            if (
                $securityScheme->scheme === $this->getScheme()
                && $securityScheme->type === $this->getType()
            ) {
                return true;
            }
        }

        return false;
    }

    private function loopSecuritySchemes(Operation $operation, Specification $specification): iterable
    {
        if (
            !empty($specification->getSecuritySchemes())
            && !empty($operation->getSecurity())
        ) {
            foreach ($specification->getSecuritySchemes() as $name => $securityScheme) {
                /** @var SecurityRequirement $securityRequirement */
                foreach ($operation->getSecurity() as $securityRequirement) {
                    if (isset($securityRequirement->$name)) {
                        yield $securityScheme;
                    }
                }
            }
        }

        yield from [];
    }
}