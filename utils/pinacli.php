<?php

namespace Pina;

include __DIR__."/../vendor/autoload.php";

Config::initPath(__DIR__.'/config');
CLI::handle($argv, basename(__FILE__));