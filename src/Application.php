<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Emitter\EmitterInterface;

final class Application implements ApplicationInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewareList = [];

    /**
     * @param ServerRequestInterface $request
     * @param EmitterInterface $emitter
     * @param ApplicationNotFoundHandler $applicationNotFoundHandler
     */
    public function __construct(
        private ServerRequestInterface $request,
        private EmitterInterface $emitter,
        private ApplicationNotFoundHandler $applicationNotFoundHandler,
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
     */
    public function run(): void
    {
        // Handle a server request and produces a response use middlewares

        $response = $this->handler($this->applicationNotFoundHandler)->handle($this->request);

        // Emit response

        if (
            $this->request->getMethod() === 'HEAD'
            || ($response->getStatusCode() >= 100 && $response->getStatusCode() <= 199) // 1xx (Informational)
            || in_array($response->getStatusCode(), [204, 304]) // No Content / Not Modified
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
