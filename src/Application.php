<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Emitter\EmitterInterface;
use Rukavishnikov\Php\Router\NotFoundException;
use Rukavishnikov\Php\Router\RouterInterface;
use Rukavishnikov\Psr\Http\Message\ServerRequest;

final class Application implements ApplicationInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewareList = [];

    /**
     * @param ContainerInterface $container
     * @param ServerRequestInterface $request
     * @param RouterInterface $router
     * @param EmitterInterface $emitter
     */
    public function __construct(
        private ContainerInterface $container,
        private ServerRequestInterface $request,
        private RouterInterface $router,
        private EmitterInterface $emitter,
    ) {
    }

    /**
     * @param MiddlewareInterface[] $middlewareList
     * @return void
     */
    public function setMiddlewareList(array $middlewareList): void
    {
        foreach ($middlewareList as $middleware) {
            $this->addMiddleware($middleware);
        }
    }

    /**
     * @inheritDoc
     *
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws \Rukavishnikov\Psr\Container\NotFoundException
     */
    public function run(): void
    {
        $request = $this->request;

        // Get route from request, parse request attributes and add them to request

        $route = $this->router->getRoute($request);

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        // Get handler

        /** @var RequestHandlerInterface $targetHandler */
        $targetHandler = $this->container->get($route->handler);

        // Handle a server request and produces a response use middlewares

        $response = $this->handler($targetHandler)->handle($request);

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

        $this->emitter->emit($response, $withoutBody);
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return void
     */
    private function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middlewareList[] = $middleware;
    }

    /**
     * @param RequestHandlerInterface $targetHandler
     * @return RequestHandlerInterface
     */
    private function handler(RequestHandlerInterface $targetHandler): RequestHandlerInterface
    {
        return new class ($this->middlewareList, $targetHandler) implements RequestHandlerInterface {
            /**
             * @param MiddlewareInterface[] $middlewareList
             * @param RequestHandlerInterface $targetHandler
             */
            public function __construct(
                private array $middlewareList,
                private RequestHandlerInterface $targetHandler,
            ) {
            }

            /**
             * @param ServerRequestInterface $request
             * @return ResponseInterface
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                /** @var MiddlewareInterface|null $middleware */
                $middleware = array_shift($this->middlewareList);

                if (is_null($middleware)) {
                    return $this->targetHandler->handle($request);
                }

                $nextRequestHandler = clone $this;

                return $middleware->process($request, $nextRequestHandler);
            }
        };
    }
}
