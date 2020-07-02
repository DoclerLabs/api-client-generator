<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use cebe\openapi\spec\OpenApi;
use DoclerLabs\ApiClientGenerator\Entity\FieldCollection;
use DoclerLabs\ApiClientGenerator\Entity\OperationCollection;
use DoclerLabs\ApiClientGenerator\Entity\ResponseExceptionCollection;

class Specification
{
    private OpenApi                     $openApi;
    private OperationCollection         $operations;
    private FieldCollection             $compositeRequestFields;
    private FieldCollection             $compositeResponseFields;
    private ResponseExceptionCollection $responseErrorTypes;

    public function __construct(
        OpenApi $openApi,
        OperationCollection $operations,
        FieldCollection $compositeRequestFields,
        FieldCollection $compositeResponseFields,
        ResponseExceptionCollection $responseErrorTypes
    ) {
        $this->openApi                 = $openApi;
        $this->operations              = $operations;
        $this->compositeRequestFields  = $compositeRequestFields;
        $this->compositeResponseFields = $compositeResponseFields;
        $this->responseErrorTypes      = $responseErrorTypes;
    }

    public function getTitle(): string
    {
        return $this->openApi->info->title;
    }

    public function getDescription(): string
    {
        return $this->openApi->info->description ?? '';
    }

    public function getOperations(): OperationCollection
    {
        return $this->operations;
    }

    public function getCompositeFields(): FieldCollection
    {
        return $this->getCompositeRequestFields()->merge($this->getCompositeResponseFields());
    }

    public function getCompositeRequestFields(): FieldCollection
    {
        return $this->compositeRequestFields;
    }

    public function getCompositeResponseFields(): FieldCollection
    {
        return $this->compositeResponseFields;
    }

    public function getResponseErrorTypes(): ResponseExceptionCollection
    {
        return $this->responseErrorTypes;
    }
}
