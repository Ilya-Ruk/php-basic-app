<?php

declare(strict_types=1);

use Rukavishnikov\Php\Basic\App\ApplicationInterface;
use Rukavishnikov\Php\Basic\App\ApplicationErrorHandler;
use Rukavishnikov\Psr\Container\Container;

require __DIR__ . '/../vendor/autoload.php';

//putenv('X_DEBUG=true'); // Disable for production!
//putenv('X_TRACE=true'); // Disable for production!

set_exception_handler([ApplicationErrorHandler::class, 'exceptionHandler']);
set_error_handler([ApplicationErrorHandler::class, 'errorHandler']);

$config = require __DIR__ . '/../config/config.php';

$container = new Container($config);

/** @var ApplicationInterface $app */
$app = $container->get(ApplicationInterface::class);

$app->run();
