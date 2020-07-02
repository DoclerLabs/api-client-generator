<?php declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Reference;
use DoclerLabs\ApiClientGenerator\Entity\Response;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;

class ResponseFactory
{
    private FieldFactory $fieldMapper;

    public function __construct(FieldFactory $fieldMapper)
    {
        $this->fieldMapper = $fieldMapper;
    }

    public function createSuccessful(string $operationName, array $openApiResponses): Response
    {
        $body = null;
        foreach ($openApiResponses as $code => $response) {
            if ($response instanceof Reference) {
                $response = $response->resolve();
            }

            if ($code === 204) {
                return new Response($code, null);
            }

            if (200 <= $code && $code < 300) {
                $content = current($response->content);
                if ($content === false) {
                    return new Response($code, null);
                }

                $schema     = $content->schema;
                $schemaName = SchemaNaming::getClassName($schema, ucfirst($operationName) . 'ResponseBody');

                $body = $this->fieldMapper->create(
                    $operationName,
                    lcfirst($schemaName),
                    $schema,
                    true,
                    $schemaName
                );

                return new Response($code, $body);
            }
        }

        throw new InvalidSpecificationException(
            sprintf('Successful response is not found for %s operation.', $operationName)
        );
    }

    public function createPossibleErrors(array $openApiResponses): array
    {
        $responses = [];
        foreach ($openApiResponses as $code => $response) {
            if ($code > 399) {
                $responses[] = new Response($code, null);
            }
        }

        return $responses;
    }
}
