<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Operation as OpenApiOperation;
use cebe\openapi\spec\Reference;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use Throwable;

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
        if ($operation->operationId === null) {
            throw new InvalidSpecificationException(
                sprintf('Mandatory operationId field is missing: [%s] %s', $method, $path)
            );
        }

        $operationId = $operation->operationId;
        $parameters  = array_merge($commonParameters, $operation->parameters ?? []);
        $requestBody = $operation->requestBody;
        if ($requestBody instanceof Reference) {
            $requestBody = $requestBody->resolve();
        }

        try {
            return new Operation(
                $operationId,
                $operation->description ?? '',
                $this->requestMapper->create($operationId, $path, $method, $parameters, $requestBody),
                $this->responseMapper->createSuccessful($operationId, $operation->responses->getResponses()),
                $this->responseMapper->createPossibleErrors($operation->responses->getResponses()),
                $operation->tags
            );
        } catch (Throwable $exception) {
            throw new InvalidSpecificationException(
                sprintf('Error on mapping `%s`: %s', $operationId, $exception->getMessage())
            );
        }
    }
}
