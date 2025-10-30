<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rukavishnikov\Php\Basic\App\Controllers\HelloController;
use Rukavishnikov\Php\Emitter\Emitter;
use Rukavishnikov\Php\Emitter\EmitterInterface;
use Rukavishnikov\Php\Helper\Classes\FilePath;
use Rukavishnikov\Php\Helper\Classes\ValueToStringHelper;
use Rukavishnikov\Php\Router\Router;
use Rukavishnikov\Php\Router\RouterInterface;
use Rukavishnikov\Psr\Http\Message\Response;
use Rukavishnikov\Psr\Http\Message\ServerRequest;
use Rukavishnikov\Psr\Log\Formatter\DefaultFormatter;
use Rukavishnikov\Psr\Log\Formatter\FormatterInterface;
use Rukavishnikov\Psr\Log\Log;
use Rukavishnikov\Psr\Log\LogTargetFile;
use Rukavishnikov\Psr\Log\LogTargetInterface;

$startDateTime = new DateTime();

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
    ResponseInterface::class => Response::class,
    LoggerInterface::class => Log::class,
    LogTargetInterface::class => [
        'class' => LogTargetFile::class,
        '__construct()' => [
            static fn () => new FilePath(__DIR__ . '/../runtime/logs/access.log', true),
        ],
    ],
    FormatterInterface::class => [
        'class' => DefaultFormatter::class,
        '__construct()' => [
            ValueToStringHelper::class,
            $startDateTime,
            //'Y-m-d H:i:s',
        ],
    ],
    EmitterInterface::class => Emitter::class,
];
