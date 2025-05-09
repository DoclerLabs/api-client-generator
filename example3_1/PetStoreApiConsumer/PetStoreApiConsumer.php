<?php

declare(strict_types=1);

namespace OpenApi\PetStoreApiConsumer;

use GuzzleHttp\Client;
use OpenApi\PetStoreClient\Request\DeletePetRequest;
use OpenApi\PetStoreClient\Request\FindPetsByStatusRequest;
use OpenApi\PetStoreClient\Request\GetPetByIdRequest;
use OpenApi\PetStoreClient\Request\UpdatePetRequest;
use OpenApi\PetStoreClient\Schema\Pet;
use OpenApi\PetStoreClient\SwaggerPetstoreOpenAPI31ClientFactory;
use UnexpectedValueException;

class PetStoreApiConsumer
{
    private $petClient;

    public function __construct()
    {
        $this->petClient = (new SwaggerPetstoreOpenAPI31ClientFactory())
            ->create(new Client(['base_uri' => 'http://pet.wiremock:8080']));
    }

    public function findPetsByStatus(): Pet
    {
        $request = new FindPetsByStatusRequest();
        $request->setStatus('sold');
        $result = $this->petClient->findPetsByStatus($request);
        if ($result === null || $result->count() === 0) {
            throw new UnexpectedValueException('findPetsByStatus should be not null or empty');
        }

        return $result->first();
    }

    public function getPetById(int $petId, string $apiKey): Pet
    {
        $request = new GetPetByIdRequest($petId, $apiKey);
        $result  = $this->petClient->getPetById($request);
        if ($result === null) {
            throw new UnexpectedValueException('getPetById should not be null');
        }

        return $result;
    }

    public function updatePet(Pet $pet, string $mimeType): void
    {
        $request = new UpdatePetRequest($pet, $mimeType);
        $result  = $this->petClient->updatePet($request);
        if ($result === null) {
            printf('getPetById failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
        }
    }

    public function deletePet(int $petId): void
    {
        $request = new DeletePetRequest($petId);
        $this->petClient->deletePet($request);
    }
}
