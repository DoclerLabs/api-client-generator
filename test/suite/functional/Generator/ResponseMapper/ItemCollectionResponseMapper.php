<?php

namespace Test\Response\Mapper;

use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Mapper\ResponseMapperInterface;
use DoclerLabs\ApiClientGenerator\Output\StaticPhp\Response\Response;
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