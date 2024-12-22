<?php

namespace Pina\Http;

use Pina\App;
use Pina\Url;

class Location
{

    protected $resource = '';

    /** @var \Pina\Http\Url  */
    protected $server;

    public function __construct($resource, \Pina\Http\Url $server = null)
    {
        $this->resource = $resource;
        $this->server = $server ?? new \Pina\Http\Url('');
    }

    public function link($pattern, $params = [])
    {
        $url = $this->server->__toString();

        $parts = explode('?', $pattern);
        $query = '';
        if (isset($parts[1])) {
            $pattern = $parts[0];
            $query = $this->query($parts[1], $params);
        }

        $resource = Url::resource($pattern, $params, $this->resource);

        $url .= ltrim($resource, '/');
        if ($query) {
            $url .= '?'.$query;
        } else {
            $ps = App::getParamsString($pattern, $params);
            $url .= !empty($ps) ? ('?' . $ps) : '';
        }

        if (!empty($params['anchor'])) {
            $url .= "#" . $params["anchor"];
        }

        return $url;
    }

    protected function query($pattern, $params)
    {
        $path = explode('&', $pattern);
        foreach ($path as $k => $p) {
            $parts = explode('=', $p);
            foreach ($parts as $kk => $pp) {
                if (isset($pp[0]) && $pp[0] == ':') {
                    $key = substr($pp, 1);
                    $parts[$kk] = isset($params[$key]) ? $params[$key] : '';
                }
            }
            $path[$k] = implode('=', $parts);
        }
        return implode('&', $path);
    }

    public function resource($pattern, $params = [])
    {
        return Url::resource($pattern, $params, $this->resource);
    }

    public function location($pattern, $params = []): Location
    {
        return new Location($this->resource($pattern, $params), $this->server);
    }

}
