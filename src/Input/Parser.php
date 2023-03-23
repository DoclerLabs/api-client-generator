<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldCollection;
use DoclerLabs\ApiClientGenerator\Entity\OperationCollection;
use DoclerLabs\ApiClientGenerator\Input\Factory\OperationCollectionFactory;

class Parser
{
    private OperationCollectionFactory $operationCollectionFactory;

    public function __construct(OperationCollectionFactory $operationCollectionFactory)
    {
        $this->operationCollectionFactory = $operationCollectionFactory;
    }

    public function parse(array $data, string $contextUri): Specification
    {
        $openApi = new OpenApi($data);

        if (!$openApi->validate()) {
            throw new InvalidSpecificationException(
                'OpenAPI specification validation failed: ' . json_encode($openApi->getErrors(), JSON_THROW_ON_ERROR)
            );
        }
        $openApi->setReferenceContext(new ReferenceContext($openApi, $contextUri));
        $operations              = $this->operationCollectionFactory->create($openApi);
        $compositeRequestFields  = $this->extractCompositeRequestFields($operations);
        $compositeResponseFields = $this->extractCompositeResponseFields($operations);

        return new Specification(
            $openApi,
            $operations,
            $compositeRequestFields,
            $compositeResponseFields,
        );
    }

    private function extractCompositeRequestFields(OperationCollection $operations): FieldCollection
    {
        $allFields = new FieldCollection();
        foreach ($operations as $operation) {
            $request = $operation->getRequest();
            foreach ($request->getFields() as $field) {
                $this->extractField($field, $allFields);
            }
        }

        return $allFields;
    }

    private function extractCompositeResponseFields(OperationCollection $operations): FieldCollection
    {
        $allFields = new FieldCollection();
        foreach ($operations as $operation) {
            $responseRoot = $operation->getSuccessfulResponse()->getBody();
            if ($responseRoot !== null) {
                $this->extractField($responseRoot, $allFields);
            }
        }

        return $allFields;
    }

    private function extractField(Field $field, FieldCollection $allFields): void
    {
        if ($field->isObject()) {
            $allFields->add($field);
            $this->extractPropertyFields($field, $allFields);
        }

        if ($field->isArrayOfObjects()) {
            $allFields->add($field);
            $allFields->add($field->getArrayItem());
            $this->extractPropertyFields($field->getArrayItem(), $allFields);
        }
    }

    private function extractPropertyFields(Field $rootObject, FieldCollection $allFields): void
    {
        $fields = $rootObject->getObjectProperties();
        if ($fields !== null) {
            foreach ($fields as $property) {
                $this->extractField($property, $allFields);
            }
        }
    }
}
