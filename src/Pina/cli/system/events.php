<?php

namespace Pina;

use Pina\Commands\RunWorker;

/** @var RunWorker $command */
$command = App::make(RunWorker::class);
$command();
