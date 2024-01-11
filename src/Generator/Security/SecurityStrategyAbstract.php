<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Ast\Builder\CodeBuilder;
use DoclerLabs\ApiClientGenerator\Ast\PhpVersion;
use DoclerLabs\ApiClientGenerator\Entity\ImportCollection;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;
use SplObjectStorage;

abstract class SecurityStrategyAbstract implements SecurityStrategyInterface
{
    private static ?SplObjectStorage $securitySchemePerStrategy = null;

    public function __construct(protected CodeBuilder $builder, protected PhpVersion $phpVersion)
    {
        if (self::$securitySchemePerStrategy === null) {
            self::$securitySchemePerStrategy = new SplObjectStorage();
        }
    }

    public function getImports(string $baseNamespace): ImportCollection
    {
        return new ImportCollection();
    }

    public function getSecurityHeadersStmts(Operation $operation, Specification $specification): array
    {
        return [];
    }

    public function getSecurityHeaders(Operation $operation, Specification $specification): array
    {
        return [];
    }

    public function getSecurityCookies(Operation $operation, Specification $specification): array
    {
        return [];
    }

    public function getSecurityQueryParameters(Operation $operation, Specification $specification): array
    {
        return [];
    }

    protected function isAuthenticationAvailable(Operation $operation, Specification $specification): bool
    {
        return $this->getSecurityScheme($operation, $specification) !== null;
    }

    protected function matches(SecurityScheme $securityScheme): bool
    {
        return $securityScheme->type === $this->getType();
    }

    protected function getSecurityScheme(Operation $operation, Specification $specification): ?SecurityScheme
    {
        foreach ($this->loopSecuritySchemes($operation, $specification) as $securityScheme) {
            /** @var SecurityScheme $securityScheme */
            if ($this->matches($securityScheme)) {
                if (!self::$securitySchemePerStrategy->contains($securityScheme)) {
                    self::$securitySchemePerStrategy->attach($securityScheme, static::class);

                    return $securityScheme;
                }

                if (self::$securitySchemePerStrategy->offsetGet($securityScheme) === static::class) {
                    return $securityScheme;
                }
            }
        }

        return null;
    }

    protected function loopSecuritySchemes(Operation $operation, Specification $specification): iterable
    {
        if (
            !empty($specification->getSecuritySchemes())
            && !empty($operation->security)
        ) {
            foreach ($specification->getSecuritySchemes() as $name => $securityScheme) {
                /** @var SecurityRequirement $securityRequirement */
                foreach ($operation->security as $securityRequirement) {
                    if (isset($securityRequirement->$name)) {
                        yield $securityScheme;
                    }
                }
            }
        }

        yield from [];
    }
}
