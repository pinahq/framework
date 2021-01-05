<?php

namespace Pina\StaticResource;

use Pina\App;

abstract class StaticResource
{

    protected $src = null;
    protected $content = null;

    abstract public function getType();

    abstract public function getTag();

    public function setSrc($src)
    {
        $this->src = $src;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    public function getContent()
    {
        return $this->content;
    }

    public function isExternalUrl()
    {
        if (empty($this->src)) {
            return false;
        }

        $parsed = parse_url($this->src);
        if (!empty($parsed['host'])) {
            return true;
        }

        return false;
    }

    protected function makeLocalUrl()
    {
        $static = \Pina\Config::get('app', 'static');
        $version = \Pina\App::version();
        $v = $version ? ('?' . $version) : '';
        return rtrim($static, '/') . '/' . ltrim($this->src, '/') . $v;
    }

}
