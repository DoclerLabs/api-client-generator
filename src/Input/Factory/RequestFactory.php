<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\RequestBody;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Entity\RequestFieldRegistry;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use Icecave\Parity\Parity;

class RequestFactory
{
    private FieldFactory $fieldFactory;

    public function __construct(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
    }

    public function create(
        string $operationName,
        string $path,
        string $method,
        array $parameters,
        RequestBody $body = null
    ): Request {
        $contentTypes = [];
        $collection   = new RequestFieldRegistry();
        foreach ($parameters as $parameter) {
            $referenceName = '';
            if ($parameter instanceof Reference) {
                $referenceName = SchemaNaming::getClassName($parameter);
                $parameter     = $parameter->resolve();
            }
            $collection->add(
                $parameter->in,
                $this->fieldFactory->create(
                    $operationName,
                    $parameter->name,
                    $parameter->schema,
                    $parameter->required,
                    $referenceName
                )
            );
        }

        if ($body !== null) {
            $schema = null;
            foreach ($body->content as $contentType => $content) {
                if ($schema !== null && !Parity::isEqualTo($content->schema, $schema)) {
                    throw new InvalidSpecificationException('Multiple schemas per request is not currently supported.');
                }
                $schema         = $content->schema;
                $contentTypes[] = $contentType;
            }

            $schemaName = SchemaNaming::getClassName($schema, ucfirst($operationName) . 'RequestBody');

            $collection->add(
                RequestFieldRegistry::ORIGIN_BODY,
                $this->fieldFactory->create(
                    $operationName,
                    lcfirst($schemaName),
                    $schema,
                    true,
                    $schemaName
                )
            );
        }

        return new Request($path, $method, $collection, $contentTypes);
    }
}
