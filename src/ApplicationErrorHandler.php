<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App;

use ErrorException;
use Throwable;

final class ApplicationErrorHandler
{
    /**
     * @var string[]
     */
    private static array $reasonPhraseList = [
        // 4xx (Client Error)
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        426 => 'Upgrade Required',

        // 5xx (Server Error)
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

    /**
     * @param Throwable $e
     * @return void
     */
    public static function exceptionHandler(Throwable $e): void
    {
        $debug = getenv('X_DEBUG', true);
        $trace = getenv('X_TRACE', true);

        $serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $code = $e->getCode();

        if ($code >= 400 && $code <= 599) { // Client error or server error
            $responseCode = $code;
        } else {
            $responseCode = 500; // Internal Server Error
        }

        $responseReasonPhrase = self::$reasonPhraseList[$responseCode] ?? null;

        if ($debug !== false) {
            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

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

            if ($trace !== false) {
                $data['trace'] = $e->getTrace();
            }
        } elseif ($code >= 100 && $code <= 499) {
            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        } else {
            $data = "Internal server error!";
        }

        $body = json_encode($data);

        if ($body === false) {
            $body = "ApplicationErrorHandler: JSON encode error!";
        }

        // Status line

        $statusLine = $serverProtocol;
        $statusLine .= ' ' . $responseCode;

        if (!is_null($responseReasonPhrase)) {
            $statusLine .= ' ' . $responseReasonPhrase;
        }

        header($statusLine, true, $responseCode);

        // Headers

        header('Content-Type: application/json; charset=utf-8', false);
        header('Content-Length: ' . strlen($body), false);

        // Message body

        if ($requestMethod === 'HEAD') {
            return;
        }

        echo $body;
    }

    /**
     * @param int $errorLevel
     * @param string $errorMessage
     * @param string $fileName
     * @param int $lineNumber
     * @return false
     * @throws ErrorException
     */
    public static function errorHandler(int $errorLevel, string $errorMessage, string $fileName, int $lineNumber): bool
    {
        if (error_reporting() & $errorLevel) {
            throw new ErrorException($errorMessage, 500, $errorLevel, $fileName, $lineNumber);
        }

        return false;
    }
}
