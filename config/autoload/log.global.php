<?php

return [
    'log' => [
        'queue_log' => [
            'writers' => [
                'FileWriter' => [
                    'name' => 'stream',
                    'priority' => \Laminas\Log\Logger::NOTICE,
                    'options' => [
                        'stream' => sprintf('%s/../../data/logs/error-log-queue.log',
                            __DIR__
                        ),
                        // explicitly log all messages
                        'filters' => [
                            'allMessages' => [
                                'name' => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => \Laminas\Log\Logger::NOTICE,
                                ],
                            ],
                        ],
                        'formatter' => [
                            'name' => \Laminas\Log\Formatter\Json::class,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
