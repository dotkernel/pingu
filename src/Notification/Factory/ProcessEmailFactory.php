<?php

declare(strict_types=1);

namespace Notification\Factory;

use Notification\Jobs\ProcessEmail;
use Psr\Container\ContainerInterface;
use Mezzio\Twig\TwigRenderer;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mime\Part;
use Laminas\Mime\Message as Body;

/**
 * Class ProcessEmailFactory
 * @package Notification\Factory
 */
class ProcessEmailFactory
{
    /**
     * @param $container
     * @return ProcessEmail
     */
    public function __invoke(ContainerInterface $container): ProcessEmail
    {
        $transport = new Smtp();
        $config = $container->get('config')['mail'];
        $options = new SmtpOptions($config);
        $transport->setOptions($options);
        return new ProcessEmail(
            $transport,
            new Message(),
            $container->get(TwigRenderer::class),
            new Body(),
            new Part(),
            new Part(),
            $container->get('config')
        );
    }
}
