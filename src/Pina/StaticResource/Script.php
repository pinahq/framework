<?php

namespace Pina\StaticResource;

use Pina\Html;

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

        return Html::tag('script', '', ['src' => $src, 'type' => $this->getTagType()]);
    }

    protected function getTagType()
    {
        return 'text/javascript';
    }

}
