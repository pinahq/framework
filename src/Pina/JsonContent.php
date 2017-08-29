<?php

namespace Pina;

class JsonContent implements ContentInterface
{
    
    protected $results;

    public function __construct(&$results)
    {
        $this->results = $results;
    }
    
    public function setErrors($errors)
    {
        $this->results['errors'] = $errors;
    }

    public function getType()
    {
        return 'application/json; charset=' . App::charset();
    }

    public function fetch()
    {
        return json_encode($this->results, JSON_UNESCAPED_UNICODE);
    }

}
