<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Operation as OpenApiOperation;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Naming\OperationNaming;
use Throwable;
use UnexpectedValueException;

class OperationFactory
{
    private RequestFactory  $requestMapper;
    private ResponseFactory $responseMapper;

    public function __construct(
        RequestFactory $requestMapper,
        ResponseFactory $responseMapper
    ) {
        $this->requestMapper  = $requestMapper;
        $this->responseMapper = $responseMapper;
    }

    public function create(
        OpenApiOperation $operation,
        string $path,
        string $method,
        array $commonParameters
    ): Operation {
        try {
            $name = OperationNaming::getOperationName($operation);
        } catch (UnexpectedValueException $exception) {
            throw new InvalidSpecificationException($exception->getMessage());
        }

        $p          = $operation->parameters;
        $parameters = array_merge($commonParameters, $operation->parameters ?? []);

        try {
            return new Operation(
                $name,
                $this->requestMapper->create($name, $path, $method, $parameters, $operation->requestBody),
                $this->responseMapper->createSuccessful($name, $operation->responses->getResponses()),
                $this->responseMapper->createPossibleErrors($operation->responses->getResponses())
            );
        } catch (Throwable $exception) {
            throw new InvalidSpecificationException(
                sprintf('Error on mapping `%s`: %s', $name, $exception->getMessage())
            );
        }
    }
}
