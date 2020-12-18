<?php

namespace Pina\Gearman;

use Pina\Config;
use Pina\App;

class GearmanEventWorker
{

    private $worker = null;

    public function __construct()
    {
        $config = Config::load('gearman');
        $this->worker = new \GearmanWorker();
        $this->worker->addServer($config['host'], $config['port']);

        $keys = App::events()->getHandlerKeys();
        foreach ($keys as $key) {
            echo 'listen ' . $key . "\n";
            $this->worker->addFunction($key, [$this, 'handler']);
        }
    }

    public function handler($job)
    {
        $cmd = $job->functionName();
        $payload = $job->workload();

        App::events()->getHandler($cmd)->handle($payload);
    }

    public function work()
    {
        $this->worker->work();
        return $this->worker->returnCode() === GEARMAN_SUCCESS;
    }

}
