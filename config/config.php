<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rukavishnikov\Php\Basic\App\Application;
use Rukavishnikov\Php\Basic\App\ApplicationInterface;
use Rukavishnikov\Php\Basic\App\Databases\DatabaseInterface;
use Rukavishnikov\Php\Basic\App\Databases\SQLiteDatabase;
use Rukavishnikov\Php\Basic\App\Events\BookChangeEvent;
use Rukavishnikov\Php\Basic\App\Events\BookChangeListener;
use Rukavishnikov\Php\Basic\App\Events\EventDispatcher;
use Rukavishnikov\Php\Basic\App\Handlers\Books\AddAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\DeleteAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\EditAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\ListAction;
use Rukavishnikov\Php\Basic\App\Handlers\Books\ViewAction;
use Rukavishnikov\Php\Basic\App\Handlers\HelloAction;
use Rukavishnikov\Php\Basic\App\Middlewares\AccessLoggerMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\ApplicationJsonMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\ContentLengthMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\ContentTypeMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\ErrorHandlerMiddleware;
use Rukavishnikov\Php\Basic\App\Middlewares\RouteDispatcherMiddleware;
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

$startDateTime = new DateTime();

return [
    ApplicationInterface::class => [
        'class' => Application::class,

        'setMiddlewareList()' => [
            static fn (ContainerInterface $container) => [
                $container->get(ErrorHandlerMiddleware::class), // Catches all exceptions (must be first)

                $container->get(ContentLengthMiddleware::class), // Adds Content-Length header to response
                $container->get(ContentTypeMiddleware::class), // Adds Content-Type header to response

                $container->get(AccessLoggerMiddleware::class), // Writers access log

                $container->get(ApplicationJsonMiddleware::class), // Decodes request body (JSON)

                $container->get(RouteDispatcherMiddleware::class), // Routes select, gets handler and call it (must be last)
            ],
        ],
    ],
    ServerRequestInterface::class => ServerRequest::class,
    ResponseInterface::class => Response::class,
    EmitterInterface::class => Emitter::class,
    BookRepositoryInterface::class => SQLiteBookRepository::class,
    DatabaseInterface::class => [
        'class' => SQLiteDatabase::class,

        '__construct()' => [
            static fn () => new FilePath(__DIR__ . '/../database/test.sqlite3'),
        ],
    ],
    ErrorHandlerMiddleware::class => [
        'class' => ErrorHandlerMiddleware::class,

        '__construct()' => [
            JsonHelper::class,
            new Response(), // Create new instance for error handler response
            (bool)getenv('X_DEBUG', true),
            (bool)getenv('X_TRACE', true),
        ],
    ],
    AccessLoggerMiddleware::class => [
        'class' => AccessLoggerMiddleware::class,

        '__construct()' => [
            'AccessLogger',
        ],
    ],
    'AccessLogger' => [
        'class' => Log::class,

        '__construct()' => [
            'AccessLogTarget',
        ],
    ],
    'AccessLogTarget' => [
        'class' => LogTargetFile::class,

        '__construct()' => [
            static fn () => new FilePath(__DIR__ . '/../runtime/logs/access.log', true),
        ],
    ],
    RouteDispatcherMiddleware::class => [
        'class' => RouteDispatcherMiddleware::class,

        '__construct()' => [
            static fn (ContainerInterface $container) => $container,
        ],
    ],
    RouterInterface::class => [
        'class' => Router::class,

        '__construct()' => [
            [
                new Route('GET', '/hello[/{name:[a-zA-Z][a-zA-Z-]*}][/{id:\d+}]', HelloAction::class),

                new Route('GET', '/books', ListAction::class),
                new Route('GET', '/books/{id:\d+}', ViewAction::class),
                new Route('POST', '/books/add', AddAction::class),
                new Route('PUT', '/books/edit/{id:\d+}', EditAction::class),
                new Route('DELETE', '/books/delete/{id:\d+}', DeleteAction::class),
            ],
        ],
    ],
    EventDispatcher::class => [
        'class' => EventDispatcher::class,

        '__construct()' => [
            static fn (ContainerInterface $container) => [
                BookChangeEvent::class => $container->get(BookChangeListener::class),
            ],
        ],
    ],
    BookChangeListener::class => [
        'class' => BookChangeListener::class,

        '__construct()' => [
            'BookChangeLogger',
        ],
    ],
    'BookChangeLogger' => [
        'class' => Log::class,

        '__construct()' => [
            'BookChangeLogTarget',
        ],
    ],
    'BookChangeLogTarget' => [
        'class' => LogTargetFile::class,

        '__construct()' => [
            static fn () => new FilePath(__DIR__ . '/../runtime/logs/book_change.log', true),
        ],
    ],
    FormatterInterface::class => [
        'class' => DefaultFormatter::class,

        '__construct()' => [
            ValueToStringHelper::class,
            $startDateTime,
        ],
    ],
];
