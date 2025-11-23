<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Middlewares;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\HttpExceptions\MethodNotAllowedHttpException;
use Rukavishnikov\Php\Router\MethodNotAllowedException;
use Rukavishnikov\Php\Router\RouteNotFoundException;
use Rukavishnikov\Php\Router\RouterInterface;

final class RouteDispatcherMiddleware implements MiddlewareInterface
{
    /**
     * @param ContainerInterface $container
     * @param RouterInterface $router
     */
    public function __construct(
        private ContainerInterface $container,
        private RouterInterface $router,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws MethodNotAllowedHttpException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get route from request, parse request attributes and add them to request

        try {
            $route = $this->router->getRoute($request);
        } catch (MethodNotAllowedException $e) {
            throw new MethodNotAllowedHttpException('Method not allowed!', 405, $e);
        } catch (RouteNotFoundException) { // If route not found then calls next handler (ApplicationNotFoundHandler)
            return $handler->handle($request);
        }

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        // Get handler from route

        /** @var RequestHandlerInterface $routeHandler */
        $routeHandler = $this->container->get($route->handler);

        // Handle a server request and produces a response use handler from route

        return $routeHandler->handle($request);
    }
}
