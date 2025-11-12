<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App;

interface ApplicationInterface
{
    /**
     * @return void
     */
    public function run(): void;
}
