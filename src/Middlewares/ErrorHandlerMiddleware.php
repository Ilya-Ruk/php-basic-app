<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;
use Throwable;

final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @param JsonHelper $jsonHelper
     * @param ResponseInterface $response
     * @param bool $debugMode
     */
    public function __construct(
        private JsonHelper $jsonHelper,
        private ResponseInterface $response,
        private bool $debugMode = false,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            $response = $this->response->withStatus($e->getCode());

            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];

            if ($this->debugMode) {
                $data['file'] = $e->getFile();
                $data['line'] = $e->getLine();

                $previousError = $e->getPrevious();

                $errorLevel = 1;

                while ($previousError !== null) {
                    $data['previousError'][$errorLevel] = [
                        'code' => $previousError->getCode(),
                        'message' => $previousError->getMessage(),
                        'file' => $previousError->getFile(),
                        'line' => $previousError->getLine(),
                    ];

                    $previousError = $previousError->getPrevious();

                    $errorLevel++;
                }

                $data['trace'] = $e->getTrace();
            }

            $body = $this->jsonHelper->encode($data);
            $response->getBody()->write($body);

            $size = $response->getBody()->getSize();

            if (!is_null($size)) {
                $response = $response->withHeader('Content-Length', (string)$size);
            }

            return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        return $response;
    }
}
