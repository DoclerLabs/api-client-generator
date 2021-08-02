<?php

declare(strict_types=1);

require('vendor/autoload.php');

use GuzzleHttp\Client;
use OpenApi\PetStoreClient\Request\DeletePetRequest;
use OpenApi\PetStoreClient\Request\FindPetsByStatusRequest;
use OpenApi\PetStoreClient\Request\FindPetsByTagsRequest;
use OpenApi\PetStoreClient\Request\GetPetByIdRequest;
use OpenApi\PetStoreClient\Request\UpdatePetRequest;
use OpenApi\PetStoreClient\Serializer\ContentType\JsonContentTypeSerializer;
use OpenApi\PetStoreClient\Serializer\ContentType\XmlContentTypeSerializer;
use OpenApi\PetStoreClient\SwaggerPetstoreOpenAPI3ClientFactory;

// https://petstore3.swagger.io/api/v3/openapi.json
$client  = new Client(['base_uri' => 'https://petstore.swagger.io/v2/']);
$factory = new SwaggerPetstoreOpenAPI3ClientFactory();
$client  = $factory->create($client);

$request = new FindPetsByStatusRequest();
$request->setStatus('sold');
$result = $client->findPetsByStatus($request);
if ($result === null || $result->count() === 0) {
    sprintf('findPetsByStatus failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
}
$firstPet = $result->first();

$request = new GetPetByIdRequest($firstPet->getId());
$result  = $client->getPetById($request);
if ($result === null) {
    sprintf('getPetById failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
}

$request = new UpdatePetRequest(
    $firstPet,
    XmlContentTypeSerializer::MIME_TYPE
);
$result  = $client->updatePet($request);
if ($result === null) {
    sprintf('getPetById failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
}

$request = new UpdatePetRequest(
    $firstPet,
    JsonContentTypeSerializer::MIME_TYPE
);
$result  = $client->updatePet($request);
if ($result === null) {
    sprintf('getPetById failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
}

$request = new DeletePetRequest($firstPet->getId());
$client->deletePet($request);

$request = new FindPetsByTagsRequest();
$request->setTags(['string']);
$result = $client->findPetsByTags($request);
if ($result === null || $result->count() === 0) {
    sprintf('findPetsByTags failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
}
