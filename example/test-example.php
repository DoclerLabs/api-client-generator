<?php

declare(strict_types=1);

require('vendor/autoload.php');

use GuzzleHttp\Client;
use OpenApi\PetStoreClient\Request\DeletePetRequest;
use OpenApi\PetStoreClient\Request\FindPetsByStatusRequest;
use OpenApi\PetStoreClient\Request\GetPetByIdRequest;
use OpenApi\PetStoreClient\Request\UpdatePetRequest;
use OpenApi\PetStoreClient\Schema\Pet;
use OpenApi\PetStoreClient\Serializer\ContentType\JsonContentTypeSerializer;
use OpenApi\PetStoreClient\Serializer\ContentType\XmlContentTypeSerializer;
use OpenApi\PetStoreClient\SwaggerPetstoreOpenAPI3ClientFactory;

class VerifyClientTest
{
    private const PET_RESPONSE = [
        'id'        => 2972962088,
        'category'  => [
            'id'   => 1085449728,
            'name' => 'dog'
        ],
        'name'      => 'hello kity',
        'photoUrls' => [
            'http://foo.bar.com/1',
            'http://foo.bar.com/2',
        ],
        'tags' => [
            [
                'id' => 2291630681,
            ]
        ],
        'status'    => 'sold',
    ];

    private Client $wiremockClient;

    private $petClient;

    public function __construct()
    {
        $this->wiremockClient = new Client(['base_uri' => 'http://wiremock:8080']);
        $this->petClient      = (new SwaggerPetstoreOpenAPI3ClientFactory())
            ->create(new Client(['base_uri' => 'http://pet.wiremock:8080']));
    }

    public function findPetsByStatus(): Pet
    {
        $this->mockFindPetsByStatus();

        $request = new FindPetsByStatusRequest();
        $request->setStatus('sold');
        $result = $this->petClient->findPetsByStatus($request);
        if ($result === null || $result->count() === 0) {
            throw new Exception('findPetsByStatus failed');
        }

        return $result->first();
    }

    public function getPetById(int $petId): Pet
    {
        $this->mockGetPetById();

        $request = new GetPetByIdRequest($petId);
        $result  = $this->petClient->getPetById($request);
        if ($result === null) {
            printf('getPetById failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
        }

        return $result;
    }

    public function updatePet(Pet $pet, string $mimeType): void
    {
        $this->mockUpdatePet();

        $request = new UpdatePetRequest($pet, $mimeType);
        $result  = $this->petClient->updatePet($request);
        if ($result === null) {
            printf('getPetById failed, result: %s', json_encode($result, JSON_THROW_ON_ERROR)) || exit(1);
        }
    }

    public function deletePet(int $petId): void
    {
        $this->mockDeletePet();

        $request = new DeletePetRequest($petId);
        $this->petClient->deletePet($request);
    }

    private function mockDeletePet(): void
    {
        $this->mock(
            $this->wiremockClient,
            'DELETE',
            '/pet/2972962088'
        );
    }

    private function mockUpdatePet(): void
    {
        $this->mock(
            $this->wiremockClient,
            'PUT',
            '/pet',
            self::PET_RESPONSE
        );
    }

    private function mockGetPetById(): void
    {
        $this->mock(
            $this->wiremockClient,
            'GET',
            '/pet/2972962088',
            self::PET_RESPONSE
        );
    }

    private function mockFindPetsByStatus(): void
    {
        $this->mock(
            $this->wiremockClient,
            'GET',
            '/pet/findByStatus.*',
            [self::PET_RESPONSE],
            [
                'status' => ['equalTo' => 'sold'],
            ]
        );
    }

    private function mock(
        Client $wiremockClient,
        string $requestMethod,
        string $urlMatcher,
        $responseBody = null,
        array $requestQueryParameters = [],
        ?int $responseCode = 200
    ): void {
        $wiremockClient->post(
            '/__admin/mappings',
            [
                'body' => json_encode([
                    'request'  => array_filter([
                        'method'          => $requestMethod,
                        'urlPathPattern'  => $urlMatcher,
                        'queryParameters' => $requestQueryParameters,
                    ]),
                    'response' => array_filter([
                        'status'   => $responseCode,
                        'jsonBody' => $responseBody,
                        'headers'  => [
                            'Content-Type' => 'application/json',
                        ],
                    ])
                ])
            ]
        );
    }
}

$test     = new VerifyClientTest();
$firstPet = $test->findPetsByStatus();
$pet      = $test->getPetById($firstPet->getId());
$test->updatePet($pet, XmlContentTypeSerializer::MIME_TYPE);
$test->updatePet($pet, JsonContentTypeSerializer::MIME_TYPE);
$test->deletePet($pet->getId());
