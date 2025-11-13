<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App;

use ErrorException;
use Throwable;

final class ApplicationErrorHandler
{
    /**
     * @param Throwable $e
     * @return void
     */
    public static function exceptionHandler(Throwable $e): void
    {
        $code = $e->getCode();

        if ($code >= 400 && $code <= 599) { // Client error or server error
            http_response_code($code);
        } else {
            http_response_code(500);
        }

        header('Content-Type: text/plain; charset=utf-8');

        $message = $e->getFile();
        $message .= ' (' . $e->getLine() . ')';
        $message .= ': ' . $e->getMessage();

        echo $message;

        $previousError = $e->getPrevious();

        $errorLevel = 1;

        while ($previousError !== null) {
            $message = $previousError->getFile();
            $message .= ' (' . $previousError->getLine() . ')';
            $message .= ': ' . $previousError->getMessage();

            echo "\r\n\r\n" . $errorLevel . ': ' . $message;

            $previousError = $previousError->getPrevious();

            $errorLevel++;
        }
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
