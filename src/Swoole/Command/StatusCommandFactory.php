<?php

declare(strict_types=1);

namespace Dot\Swoole\Command;

use Psr\Container\ContainerInterface;
use Dot\Swoole\PidManager;

class StatusCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new StatusCommand($container->get(PidManager::class));
    }
}
