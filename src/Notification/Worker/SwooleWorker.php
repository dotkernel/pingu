<?php


namespace Notification\Worker;

use Psr\Log\LoggerInterface;
use Throwable;

class SwooleWorker
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke($server, $taskId, $fromId, $data)
    {
//        if (!$data instanceof Array_) {
//            $this->logger->error('Invalid data type provided to task worker: {type}', [
//                'type' => is_object($data) ? get_class($data) : gettype($data)
//            ]);
//            return;
//        }

        $this->logger->notice('Starting work on task {taskId} using data: {data}', [
            'taskId' => $taskId,
            'data' => json_encode($data),
        ]);

        try {
            if ($this->redis->hasItem($data['taskKey'])) {
                $this->logger->notice('Received success taskId {taskId}, redisValue {redisKey} using data: {data}', [
                    'taskId' => $taskId,
                    'redisKey' => $this->redis->getItem($data['taskKey']),
                    'data' => json_encode($data),
                ]);
            } else {
                $this->redis->setItem($data['taskKey'], $data['taskValue']);
                $this->logger->notice('Insert success taskId {taskId}, redisValue {redisKey} using data: {data}', [
                    'taskId' => $taskId,
                    'redisKey' => $data['taskValue'],
                    'data' => json_encode($data),
                ]);
            }
        } catch (Throwable $e) {
            $this->logger->error('Error processing task {taskId}: {error}', [
                'taskId' => $taskId,
                'error' => $e->getTraceAsString(),
            ]);
        }

        // Notify the server that processing of the task has finished:
        $server->finish('');
    }
}
