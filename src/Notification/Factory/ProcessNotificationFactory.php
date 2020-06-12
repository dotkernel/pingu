<?php

declare(strict_types=1);

namespace Notification\Factory;

use Notification\Jobs\ProcessNotification;
use Psr\Container\ContainerInterface;

/**
 * Class TeamHandlerFactory
 * @package Workspace\Factory
 */
class ProcessNotificationFactory
{
    /**
     * @param ContainerInterface $container
     * @return ProcessNotification
     */
    public function __invoke(ContainerInterface $container): ProcessNotification
    {
        return new ProcessNotification(
            $container->get('config')['notification']
        );
    }
}
