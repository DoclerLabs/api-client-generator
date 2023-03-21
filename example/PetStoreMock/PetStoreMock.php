<?php

namespace OpenApi\PetStoreMock;

use GuzzleHttp\Client;

class PetStoreMock
{
    private const PET_RESPONSE = [
        'id'        => 2972962088,
        'category'  => [
            'id'   => 1085449728,
            'name' => 'dog',
        ],
        'name'      => 'hello kity',
        'photoUrls' => [
            'http://foo.bar.com/1',
            'http://foo.bar.com/2',
        ],
        'tags'      => [
            [
                'id' => 2291630681,
            ],
        ],
        'status'    => 'sold',
    ];

    private Client $wiremockClient;

    public function __construct()
    {
        $this->wiremockClient = new Client(['base_uri' => 'http://wiremock:8080']);
    }

    public function mockFindPetsByStatus(): void
    {
        $this->mock(
            'GET',
            '/pet/findByStatus.*',
            [self::PET_RESPONSE],
            [
                'status' => ['equalTo' => 'sold'],
            ]
        );
    }

    public function mockGetPetById(): void
    {
        $this->mock(
            'GET',
            '/pet/' . self::PET_RESPONSE['id'],
            self::PET_RESPONSE
        );
    }

    public function mockDeletePet(): void
    {
        $this->mock(
            'DELETE',
            '/pet/2972962088'
        );
    }

    public function mockUpdatePet(): void
    {
        $this->mock(
            'PUT',
            '/pet',
            self::PET_RESPONSE
        );
    }

    private function mock(
        string $requestMethod,
        string $urlMatcher,
        $responseBody = null,
        array $requestQueryParameters = [],
        ?int $responseCode = 200
    ): void {
        $this->wiremockClient->post(
            '/__admin/mappings',
            [
                'body' => json_encode(
                    [
                        'request'  => array_filter(
                            [
                                'method'          => $requestMethod,
                                'urlPathPattern'  => $urlMatcher,
                                'queryParameters' => $requestQueryParameters,
                            ]
                        ),
                        'response' => array_filter(
                            [
                                'status'   => $responseCode,
                                'jsonBody' => $responseBody,
                                'headers'  => [
                                    'Content-Type' => 'application/json',
                                ],
                            ]
                        ),
                    ]
                ),
            ]
        );
    }
}
