<?php

namespace Pina\StaticResource;

class Style extends StaticResource
{

    public function getType()
    {
        return 'css';
    }

    public function getTag()
    {
        if (empty($this->src)) {
            return $this->content;
        }

        $src = $this->isExternalUrl() ? $this->src : $this->makeLocalUrl();

        return '<link rel="stylesheet" href="' . $src . '" />';
    }

}
