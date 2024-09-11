<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Emitter\EmitterInterface;
use Rukavishnikov\Php\Router\RouterInterface;
use Rukavishnikov\Psr\Container\Container;
use Rukavishnikov\Psr\Http\Message\ServerRequest;
use RuntimeException;
use Throwable;

final class App
{
    /**
     * @var App
     */
    private static App $instance;

    /**
     * @var Container
     */
    private static Container $container;

    /**
     * @param Container $container
     */
    private function __construct(Container $container)
    {
        self::$container = $container;
    }

    /**
     * @param Container $container
     * @return App
     */
    public static function create(Container $container): App
    {
        if (!isset(self::$instance)) {
            self::$instance = new App($container);
        }

        return self::$instance;
    }

    /**
     * @return Container
     */
    public static function getContainer(): Container
    {
        if (!isset(self::$container)) {
            throw new RuntimeException('Container not defined!', 500);
        }

        return self::$container;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        try {
            // Get server request component

            /** @var ServerRequestInterface $request */
            $request = self::$container->get(ServerRequestInterface::class);

            // Get router component

            /** @var RouterInterface $router */
            $router = self::$container->get(RouterInterface::class);

            // Get controller name from request, parse request attributes and add them to request

            $attributes = [];

            $controllerName = $router->getControllerNameAndParseAttributes($request, $attributes);

            foreach ($attributes as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            // Get controller component

            /** @var RequestHandlerInterface $controller */
            $controller = self::$container->get($controllerName);

            // Handles a server request and produces a response

            $response = $controller->handle($request);

            // Get emitter component

            /** @var EmitterInterface $emitter */
            $emitter = self::$container->get(EmitterInterface::class);

            // Emit response

            if (
                $request->getMethod() === ServerRequest::METHOD_HEAD
                || ($response->getStatusCode() >= 100 && $response->getStatusCode() < 200)
                || in_array($response->getStatusCode(), [204, 304])
            ) {
                $withoutBody = true;
            } else {
                $withoutBody = false;
            }

            $emitter->emit($response, $withoutBody);
        } catch (Throwable $e) {
            http_response_code($e->getCode());

            header('Content-Type: text/plain; charset=utf-8');

            echo $e->getMessage();

            $previousError = $e->getPrevious();

            $errorLevel = 1;

            while ($previousError !== null) {
                echo "\r\n\r\n" . $errorLevel . ': ' . $previousError->getMessage();

                $previousError = $previousError->getPrevious();

                $errorLevel++;
            }
        }
    }
}
