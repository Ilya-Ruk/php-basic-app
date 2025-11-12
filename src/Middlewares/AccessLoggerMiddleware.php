<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class AccessLoggerMiddleware implements MiddlewareInterface
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $message = "{ip} {method} {path} {code}\r\n";

        $context = [
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? '-',
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'code' => $response->getStatusCode(),
        ];

        $this->logger->info($message, $context);

        return $response;
    }
}
