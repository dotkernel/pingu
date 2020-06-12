<?php

declare(strict_types=1);

namespace Dot\Swoole\Command;

use Psr\Container\ContainerInterface;
use Dot\Swoole\PidManager;

class StopCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new StopCommand($container->get(PidManager::class));
    }
}
