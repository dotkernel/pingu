<?php

namespace Notification\Jobs;

use QueueJitsu\Job\Job;
use QueueJitsu\Job\JobManager;
use QueueJitsu\Scheduler\Scheduler;

class ProcessRequest
{
    protected $args;

    /**@var JobManager $jobManager */
    protected $jobManager;

    /** @var Scheduler */
    protected $jobScheduler;

    /**
     * @param mixed ...$args
     */
    public function __invoke(...$args)
    {
        $this->args = json_decode($args[0], true);
        $this->perform();
    }

    /**
     * ProcessRequest constructor.
     * @param JobManager $jobManager
     * @param Scheduler $scheduler
     */
    public function __construct(
        JobManager $jobManager,
        Scheduler $scheduler
    ) {
        $this->jobManager = $jobManager;
        $this->jobScheduler = $scheduler;
    }

    /**
     * Add requests in queues.
     * @return void
     */
    public function perform()
    {
        echo "Start processing request: " . $this->args['type'] . "\n \n";
        switch ($this->args['type']) {
            case 'password-reset':
                    $this->setEmailEnqueue();
                break;
            case 'new-friend-request':
                $this->setNotificationEnqueue();
                break;
            case 'overnight-report':
                // for delaying the email just use methods with setDelayed giving parameter the
                // strtotime() moment when the notification should be send
                $this->setDelayedEmailEnqueue(strtotime('in 1h'));
                // same for sending notification
                $this->setDelayedNotificationEnqueue(strtotime('today 7:00 am'));
                break;
            default:
                $this->setNotificationEnqueue();
                $this->setEmailEnqueue();
                break;
        }
        echo "End processing request: " . $this->args['type'] . "\n \n";
    }

    private function setNotificationEnqueue()
    {
        $this->jobManager->enqueue(new Job(ProcessNotification::class,'push', [json_encode($this->args)]));
    }

    private function setEmailEnqueue()
    {
        $this->jobManager->enqueue(
            new Job(ProcessEmail::class, 'email', [json_encode($this->args)])
        );
    }

    private function setDelayedNotificationEnqueue(int $delay)
    {
        $this->jobScheduler->enqueueAt(
            $delay,
            new Job(
                ProcessNotification::class,
                'push',
                [json_encode($this->args)]
            )
        );
    }

    private function setDelayedEmailEnqueue(int $delay)
    {
        $this->jobScheduler->enqueueAt(
            $delay,
            new Job(ProcessEmail::class, 'email', [json_encode($this->args)])
        );
    }
}
