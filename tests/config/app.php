<?php

return array(
    'path' => realpath(__DIR__.'/../app'),
    'version' => '1',
    'charset' => 'utf-8',
    'timezone' => 'Europe/Moscow',
    'templater' => array(
        'cache' => __DIR__.'/../var/cache',
        'compiled' => __DIR__.'/../var/compiled',
    ),
    'sharedDepencies' => [
    ],
);