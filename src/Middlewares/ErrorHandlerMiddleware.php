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
     * @param bool $debug
     * @param bool $trace
     */
    public function __construct(
        private JsonHelper $jsonHelper,
        private ResponseInterface $response,
        private bool $debug = false,
        private bool $trace = false,
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
            $code = $e->getCode();

            if ($code >= 400 && $code <= 599) { // 4xx (Client Error) / 5xx (Server Error)
                $responseCode = $code;
            } else {
                $responseCode = 500; // Internal Server Error
            }

            $response = $this->response->withStatus($responseCode)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');

            if ($this->debug) {
                $data = [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];

                $previousError = $e->getPrevious();

                $errorLevel = 1;

                while (!is_null($previousError)) {
                    $data['previousError'][$errorLevel] = [
                        'code' => $previousError->getCode(),
                        'message' => $previousError->getMessage(),
                        'file' => $previousError->getFile(),
                        'line' => $previousError->getLine(),
                    ];

                    $previousError = $previousError->getPrevious();

                    $errorLevel++;
                }

                if ($this->trace) {
                    $data['trace'] = $e->getTrace();
                }
            } elseif ($code >= 100 && $code <= 599) { // Valid HTTP response status code
                $data = [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ];
            } else {
                $data = 'Internal server error!';
            }

            $body = $this->jsonHelper->encode($data);
            $response->getBody()->write($body);

            $size = $response->getBody()->getSize();

            if (!is_null($size)) {
                $response = $response->withHeader('Content-Length', (string)$size);
            }
        }

        return $response;
    }
}
