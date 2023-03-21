<?php

declare(strict_types=1);

require('vendor/autoload.php');

use OpenApi\PetStoreApiConsumer\PetStoreApiConsumer;
use OpenApi\PetStoreClient\Serializer\ContentType\JsonContentTypeSerializer;
use OpenApi\PetStoreClient\Serializer\ContentType\XmlContentTypeSerializer;
use OpenApi\PetStoreMock\PetStoreMock;

$mock = new PetStoreMock();
$consumer = new PetStoreApiConsumer();

$mock->mockFindPetsByStatus();
$firstPet = $consumer->findPetsByStatus();

$mock->mockGetPetById();
$pet = $consumer->getPetById($firstPet->getId());

$mock->mockUpdatePet();
$consumer->updatePet($pet, XmlContentTypeSerializer::MIME_TYPE);

$consumer->updatePet($pet, JsonContentTypeSerializer::MIME_TYPE);

$mock->mockDeletePet();
$consumer->deletePet($pet->getId());
