<?php

use Monolog\Handler\RotatingFileHandler;

return function(\Monolog\Logger $logger) {
    $name = $logger->getName();
    $name =  preg_replace('/[^a-zA-Z0-9]/ui', '-', $name);
    $handler = new RotatingFileHandler(__DIR__.'/../var/log/'.$name.'.log', 10, \Monolog\Logger::INFO);
    $logger->pushHandler($handler);
};
