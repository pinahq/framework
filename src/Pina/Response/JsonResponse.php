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
        $c = json_encode($this->makePacket('fail'), JSON_UNESCAPED_UNICODE);
        Language::rewrite($c);
        echo $c;
        die();
    }
    
    public function fetch($handler = '', $first = true)
    {
        $this->header('Pina-Response: json');
        $this->ok();
        $this->contentType('application/json');
        $c = json_encode($this->makePacket('ok'), JSON_UNESCAPED_UNICODE);
        Language::rewrite($c);
        return $c;
    }
}
