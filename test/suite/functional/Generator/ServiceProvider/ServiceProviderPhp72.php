<?php declare(strict_types=1);

/*
 * This file was generated by docler-labs/api-client-generator.
 *
 * Do not edit it manually.
 */

namespace Test;

use Pimple\Container;
use Test\Response\Mapper\FoodResponseMapper;
use Test\Response\Mapper\PetCollectionResponseMapper;
use Test\Response\Mapper\PetResponseMapper;

class ServiceProvider
{
    public function register(Container $container)
    {
        $this->registerResponseMappers($container);
    }

    /**
     * @param Container $container
     */
    private function registerResponseMappers(Container $container): void
    {
        $container[PetCollectionResponseMapper::class] = static function () use ($container): PetCollectionResponseMapper {
            return new PetCollectionResponseMapper($container[PetResponseMapper::class]);
        };
        $container[PetResponseMapper::class] = static function () use ($container): PetResponseMapper {
            return new PetResponseMapper($container[FoodResponseMapper::class]);
        };
        $container[FoodResponseMapper::class] = static function () use ($container): FoodResponseMapper {
            return new FoodResponseMapper();
        };
    }
}