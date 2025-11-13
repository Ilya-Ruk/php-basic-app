<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rukavishnikov\Php\Basic\App\Application;
use Rukavishnikov\Php\Basic\App\ApplicationInterface;
use Rukavishnikov\Php\Basic\App\Databases\DatabaseInterface;
use Rukavishnikov\Php\Basic\App\Databases\SQLiteDatabase;
use Rukavishnikov\Php\Basic\App\Handlers\Books\AddAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\DeleteAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\EditAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\ListAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\ViewAction;
use Rukavishnikov\Php\Basic\App\Handlers\HelloAction;
use Rukavishnikov\Php\Basic\App\Middlewares\AccessLoggerMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\BodyParamsMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\ContentLengthMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\ContentTypeMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\ErrorHandlerMiddleware;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Repositories\Books\SQLiteBookRepository;
use Rukavishnikov\Php\Emitter\Emitter;
use Rukavishnikov\Php\Emitter\EmitterInterface;
use Rukavishnikov\Php\Helper\Classes\FilePath;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;
use Rukavishnikov\Php\Helper\Classes\ValueToStringHelper;
use Rukavishnikov\Php\Router\Route;
use Rukavishnikov\Php\Router\Router;
use Rukavishnikov\Php\Router\RouterInterface;
use Rukavishnikov\Psr\Http\Message\Response;
use Rukavishnikov\Psr\Http\Message\ServerRequest;
use Rukavishnikov\Psr\Log\Formatter\DefaultFormatter;
use Rukavishnikov\Psr\Log\Formatter\FormatterInterface;
use Rukavishnikov\Psr\Log\Log;
use Rukavishnikov\Psr\Log\LogTargetFile;
use Rukavishnikov\Psr\Log\LogTargetInterface;

$debug = false;
$trace = false; // Only used when debug is enabled

$startDateTime = new DateTime();

return [
    ApplicationInterface::class => [
        'class' => Application::class,

        '__construct()' => [
            static fn (ContainerInterface $container) => $container,
        ],

        'setMiddlewareList()' => [
            static fn (ContainerInterface $container) => [
                $container->get(ErrorHandlerMiddleware::class), // Error handler must be first for catch all exceptions

                $container->get(BodyParamsMiddleware::class),

                $container->get(AccessLoggerMiddleware::class),

                $container->get(ContentLengthMiddleware::class),
                $container->get(ContentTypeMiddleware::class),
            ],
        ],
    ],
    ServerRequestInterface::class => ServerRequest::class,
    RouterInterface::class => [
        'class' => Router::class,

        '__construct()' => [
            [
                new Route('GET', '/hello[/{name:[a-zA-Z][a-zA-Z-]*}][/{id:\d+}]', HelloAction::class),

                new Route('GET', '/books', ListAction::class),
                new Route('GET', '/books/{id:\d+}', ViewAction::class),
                new Route('POST', '/books/add', AddAction::class),
                new Route('PATCH', '/books/edit/{id:\d+}', EditAction::class),
                new Route('DELETE', '/books/delete/{id:\d+}', DeleteAction::class),
            ],
        ],
    ],
    ResponseInterface::class => Response::class,
    EmitterInterface::class => Emitter::class,
    DatabaseInterface::class => [
        'class' => SQLiteDatabase::class,

        '__construct()' => [
            static fn () => new FilePath(__DIR__ . '/../database/test.sqlite3'),
        ],
    ],
    BookRepositoryInterface::class => SQLiteBookRepository::class,
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
        ],
    ],
    ErrorHandlerMiddleware::class => [
        'class' => ErrorHandlerMiddleware::class,

        '__construct()' => [
            JsonHelper::class,
            new Response(), // Create new instance for error handler response
            $debug,
            $trace,
        ],
    ],
];
