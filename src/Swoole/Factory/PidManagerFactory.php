<?php

declare(strict_types=1);

namespace Dot\Swoole\Factory;

use Dot\Swoole\PidManager;
use Psr\Container\ContainerInterface;

use function sys_get_temp_dir;

class PidManagerFactory
{
    public function __invoke(ContainerInterface $container) : PidManager
    {
        $config = $container->get('config');
        return new PidManager(
            $config['dot-swoole']['swoole-server']['options']['pid_file']
                ?? sys_get_temp_dir() . '/dot-swoole.pid'
        );
    }
}
