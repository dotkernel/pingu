<?php

declare(strict_types=1);

namespace Dot\Swoole\Command;

use Psr\Container\ContainerInterface;

use const SWOOLE_BASE;

class ReloadCommandFactory
{
    public function __invoke(ContainerInterface $container) : ReloadCommand
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $mode   = $config['dot-swoole']['swoole-server']['mode'] ?? SWOOLE_BASE;

        return new ReloadCommand($mode);
    }
}
