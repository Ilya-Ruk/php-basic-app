<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class HelloAction implements RequestHandlerInterface
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
        // Get request params (attributes)

        $name = $request->getAttribute('name', 'World');
        $id = $request->getAttribute('id');

        // Prepare response

        if (is_null($id)) {
            $body = sprintf("Hello, %s!", $name);
        } else {
            $body = sprintf("Hello, %s (%s)!", $name, $id);
        }

        $body = $this->jsonHelper->encode($body);
        $this->response->getBody()->write($body);

        // Return response

        return $this->response;
    }
}
