<?php

declare(strict_types=1);

namespace Notification\Middleware;

use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Class DefaultTemplateParamsMiddleware
 * @package Notification\Middleware
 *
 * @Service()
 */
class DefaultTemplateParamsMiddleware implements MiddlewareInterface
{
    /** @var TemplateRendererInterface */
    protected $template;

    /** @var string */
    protected $apiUrl;

    /**
     * DefaultTemplateParamsMiddleware constructor.
     * @param TemplateRendererInterface $template
     * @param string $apiUrl
     *
     * @Inject({TemplateRendererInterface::class, "config.api_url"})
     */
    public function __construct(
        TemplateRendererInterface $template,
        string $apiUrl
    ) {
        $this->template = $template;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return mixed|ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->template->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'api_url',
            rtrim($this->apiUrl, '/')
        );

        return $handler->handle($request);
    }
}
