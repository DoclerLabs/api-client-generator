<?php

namespace DoclerLabs\ApiClientGenerator\Naming;

use cebe\openapi\spec\Operation;
use UnexpectedValueException;

class OperationNaming
{
    public static function getOperationName(Operation $operation): string
    {
        if ($operation->operationId === null) {
            throw new UnexpectedValueException('Operation Id is not set up for operation: ' . $operation->description);
        }

        return (string)$operation->operationId;
    }
}
