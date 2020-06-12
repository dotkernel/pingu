<?php

use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\TwigEnvironmentFactory;
use Mezzio\Twig\TwigRendererFactory;

return [
    'dependencies' => [
        'factories' => [
            Twig\Environment::class => TwigEnvironmentFactory::class,
            TemplateRendererInterface::class => TwigRendererFactory::class,
        ],
    ],

    'templates' => [
        'extension' => 'html.twig',
    ],

    'twig' => [
        'cache_dir' => __DIR__ . '/../../data/cache/twig',
        'assets_url' => '/',
        'assets_version' => 1,
        'extensions' => [
            // extension service names or instances
        ],
        'runtime_loaders' => [
            // runtime loader names or instances

        ],
        'globals' => [
            // Variables to pass to all twig templates
        ],
        // 'timezone' => 'default timezone identifier; e.g. America/Chicago',
    ],
];
