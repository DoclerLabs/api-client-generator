<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\PathItem;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Naming\OperationNaming;
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

    public function create(PathItem $pathItem, string $path, string $method): Operation
    {
        switch ($method) {
            case Request::GET:
                $operation = $pathItem->get;
                break;
            case Request::POST:
                $operation = $pathItem->post;
                break;
            case Request::PUT:
                $operation = $pathItem->put;
                break;
            case Request::PATCH:
                $operation = $pathItem->patch;
                break;
            case Request::DELETE:
                $operation = $pathItem->delete;
                break;
            default:
                throw new InvalidSpecificationException(
                    sprintf('Unsupported request method `%s` in `%s`.', $method, $path)
                );
        }

        $name = OperationNaming::getOperationName($operation);

        $parameters = array_merge(
            $pathItem->parameters ?? [],
            $operation->parameters ?? []
        );

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
