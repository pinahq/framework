<?php

namespace Pina\StaticResource;

class Script extends StaticResource
{

    public function getType()
    {
        return 'js';
    }

    public function getTag()
    {
        if (empty($this->src)) {
            return $this->content;
        }

        $src = $this->isExternalUrl() ? $this->src : $this->makeLocalUrl();

        return '<script src="' . $src . '" type="text/javascript"></script>';
    }

}
