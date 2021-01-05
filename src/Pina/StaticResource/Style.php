<?php

namespace Pina\StaticResource;

use Pina\Html;

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

        return Html::tag('link', '', ['rel' => 'stylesheet', 'href' => $src]);
    }

}
