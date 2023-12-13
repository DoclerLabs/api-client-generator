# Swagger Petstore client

This client is generated using [docler-labs/api-client-generator](https://github.com/DoclerLabs/api-client-generator) based on the OpenAPI specification of the Swagger Petstore.

You can generate the client with the following command:
```bash
docker run -it --rm \
-v <local-path-to-api>/doc/openapi.yaml:/openapi.yaml:ro \
-v <local-path-to-client>:/client \
-e NAMESPACE=Test \
-e OPENAPI=/openapi.yaml \
-e OUTPUT_DIR=/client \
-e PACKAGE=test/test-api-client \
-e CLIENT_PHP_VERSION=7.4 \
dhlabs/api-client-generator:5.6.0
```

## Usage

```php
<?php declare(strict_types=1);

use Test\SwaggerPetstoreClientFactory;
use Test\Request\FindPetsRequest;

/**
 * If using Guzzle 6, make sure to configure Guzzle to not throw exceptions
 * on HTTP error status codes, or this client will violate PSR-18.
 * e.g. new Client(['base_uri' => $baseUri, 'http_errors' => false, ...])
 */
$client = ...; // any PSR-18 HTTP CLIENT

$factory = new SwaggerPetstoreClientFactory();
$client  = $factory->create($client);

$request = new FindPetsRequest();
$result  = $client->findPets($request);
```

## Operations

### Pet
Endpoints:
- **findPets**
- **addPet**
- **countPets**
- **findPetById**

### New
Endpoints:
- **findPets**

### [No tag]
Endpoints:
- **deletePet** - deletes a single pet based on the ID supplied 

