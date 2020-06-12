<?php

declare(strict_types=1);

namespace Notification\Handler;

use Notification\Jobs\ProcessRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class NotificationHandler implements RequestHandlerInterface
{
    /** @var array */
    private $config;

    /** @var array */
    private $configFrom;

    /**
     * NotificationHandler constructor.
     * @param array $config
     * @param array $configFrom
     */
    public function __construct(
        array $config,
        array $configFrom
    ) {
        $this->config = $config;
        $this->configFrom = $configFrom;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [
            'type' => 'email',
            'to' => 'team@dotkernel.com',
            'subject' => 'This is a test',
            'body' => "This is a test email.\n Thanks",
            'config' => $this->config,
            'application' => $this->configFrom
        ];
        \Resque::enqueue('requests', ProcessRequest::class, $data);
        return new JsonResponse([
            'taskIdentifier' => $data,
        ]);
    }
}
