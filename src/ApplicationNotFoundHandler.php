<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class ApplicationNotFoundHandler implements RequestHandlerInterface
{
    /**
     * @param JsonHelper $jsonHelper
     * @param ResponseInterface $response
     */
    public function __construct(
        private JsonHelper $jsonHelper,
        private ResponseInterface $response,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = sprintf("Path '%s' not found!", $request->getUri()->getPath());

        $body = $this->jsonHelper->encode($body);
        $this->response->getBody()->write($body);

        return $this->response->withStatus(404); // Not Found
    }
}
