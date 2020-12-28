<?php

namespace Pina\Http;

use Pina\App;
use Pina\Route;
use Pina\Url;

class Location
{
    
    protected $resource = '';

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function link($pattern, $params = [])
    {
        $url = App::baseUrl();
        
        $resource = Url::resource($pattern, $params, $this->resource);

        $ps = App::getParamsString($pattern, $params);

        $url .= ltrim($resource, '/');
        $url .= !empty($ps) ? ('?' . $ps) : '';

        if (!empty($params['anchor'])) {
            $url .= "#" . $params["anchor"];
        }
        
        return $url;
    }
    
}
