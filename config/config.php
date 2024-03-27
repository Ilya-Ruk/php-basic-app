<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rukavishnikov\Php\Basic\App\Controllers\HelloController;
use Rukavishnikov\Php\Emitter\Emitter;
use Rukavishnikov\Php\Emitter\EmitterInterface;
use Rukavishnikov\Php\Helper\Classes\ArrayToString;
use Rukavishnikov\Php\Helper\Classes\FilePath;
use Rukavishnikov\Php\Router\Router;
use Rukavishnikov\Php\Router\RouterInterface;
use Rukavishnikov\Psr\Http\Message\Response;
use Rukavishnikov\Psr\Http\Message\ServerRequest;
use Rukavishnikov\Psr\Log\Formatter\Formatter;
use Rukavishnikov\Psr\Log\Formatter\FormatterInterface;
use Rukavishnikov\Psr\Log\Log;
use Rukavishnikov\Psr\Log\LogTargetFile;
use Rukavishnikov\Psr\Log\LogTargetInterface;

return [
    ServerRequestInterface::class => ServerRequest::class,
    RouterInterface::class => [
        'class' => Router::class,
        '__construct()' => [
            [
                '/hello[/{name:[a-zA-Z][a-zA-Z-]*}[/{id:\d+}]]' => HelloController::class,
            ],
        ],
    ],
    HelloController::class => HelloController::class,
    ResponseInterface::class => Response::class,
    LoggerInterface::class => Log::class,
    LogTargetInterface::class => [
        'class' => LogTargetFile::class,
        '__construct()' => [
            fn() => new FilePath(__DIR__ . '/../runtime/logs/access.log', true),
        ],
    ],
    FormatterInterface::class => [
        'class' => Formatter::class,
        '__construct()' => [
            fn() => new DateTime(),
            fn() => new ArrayToString(),
        ],
    ],
    EmitterInterface::class => Emitter::class,
];
