<?php

namespace Test\Response\Mapper;

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
     * @param array $response
     * @return ItemCollection
    */
    public function map(array $response) : ItemCollection
    {
        $items = array();
        foreach ($response as $responseItem) {
            $items[] = $this->itemResponseMapper->map($responseItem);
        }
        return new ItemCollection(...$items);
    }
}