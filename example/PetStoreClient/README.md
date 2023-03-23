# Swagger Petstore - OpenAPI 3.0 client

This client is generated using [docler-labs/api-client-generator](https://github.com/DoclerLabs/api-client-generator) based on the OpenAPI specification of the Swagger Petstore - OpenAPI 3.0.

You can generate the client with the following command:
```bash
docker run -it --rm \
-v <local-path-to-api>/doc/openapi.yaml:/openapi.yaml:ro \
-v <local-path-to-client>:/client \
-e NAMESPACE=OpenApi\\PetStoreClient \
-e OPENAPI=/openapi.yaml \
-e OUTPUT_DIR=/client \
-e PACKAGE=openapi/pet-store-client \
-e CLIENT_PHP_VERSION=7.4 \
dhlabs/api-client-generator
```

## Usage

```php
<?php declare(strict_types=1);

use OpenApi\PetStoreClient\SwaggerPetstoreOpenAPI3ClientFactory;
use OpenApi\PetStoreClient\Request\UpdatePetRequest;

/**
 * If using Guzzle 6, make sure to configure Guzzle to not throw exceptions
 * on HTTP error status codes, or this client will violate PSR-18.
 * e.g. new Client(['base_uri' => $baseUri, 'http_errors' => false, ...])
 */
$client = ...; // any PSR-18 HTTP CLIENT

$factory = new SwaggerPetstoreOpenAPI3ClientFactory();
$client  = $factory->create($client);

$request = new UpdatePetRequest();
$result  = $client->updatePet($request);
```

## Operations

### pet
Endpoints:
- **updatePet** - Update an existing pet by Id 
- **addPet** - Add a new pet to the store 
- **findPetsByStatus** - Multiple status values can be provided with comma separated strings 
- **findPetsByTags** - Multiple tags can be provided with comma separated strings. Use tag1, tag2, tag3 for testing. 
- **getPetById** - Returns a single pet 
- **updatePetWithForm**
- **deletePet**

### store
Endpoints:
- **getInventory** - Returns a map of status codes to quantities 
- **placeOrder** - Place a new order in the store 
- **getOrderById** - For valid response try integer IDs with value &lt;= 5 or &gt; 10. Other values will generated exceptions 
- **deleteOrder** - For valid response try integer IDs with value &lt; 1000. Anything above 1000 or nonintegers will generate API errors 

### user
Endpoints:
- **createUser** - This can only be done by the logged in user. 
- **createUsersWithListInput** - Creates list of users with given input array 
- **loginUser**
- **logoutUser**
- **getUserByName**
- **updateUser** - This can only be done by the logged in user. 
- **deleteUser** - This can only be done by the logged in user. 

