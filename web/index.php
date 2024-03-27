<?php

declare(strict_types=1);

use Rukavishnikov\Php\Basic\App\App;
use Rukavishnikov\Psr\Container\Container;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';

$container = new Container($config);

$app = App::create($container);
$app->run();
