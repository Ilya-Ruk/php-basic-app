<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class HelloController implements RequestHandlerInterface
{
    /**
     * @param ResponseInterface $response
     * @param LoggerInterface $log
     */
    public function __construct(
        private ResponseInterface $response,
        private LoggerInterface $log,
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get request params (attributes)

        $name = $request->getAttribute('name', 'World');
        $id = $request->getAttribute('id');

        // Log

        $context = [
            'ip' => $request->getServerParam('REMOTE_ADDR'),
            'name' => $name,
        ];

        if (!is_null($id)) {
            $context['id'] = $id;
        }

        $this->log->info('Hello', $context);

        // Prepare response

        $fullName = $name;

        if (!is_null($id)) {
            $fullName .= ' (' . $id . ')';
        }

        $body = sprintf("Hello, %s!", $fullName);

        $this->response->getBody()->write($body);

        // Return response

        return $this->response;
    }
}
