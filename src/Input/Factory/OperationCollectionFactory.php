<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\OpenApi;
use DoclerLabs\ApiClientGenerator\Entity\OperationCollection;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;

class OperationCollectionFactory
{
    private OperationFactory $operationFactory;

    public function __construct(OperationFactory $operationFactory)
    {
        $this->operationFactory = $operationFactory;
    }

    public function create(OpenApi $specification): OperationCollection
    {
        $collection = new OperationCollection();
        if ($specification->paths === null) {
            throw new InvalidSpecificationException('No paths found in the specification.');
        }

        foreach ($specification->paths as $path => $pathItem) {
            if ($pathItem->get !== null) {
                $collection->add($this->operationFactory->create($pathItem, $path, Request::GET));
            }

            if ($pathItem->patch !== null) {
                $collection->add($this->operationFactory->create($pathItem, $path, Request::PATCH));
            }

            if ($pathItem->post !== null) {
                $collection->add($this->operationFactory->create($pathItem, $path, Request::POST));
            }

            if ($pathItem->put !== null) {
                $collection->add($this->operationFactory->create($pathItem, $path, Request::PUT));
            }

            if ($pathItem->delete !== null) {
                $collection->add($this->operationFactory->create($pathItem, $path, Request::DELETE));
            }
        }

        return $collection;
    }
}
