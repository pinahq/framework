<?php

namespace Pina\Controls;

use Pina\Access;
use Pina\App;

class PermittedLinkedButton extends LinkedButton
{
    protected function draw()
    {
        $parsed = parse_url($this->link);
        if ($parsed['host'] == App::host()) {
            if (!App::access()->isPermitted($parsed['path'])) {
                return '';
            }
        }
        return parent::draw();
    }
}