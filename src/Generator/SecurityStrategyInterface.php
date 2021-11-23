<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Generator;

use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\Specification;

interface SecurityStrategyInterface
{
    public function getProperties(Operation $operation, Specification $specification): array;

    public function getConstructorParams(Operation $operation, Specification $specification): array;

    public function getConstructorParamInits(Operation $operation, Specification $specification): array;

    public function getSecurityHeaders(Operation $operation, Specification $specification): array;
}
