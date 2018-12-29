<?php

namespace Pina\StaticResource;

use Pina\App;

abstract class StaticResource
{

    protected $src = null;
    protected $content = null;

    abstract public function getType();

    abstract public function getTag();

    abstract public function getUnwrappedContent();

    public function setSrc($src)
    {
        $this->src = $src;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        if (!empty($this->content)) {
            return $this->getUnwrappedContent();
        }

        if (!empty($this->src)) {
            if (strpos('/../', $this->src) !== false) {
                throw new \Exception('access denied');
            }
            $file = App::path() . '/../public/' . ltrim($this->src, '/');
            if (file_exists($file)) {
                return file_get_contents($file);
            }
        }
        
        return '';
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
