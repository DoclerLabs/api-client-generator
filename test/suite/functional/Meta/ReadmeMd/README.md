# Swagger Petstore client

This client is generated using [docler-labs/api-client-generator](https://github.com/DoclerLabs/api-client-generator) based on the OpenAPI specification of the Swagger Petstore.

You can generate the client with the following command:
```bash
docker run -it \
-v <local-path-to-api>/doc/openapi.yaml:/openapi.yaml:ro \
-v <local-path-to-client>:/client \
-e NAMESPACE=Test\\PetstoreApi \
-e OPENAPI=/openapi.yaml \
-e OUTPUT_DIR=/client \
-e PACKAGE=test/petstore-api \
-e CLIENT_PHP_VERSION=7.2 \
dhlabs/api-client-generator
```

## Usage

```php
<?php declare(strict_types=1);

use \Test\PetstoreApi\SwaggerPetstoreClientFactory;
use \Test\PetstoreApi\Request\FindPetsRequest;

$factory = new SwaggerPetstoreClientFactory();
$client  = $factory->create('http://petstore.swagger.io/api', ['timeout' => 2]);

$request = new FindPetsRequest();
$result  = $client->findPets($request);
```

## Operations

### [No tag]
Endpoints:
- **findPets**
- **addPet**
- **findPetById**
- **deletePet** - deletes a single pet based on the ID supplied 

