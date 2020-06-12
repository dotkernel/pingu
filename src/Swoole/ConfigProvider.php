<?php

declare(strict_types=1);

namespace Dot\Swoole;

use Dot\Swoole\PidManager;
use Dot\Swoole\Factory\PidManagerFactory;
use Dot\Swoole\Factory\ServerFactory;
use Swoole\Server as SwooleServer;
use function extension_loaded;

class ConfigProvider
{
    public function __invoke() : array
    {
        $config = PHP_SAPI === 'cli' && extension_loaded('swoole')
            ? ['dependencies' => $this->getDependencies()]
            : [];

        $config['dot-swoole'] = $this->getDefaultConfig();

        return $config;
    }

    public function getDefaultConfig() : array
    {
        return [
            'swoole-server' => [
                'options' => [
                    // We set a default for this. Without one, Swoole\Server
                    // defaults to the value of `ulimit -n`. Unfortunately, in
                    // virtualized or containerized environments, this often
                    // reports higher than the host container allows. 1024 is a
                    // sane default; users should check their host system, however,
                    // and set a production value to match.
                    'max_conn' => 1024,
                ],
                'static-files' => [
                    'enable' => true,
                ],
            ],
        ];
    }

    public function getDependencies() : array
    {
        return [
            'factories'  => [
                Command\ReloadCommand::class           => Command\ReloadCommandFactory::class,
                Command\StartCommand::class            => Command\StartCommandFactory::class,
                Command\StatusCommand::class           => Command\StatusCommandFactory::class,
                Command\StopCommand::class             => Command\StopCommandFactory::class,
                PidManager::class                      => PidManagerFactory::class,
                SwooleServer::class                    => ServerFactory::class,
            ]
        ];
    }
}
