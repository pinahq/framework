<?php

namespace Pina\Response;

use Pina\Language;

class JsonResponse extends Response
{
    public $results = array();
    
    public $isFailed = false;
    
    public $redirect = false;
    
    public function makePacket($r)
    {
        $packet = $this->results;
        if ($this->messages) {
            $packet['__messages__'] = $this->messages;
        }

        return $packet;
    }
    
    public function fail()
    {
        $this->badRequest();
        $this->contentType('application/json');
        echo Language::rewrite(json_encode($this->makePacket('fail'), JSON_UNESCAPED_UNICODE));
        die();
    }
    
    public function fetch($handler = '', $first = true)
    {
        $this->header('Pina-Response: Json');
        $this->ok();
        $this->contentType('application/json');
        return Language::rewrite(json_encode($this->makePacket('ok'), JSON_UNESCAPED_UNICODE));
    }
}
