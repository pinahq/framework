<?php

return array(
    'version' => '1',
    'charset' => 'utf-8',
    'timezone' => 'Europe/Moscow',
    'sharedDepencies' => [
        \Pina\ResourceManagerInterface::class => \Pina\ResourceManager::class,
        \Pina\EventQueueInterface::class => \Pina\CronEventQueue::class,
    ],
);