<?php


namespace Notification\Delegator;

use Notification\Jobs\ProcessRequest;
use Notification\Worker\SwooleWorker;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use QueueJitsu\Job\Job;
use QueueJitsu\Job\JobManager;
use Swoole\Server as Server;
use Mezzio\Swoole\SwooleEmitter;

class SwooleWorkerDelegator
{
    /** @var JobManager $jobManager */
    private $jobManager;

    public function __invoke(ContainerInterface $container, $serviceName, callable $callback): Server
    {
        $server = $callback();
        $logger = $container->get(LoggerInterface::class);

        $this->jobManager = $container->get(JobManager::class);
        $server->on('task', $container->get(SwooleWorker::class));

        $server->on('connect', function ($server, $fd) {
            echo "Client : {$fd}  Connect.\n";
        });
        $server->on('message', function ($server, $frame) {
            echo "received message: {$frame->data}\n";
//            $server->push($frame->fd, json_encode(["hello", "world"]));
        });

        // Register the function for the event `receive`
        $server->on('receive', function ($server, $fd, $from_id, $data) use ($logger) {
            // add raw job here
            $this->jobManager->enqueue(new Job(ProcessRequest::class, 'requests', [$data]));
            $logger->notice("Request added to queue.\n");
        });

        // Register the function for the event `close`
        $server->on('close', function ($server, $fd) {
            echo "Client: {$fd} close.\n";
        });

        $server->on('finish', function ($server, $taskId, $data) use ($logger) {
            $logger->notice('Task #{taskId} has finished processing', ['taskId' => $taskId]);
        });

        return $server;
    }
}
