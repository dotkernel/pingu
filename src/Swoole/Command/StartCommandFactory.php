<?php

declare(strict_types=1);

namespace Dot\Swoole\Command;

use Psr\Container\ContainerInterface;

class StartCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new StartCommand($container);
    }
}
