<?php

namespace Pina;

use Pina\Commands\ClearSmartyCache;
use Pina\Commands\RunScheduler;
use Pina\Commands\RunWorker;
use Pina\Commands\Update;

class Module implements ModuleInterface
{

    public function getPath()
    {
        return __DIR__;
    }
    
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getTitle()
    {
        return 'Framework';
    }

    public function __construct()
    {
        App::onLoad(CLI::class, function (CLI $cli) {
            $cli->register('system.clear-smarty-cache', ClearSmartyCache::class);
            $cli->register('system.cron', RunScheduler::class);
            $cli->register('system.events', RunWorker::class);
            $cli->register('system.update', Update::class);
        });
    }

    
}
