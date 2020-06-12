<?php

namespace Notification\Jobs;

class ProcessNotification
{
    protected $args;

    /**@var array $config*/
    protected $config;

    /**
     * @param mixed ...$args
     */
    public function __invoke(...$args)
    {
        $this->args = json_decode($args[0], true);
        $this->perform();
    }

    /**
     * ProcessNotification constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Add notification.
     * @return void
     */
    public function perform()
    {
        echo "Start processing notification: " . $this->args['type'] . "\n \n";
        switch ($this->args['type']) {
            case 'new-friend-request':
                $this->sendNewFriendNotification();
                break;
        }
    }

    public function sendNewFriendNotification()
    {
        // TODO: Implement sendNewFriendNotification() method.
    }
}
