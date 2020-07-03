<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldCollection;
use DoclerLabs\ApiClientGenerator\Entity\OperationCollection;
use DoclerLabs\ApiClientGenerator\Input\Factory\OperationCollectionFactory;
use Symfony\Component\Yaml\Yaml;

class Parser
{
    private OperationCollectionFactory $operationCollectionFactory;

    public function __construct(OperationCollectionFactory $operationCollectionFactory)
    {
        $this->operationCollectionFactory = $operationCollectionFactory;
    }

    public function parseFile(string $fileName): Specification
    {
        if (!is_readable($fileName)) {
            throw new InvalidSpecificationException('File does not exist or not readable: ' . $fileName);
        }

        $ext      = pathinfo($fileName, PATHINFO_EXTENSION);
        $contents = file_get_contents($fileName);
        switch ($ext) {
            case 'yaml':
            case 'yml':
                $openApi = $this->parse(Yaml::parse($contents));
                break;
            case 'json':
                $openApi = $this->parse(json_decode($contents, true, 512, JSON_THROW_ON_ERROR));
                break;
            default:
                throw new InvalidSpecificationException(
                    sprintf('Unknown specification file extension: %s. Supported: yaml, yml, json', $ext)
                );
        }
        $openApi->setReferenceContext(new ReferenceContext($openApi, $fileName));
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

    public function parse(array $data): OpenApi
    {
        $openApi = new OpenApi($data);

        if (!$openApi->validate()) {
            throw new InvalidSpecificationException(
                'OpenAPI specification validation failed: ' . json_encode($openApi->getErrors(), JSON_THROW_ON_ERROR)
            );
        }

        return $openApi;
    }

    private function extractCompositeRequestFields(OperationCollection $operations): FieldCollection
    {
        $allFields = new FieldCollection();
        foreach ($operations as $operation) {
            $request = $operation->getRequest();
            foreach ($request->getFields() as $origin => $fields) {
                foreach ($fields as $field) {
                    $this->extractField($field, $allFields);
                }
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
            $this->extractPropertyField($field, $allFields);
        }

        if ($field->isArrayOfObjects()) {
            $allFields->add($field);
            $allFields->add($field->getStructure()->getArrayItem());
            $this->extractPropertyField($field->getStructure()->getArrayItem(), $allFields);
        }
    }

    private function extractPropertyField(Field $rootObject, FieldCollection $allFields): void
    {
        foreach ($rootObject->getStructure()->getObjectProperties() as $property) {
            $this->extractField($property, $allFields);
        }
    }
}
