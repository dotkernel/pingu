<?php

declare(strict_types=1);

namespace Notification;

use Notification\Factory\NotificationHandlerFactory;
use Notification\Factory\ProcessEmailFactory;
use Notification\Factory\ProcessNotificationFactory;
use Notification\Factory\ProcessRequestFactory;
use Notification\Factory\QueueLoggerAdapterFactory;
use Notification\Factory\RedisAdapterFactory;
use Notification\Factory\SwooleWorkerFactory;
use Notification\Handler\NotificationHandler;
use Notification\Jobs\ProcessEmail;
use Notification\Jobs\ProcessNotification;
use Notification\Jobs\ProcessRequest;
use Notification\Worker\SwooleWorker;
use Swoole\Server as SwooleServer;
use Psr\Log\LoggerInterface;
use Notification\Delegator\SwooleWorkerDelegator;
use Laminas\Log\PsrLoggerAdapter;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'delegators' => [
                SwooleServer::class => [
                    SwooleWorkerDelegator::class
                ]
            ],
            'factories' => [
                PsrLoggerAdapter::class => QueueLoggerAdapterFactory::class,
                SwooleWorker::class => SwooleWorkerFactory::class,
                NotificationHandler::class => NotificationHandlerFactory::class,
                ProcessEmail::class => ProcessEmailFactory::class,
                ProcessNotification::class => ProcessNotificationFactory::class,
                ProcessRequest::class => ProcessRequestFactory::class,
                \QueueJitsu\Job\Adapter\RedisAdapter::class => \QueueJitsu\Cli\Job\RedisAdapterFactory::class,
                \QueueJitsu\Queue\Adapter\RedisAdapter::class => \QueueJitsu\Cli\Queue\RedisAdapterFactory::class,
                \QueueJitsu\Worker\Adapter\RedisAdapter::class => \QueueJitsu\Cli\Worker\RedisAdapterFactory::class,
                \QueueJitsu\Scheduler\Worker\Worker::class =>  \QueueJitsu\Scheduler\Worker\WorkerFactory::class,
                \Notification\Adapter\RedisAdapter::class => RedisAdapterFactory::class,
            ],
            'aliases' => [
                LoggerInterface::class => PsrLoggerAdapter::class,
                \QueueJitsu\Job\Adapter\AdapterInterface::class => \QueueJitsu\Job\Adapter\RedisAdapter::class,
                \QueueJitsu\Queue\Adapter\AdapterInterface::class => \QueueJitsu\Queue\Adapter\RedisAdapter::class,
                \QueueJitsu\Worker\Adapter\AdapterInterface::class => \QueueJitsu\Worker\Adapter\RedisAdapter::class,
                \QueueJitsu\Scheduler\Adapter\AdapterInterface::class => \Notification\Adapter\RedisAdapter::class
            ]
        ];
    }

    /**
     * @return array
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'layout' => [__DIR__ . '/../../templates/layout'],
                'notification-email' => [__DIR__ . '/../../templates/notification/email'],
                'error' => [__DIR__ . '/../../templates/error'],
            ],
        ];
    }
}
