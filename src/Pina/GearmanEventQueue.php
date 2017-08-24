<?php

namespace Pina;

class GearmanEventQueue implements EventQueueInterface
{
    private $client = null;
    
    public function push($handler, $data)
    {
        $this->getClient()->doBackground($handler, $data);
    }
    
    protected function getClient()
    {
        if(!is_null($this->client)) {
            return $this->client;
        }
        
        $config = Config::load('gearman');
        $this->client = new \GearmanClient();
        $this->client->addServer($config['host'], $config['port']);
        
        return $this->client;
    }
    
    
}