<?php

namespace Pina;

use Pina\Commands\Update;

/** @var Update $command */
$command = App::load(Update::class);
echo $command($argv[2] ?? '');