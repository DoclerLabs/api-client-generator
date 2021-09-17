<?php

declare(strict_types=1);

namespace DoclerLabs\ApiClientGenerator\Input\Factory;

use cebe\openapi\spec\Reference;
use DoclerLabs\ApiClientGenerator\Entity\Response;
use DoclerLabs\ApiClientGenerator\Input\InvalidSpecificationException;
use DoclerLabs\ApiClientGenerator\Naming\SchemaNaming;
use Icecave\Parity\Parity;

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
                return new Response(204, null);
            }

            if (200 <= $code && $code < 300) {
                if (empty($response->content) || current($response->content) === false) {
                    return new Response((int)$code, null);
                }

                $contentTypes = [];
                $schema       = null;
                foreach ($response->content as $contentType => $content) {
                    if ($schema !== null && !Parity::isEqualTo($content->schema, $schema)) {
                        throw new InvalidSpecificationException('Multiple schemas per response is not currently supported.');
                    }
                    $schema         = $content->schema;
                    $contentTypes[] = $contentType;
                }

                $schemaName = SchemaNaming::getClassName($schema, ucfirst($operationName) . 'ResponseBody');

                $body = $this->fieldMapper->create(
                    $operationName,
                    lcfirst($schemaName),
                    $schema,
                    true,
                    $schemaName
                );

                return new Response((int)$code, $body, $contentTypes);
            }
        }

        $warningMessage = sprintf(
            'Successful response is not found for %s operation, 200 response assumed.',
            $operationName
        );
        trigger_error($warningMessage, E_USER_WARNING);

        return new Response(200, null);
    }

    public function createPossibleErrors(array $openApiResponses): array
    {
        $responses = [];
        foreach ($openApiResponses as $code => $response) {
            if ($code > 399) {
                $responses[] = new Response((int)$code, null);
            }
        }

        return $responses;
    }
}
