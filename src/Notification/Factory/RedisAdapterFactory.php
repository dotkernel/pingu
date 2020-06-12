<?php

declare(strict_types=1);

namespace Notification\Factory;

use Notification\Adapter\RedisAdapter;
use Predis\Client;
use Psr\Container\ContainerInterface;

/**
 * Class RedisAdapterFactory
 * @package Notification\Factory
 */
class RedisAdapterFactory
{
    /**
     * @param ContainerInterface $container
     * @return RedisAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $redis = $container->get(Client::class);

        return new RedisAdapter($redis);
    }
}
