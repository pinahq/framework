<?php

namespace Pina\Controls\Components;

use Pina\App;

class PermittedLinkedButton extends LinkedButton
{
    protected function draw(): string
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