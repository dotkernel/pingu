<?php

namespace Notification\Jobs;

use Doctrine\ORM\EntityNotFoundException;
use Mezzio\Twig\TwigRenderer;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Message as Body;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

class ProcessEmail
{
    protected $args;

    protected $config;

    /** @var Body */
    protected $body;

    /** @var  Part */
    protected $html;

    /** @var  Part */
    protected $text;

    /** @var TransportInterface */
    protected $transporter;

    /** @var Message */
    protected $message;

    /** @var TwigRenderer $twigRenderer*/
    protected $twigRenderer;

    /**
     * ProcessEmail constructor.
     * @param TransportInterface $transporter
     * @param Message $message
     * @param TwigRenderer $twigRenderer
     * @param Part $html
     * @param Body $body
     * @param Part $text
     * @param $config
     */

    public function __construct(
        TransportInterface $transporter,
        Message $message,
        TwigRenderer $twigRenderer,
        Body $body,
        Part $html,
        Part $text,
        $config
    ) {
        $this->transporter = $transporter;
        $this->message = $message;
        $this->config = $config;
        $this->twigRenderer = $twigRenderer;
        $this->html = $html;
        $this->body = $body;
        $this->text = $text;
    }

    /**
     * @param mixed ...$args
     * @throws EntityNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function __invoke(...$args)
    {
        $this->args = json_decode($args[0], true);
        $this->html->setType(Mime::TYPE_HTML);
        $this->text->setType(Mime::TYPE_TEXT);
        $this->perform();
    }

    /**
     * @throws EntityNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function perform()
    {
        echo "Start processing email: " . $this->args['type'] . "\n \n";

        switch ($this->args['type']) {
            case 'password-reset':
                $this->preparePasswordResetEmail();
                break;
            case 'overnight-report':
                $this->prepareReportEmail();
                break;
        }

        $this->sendEmail();

        echo "End processing email: " . $this->args['type'] . "\n \n";
    }

    /**
     * @throws EntityNotFoundException
     */
    public function sendEmail()
    {
        $this->message->setTo('email@receiver.com');
        $this->message->setFrom('pingu@apidemia.com', 'Pingu');
        $this->body->setParts([$this->html, $this->text]);
        $this->message->setBody($this->body);

        try {
            $this->transporter->send($this->message);
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function prepareReportEmail()
    {
        $this->message->setSubject('Daily report - ' . date('Y-m-d'));
        $this->html->setContent($this->twigRenderer->render('notification-email::report/new'));
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function preparePasswordResetEmail()
    {
        $this->message->setSubject('Reset your password');
        $this->html->setContent($this->twigRenderer->render('notification-email::user/reset-password-requested'));
    }
}
