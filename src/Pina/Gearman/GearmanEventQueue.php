<?php

namespace Pina\Gearman;

use Pina\Config;
use Pina\EventQueueInterface;

class GearmanEventQueue implements EventQueueInterface
{

    private $client = null;

    public function push($handler, $data, $priority)
    {
        switch ($priority) {
            case \Pina\Event::PRIORITY_HIGH:
                $this->getClient()->doHighBackground($handler->getKey(), $data);
                break;
            case \Pina\Event::PRIORITY_LOW:
                $this->getClient()->doLowBackground($handler->getKey(), $data);
                break;
            default:
                $this->getClient()->doBackground($handler->getKey(), $data);
                break;
        }
    }

    /**
     * 
     * @return \GearmanClient
     */
    protected function getClient()
    {
        if (!is_null($this->client)) {
            return $this->client;
        }

        $config = Config::load('gearman');
        $this->client = new \GearmanClient();
        $this->client->addServer($config['host'], $config['port']);

        return $this->client;
    }

}
