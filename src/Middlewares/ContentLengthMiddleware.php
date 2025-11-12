<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ContentLengthMiddleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$response->hasHeader('Content-Length')) {
            $size = $response->getBody()->getSize();

            if (!is_null($size)) {
                $response = $response->withHeader('Content-Length', (string)$size);
            }
        }

        return $response;
    }
}
