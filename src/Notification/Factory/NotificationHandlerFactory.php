<?php

declare(strict_types=1);

namespace Notification\Factory;

use Notification\Handler\NotificationHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function get_class;

class NotificationHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {

        return new NotificationHandler($container->get('config')['mail'], $container->get('config')['application']);
    }
}
