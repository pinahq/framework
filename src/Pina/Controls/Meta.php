<?php

namespace Pina\Controls;

use Pina\Html;

class Meta extends Control
{

    protected $lines = [];

    public function set($name, $content)
    {
        $this->lines[$name] = $content;
        return $this;
    }

    public function draw()
    {
        $r = '';
        foreach ($this->lines as $name => $content) {
            if ($name == 'title') {
                $r .= $this->drawTitle($content);
            } else {
                $r .= $this->drawMetaLine($name, $content);
            }
        }
        return $r;
    }

    protected function drawMetaLine($name, $content)
    {
        if (empty($content)) {
            return '';
        }
        return Html::tag('meta', '', $this->makeMetaAttributes($name, $content));
    }

    protected function drawTitle($content)
    {
        return Html::tag('title', $content);
    }

    protected function makeMetaAttributes($name, $content)
    {
        $key = $this->resolveNameKey($name);
        return [
            $key => $name,
            'content' => $content,
        ];
    }

    protected function resolveNameKey($name)
    {
        if ($this->isPropertyAttribute($name)) {
            return 'property';
        }
        return 'name';
    }

    protected function isPropertyAttribute($name)
    {
        if (strpos($name, 'og:') === 0) {
            return true;
        }
        return false;
    }

}