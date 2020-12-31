<?php declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace OpenApi\PetStoreClient\Request;

use OpenApi\PetStoreClient\Schema\SerializableInterface;

class DeletePetRequest implements RequestInterface
{
    /** @var string|null */
    private $apiKey;

    /** @var int */
    private $petId;

    /** @var string */
    private $contentType = '';

    /**
     * @param int $petId
     */
    public function __construct(int $petId)
    {
        $this->petId = $petId;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $apiKey
     *
     * @return self
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'DELETE';
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return \strtr('pet/{petId}', ['{petId}' => $this->petId]);
    }

    /**
     * @return array
     */
    public function getQueryParameters(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getRawQueryParameters(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getCookies(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return \array_merge([], \array_map(static function ($value) {
            return $value instanceof SerializableInterface ? $value->toArray() : $value;
        }, \array_filter(['api_key' => $this->apiKey], static function ($value) {
            return null !== $value;
        })));
    }

    public function getBody()
    {
        return null;
    }
}