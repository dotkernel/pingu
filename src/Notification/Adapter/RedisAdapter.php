<?php

declare(strict_types=1);

namespace Notification\Adapter;

use Cron\CronExpression;
use Predis\Client;
use QueueJitsu\Job\Job;
use QueueJitsu\Scheduler\Adapter\AdapterInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class RedisAdapter
 * @package Notification\Adapter
 */
class RedisAdapter implements AdapterInterface
{
    const AT_QUEUE_NAME = '_schdlr_at_';
    const CRON_QUEUE_NAME = '_schdlr_cron_';

    /**
     * @var \Predis\Client $client
     */
    private $client;

    /**
     * RedisAdapter constructor.
     *
     * @param \Predis\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * enqueueAt
     *
     * @param int $at
     * @param Job $job
     */
    public function enqueueAt(int $at, Job $job): void
    {
        $data = $job->getPayload();

        $data['s_time'] = time();

        $key = sprintf('%s:%s', self::AT_QUEUE_NAME, $at);
        $this->client->rpush($key, [json_encode($data)]);
        $this->client->zadd(self::AT_QUEUE_NAME, [$at => $at]);
    }

    /**
     * enqueueCron
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param string $cron
     * @param Job $job
     *
     * @throws \RuntimeException
     */
    public function enqueueCron(string $cron, Job $job): void
    {
        $data = ['cron' => $cron, 'job' => $job->getPayload()];

        $id = Uuid::uuid4()->toString();

        $key = sprintf('%s:%s', self::CRON_QUEUE_NAME, $id);

        $this->client->set($key, json_encode($data));

        $this->updateCron($id, $cron);
    }

    /**
     * getNextJob
     *
     * @throws \RuntimeException
     *
     * @return null|Job
     */
    public function getNextJob(): ?Job
    {
        if ($this->hasAtJobsToProcess() && $this->hasCronJobsToProcess()) {
            return $this->findNextJob();
        }

        if ($this->hasAtJobsToProcess()) {
            return $this->getNextAtJob();
        }

        return $this->getNextCronJob();
    }

    /**
     * getNextAtTimestamp
     *
     * @return int|null
     */
    protected function getNextAtTimestamp(): ?int
    {
        $at = time();

        $items = $this->client->zrangebyscore(self::AT_QUEUE_NAME, '-inf', $at, ['limit', 0, 1]);
        if (empty($items)) {
            return null;
        }

        return (int)$items[0];
    }

    /**
     * findNextJob
     *
     * @throws \RuntimeException
     *
     * @return null|Job
     */
    protected function findNextJob(): ?Job
    {
        $next_at_timestamp = $this->getNextAtTimestamp();
        $cron_id = $this->getNextCronId();

        if (is_null($cron_id)) {
            return $this->getNextAtJob();
        }

        $next_cron_timestamp = $this->getCronTimestamp($cron_id);

        if ($next_at_timestamp <= $next_cron_timestamp) {
            return $this->getNextAtJob();
        }

        return $this->getNextCronJob();
    }

    /**
     * getNextAtJob
     *
     * @return null|Job
     */
    protected function getNextAtJob(): ?Job
    {
        $next_timestamp = $this->getNextAtTimestamp();

        if (!is_null($next_timestamp)) {
            return $this->getNextJobAtTimestamp($next_timestamp);
        }

        return null;
    }

    /**
     * hasAtJobsToProcess
     *
     * @return bool
     */
    private function hasAtJobsToProcess(): bool
    {
        return !is_null($this->getNextAtTimestamp());
    }

    /**
     * hasCronJobsToProcess
     *
     * @return bool
     */
    private function hasCronJobsToProcess(): bool
    {
        return !is_null($this->getNextCronId());
    }

    /**
     * getNextCronId
     */
    private function getNextCronId()
    {
        $at = time();

        $items = $this->client->zrangebyscore(self::CRON_QUEUE_NAME, '-inf', $at, ['limit', 0, 1]);

        if (empty($items)) {
            return;
        }

        return $items[0];
    }

    /**
     * getNextJobAtTimestamp
     *
     * @param int $timestamp
     *
     * @return Job
     */
    private function getNextJobAtTimestamp(int $timestamp): Job
    {
        $key = sprintf('%s:%s', self::AT_QUEUE_NAME, $timestamp);

        $item = json_decode($this->client->lpop($key), true);

        $this->cleanupTimestamp($timestamp);

        return new Job($item['class'], $item['queue'], $item['args'], $item['id']);
    }

    /**
     * cleanupTimestamp
     *
     * @param int $timestamp
     */
    private function cleanupTimestamp(int $timestamp): void
    {
        $key = sprintf('%s:%s', self::AT_QUEUE_NAME, $timestamp);

        if (!$this->client->llen($key)) {
            $this->client->del([$key]);
            $this->client->zrem(self::AT_QUEUE_NAME, $timestamp);
        }
    }

    /**
     * getCronTimestamp
     *
     * @param string $cron_id
     *
     * @return int|null
     */
    private function getCronTimestamp(string $cron_id): ?int
    {
        $items = $this->client->zscore(self::CRON_QUEUE_NAME, $cron_id);

        if (empty($items)) {
            return null;
        }

        return (int)$items[0];
    }

    /**
     * getNextCronJob
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @throws \RuntimeException
     *
     * @return null|Job
     */
    private function getNextCronJob(): ?Job
    {
        $cron_id = $this->getNextCronId();

        if (is_null($cron_id)) {
            return null;
        }

        $key = sprintf('%s:%s', self::CRON_QUEUE_NAME, $cron_id);

        $data = json_decode($this->client->get($key), true);

        $this->updateCron($cron_id, $data['cron']);

        $job = $data['job'];

        return new Job($job['class'], $job['queue'], $job['args']);
    }

    /**
     * updateCron
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param string $id
     * @param string $cron
     *
     * @throws \RuntimeException
     */
    private function updateCron(string $id, string $cron)
    {
        $cronExpression = CronExpression::factory($cron);
        $next_run = $cronExpression->getNextRunDate()->getTimestamp();

        $this->client->zadd(self::CRON_QUEUE_NAME, [$id => $next_run]);
    }
}
