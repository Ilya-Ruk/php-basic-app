<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class BodyParamsMiddleware implements MiddlewareInterface
{
    /**
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        private JsonHelper $jsonHelper,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader('Content-Type')) {
            $contentType = $request->getHeaderLine('Content-Type');

            if (preg_match('~^application/json.*$~i', $contentType) === 1) {
                $body = $request->getBody()->getContents();
                $parsedBody = $this->jsonHelper->decode($body, true);

                return $handler->handle($request->withParsedBody($parsedBody));
            }
        }

        return $handler->handle($request);
    }
}
