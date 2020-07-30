<?php

namespace Test\Response\Mapper;

use DoclerLabs\ApiClientBase\Response\Response;
use DoclerLabs\ApiClientBase\Response\Mapper\ResponseMapperInterface;
use Test\Schema\ItemCollection;
class ItemCollectionResponseMapper implements ResponseMapperInterface
{
    /** @var ItemResponseMapper */
    private $itemResponseMapper;
    /**
     * @param ItemResponseMapper $itemResponseMapper
    */
    public function __construct(ItemResponseMapper $itemResponseMapper)
    {
        $this->itemResponseMapper = $itemResponseMapper;
    }
    /**
     * @param Response $response
     * @return ItemCollection
    */
    public function map(Response $response) : ItemCollection
    {
        $payload = $response->getPayload();
        $items = array();
        foreach ($payload as $payloadItem) {
            $items[] = $this->itemResponseMapper->map(new Response($response->getStatusCode(), $payloadItem));
        }
        return new ItemCollection(...$items);
    }
}