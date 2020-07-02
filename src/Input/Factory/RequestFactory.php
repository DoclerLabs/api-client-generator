<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\RequestBody;
use DoclerLabs\ApiClientGenerator\Entity\Request;
use DoclerLabs\ApiClientGenerator\Entity\RequestFieldRegistry;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;

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
        $collection = new RequestFieldRegistry();
        foreach ($parameters as $parameter) {
            if ($parameter instanceof Reference) {
                $parameter = $parameter->resolve();
            }
            $collection->add(
                $parameter->in,
                $this->fieldFactory->create(
                    $operationName,
                    $parameter->name,
                    $parameter->schema,
                    $parameter->required,
                    ''
                )
            );
        }

        if ($body !== null) {
            foreach ($body->content as $content) {
                if ($content->schema !== null) {
                    $schema     = $content->schema;
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
            }
        }

        return new Request($path, $method, $collection);
    }
}
