<?php

declare(strict_types=1);

namespace Notification\Factory;

use Psr\Container\ContainerInterface;
use Laminas\Log\PsrLoggerAdapter;

/**
 * Class QueueLoggerAdapterFactory
 * @package Notification\Factory
 */
class QueueLoggerAdapterFactory
{
    /**
     * @param ContainerInterface $container
     * @return PsrLoggerAdapter
     */
    public function __invoke(ContainerInterface $container): PsrLoggerAdapter
    {
        return new PsrLoggerAdapter(
            $container->get('queue_log')
        );
    }
}
