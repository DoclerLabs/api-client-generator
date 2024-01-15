<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator\Security;

use DoclerLabs\ApiClientGenerator\Entity\ImportCollection;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;

interface SecurityStrategyInterface
{
    /**
     * @return string OpenAPI security schema type
     */
    public function getType(): string;

    public function getProperties(Operation $operation, Specification $specification): array;

    public function getConstructorParams(Operation $operation, Specification $specification): array;

    public function getConstructorParamInits(Operation $operation, Specification $specification): array;

    public function getImports(string $baseNamespace): ImportCollection;

    public function getSecurityHeaders(Operation $operation, Specification $specification): array;

    public function getSecurityHeadersStmts(Operation $operation, Specification $specification): array;

    public function getSecurityCookies(Operation $operation, Specification $specification): array;

    public function getSecurityQueryParameters(Operation $operation, Specification $specification): array;
}
