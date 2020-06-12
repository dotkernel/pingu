<?php

declare(strict_types=1);

namespace Notification\Factory;

use Notification\Jobs\ProcessRequest;
use Psr\Container\ContainerInterface;
use QueueJitsu\Job\JobManager;
use QueueJitsu\Scheduler\Scheduler;

/**
 * Class TeamHandlerFactory
 * @package Workspace\Factory
 */
class ProcessRequestFactory
{
    /**
     * @param ContainerInterface $container
     * @return ProcessRequest
     */
    public function __invoke(ContainerInterface $container): ProcessRequest
    {
        return new ProcessRequest(
            $container->get(JobManager::class),
            $container->get(Scheduler::class)
        );
    }
}
