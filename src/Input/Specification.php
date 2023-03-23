<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\SecurityScheme;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\ConstraintInterface;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MaxLengthConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Constraint\MinLengthConstraint;
use DoclerLabs\ApiClientGenerator\Entity\Field;
use DoclerLabs\ApiClientGenerator\Entity\FieldCollection;
use DoclerLabs\ApiClientGenerator\Entity\Operation;
use DoclerLabs\ApiClientGenerator\Entity\OperationCollection;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\FormUrlencodedContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\JsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\VdnApiJsonContentTypeSerializer;
use DoclerLabs\ApiClientGenerator\Output\Copy\Serializer\ContentType\XmlContentTypeSerializer;

class Specification
{
    private OpenApi                     $openApi;
    private OperationCollection         $operations;
    private FieldCollection             $compositeRequestFields;
    private FieldCollection             $compositeResponseFields;

    public function __construct(
        OpenApi $openApi,
        OperationCollection $operations,
        FieldCollection $compositeRequestFields,
        FieldCollection $compositeResponseFields
    ) {
        $this->openApi                 = $openApi;
        $this->operations              = $operations;
        $this->compositeRequestFields  = $compositeRequestFields;
        $this->compositeResponseFields = $compositeResponseFields;
    }

    public function getTitle(): string
    {
        return $this->openApi->info->title;
    }

    public function hasLicense(): bool
    {
        $license = $this->openApi->info->license;

        return $license && $license->name;
    }

    public function getLicenseName(): string
    {
        return $this->openApi->info->license->name;
    }

    public function getServerUrls(): array
    {
        $serverUrls = [];
        foreach ($this->openApi->servers as $server) {
            $serverUrls[] = $server->url;
        }

        return $serverUrls;
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

    /**
     * @return SecurityScheme[]
     */
    public function getSecuritySchemes(): array
    {
        return array_map(static function ($securityScheme): SecurityScheme {
            if ($securityScheme instanceof Reference) {
                $securityScheme = $securityScheme->resolve();
            }

            return $securityScheme;
        }, $this->openApi->components->securitySchemes ?? []);
    }

    public function getAllContentTypes(): array
    {
        $allContentTypes = [
            XmlContentTypeSerializer::MIME_TYPE            => false,
            FormUrlencodedContentTypeSerializer::MIME_TYPE => false,
            JsonContentTypeSerializer::MIME_TYPE           => false,
            VdnApiJsonContentTypeSerializer::MIME_TYPE     => false,
        ];

        /** @var Operation $operation */
        foreach ($this->getOperations() as $operation) {
            foreach ($operation->getRequest()->getBodyContentTypes() as $contentType) {
                $allContentTypes[$contentType] = true;
            }
            foreach ($operation->getSuccessfulResponse()->getBodyContentTypes() as $contentType) {
                $allContentTypes[$contentType] = true;
            }
        }

        return array_keys(array_filter($allContentTypes));
    }

    public function requiresIntlExtension(): bool
    {
        foreach ($this->getOperations() as $operation) {
            foreach ($operation->getRequest()->getFields() as $field) {
                if ($this->fieldRequiresIntlExtension($field)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isSecuritySchemeEnabled(string $scheme): bool
    {
        if (empty($this->getSecuritySchemes())) {
            return false;
        }

        foreach ($this->getSecuritySchemes() as $securityScheme) {
            if ($securityScheme->scheme === $scheme) {
                return true;
            }
        }

        return false;
    }

    private function fieldRequiresIntlExtension(Field $field): bool
    {
        /** @var ConstraintInterface $constraint */
        foreach ($field->getConstraints() as $constraint) {
            if (
                (
                    $constraint instanceof MinLengthConstraint
                    || $constraint instanceof MaxLengthConstraint
                )
                && $constraint->exists()
            ) {
                return true;
            }
        }

        if (
            $field->isObject()
            && !empty($field->getObjectProperties())
        ) {
            foreach ($field->getObjectProperties() as $objectProperty) {
                if ($this->fieldRequiresIntlExtension($objectProperty)) {
                    return true;
                }
            }
        }

        return false;
    }
}
