<?php

namespace Pina\StaticResource;

class Javascript extends StaticResource
{

    public function getType()
    {
        return 'js';
    }

    public function getTag()
    {
        if (empty($this->src)) {
            return '<script>' . $this->getUnwrappedContent() . '</script>';
        }

        $src = $this->isExternalUrl() ? $this->src : $this->makeLocalUrl();

        return '<script src="' . $src . '" type="text/javascript"></script>';
    }

    public function getUnwrappedContent()
    {
        $trimmed = trim($this->content);
        if (preg_match('/^<script>(.*)<\/script>$/si', $trimmed, $matches)) {
            return $matches[1];
        }
        return $trimmed;
    }

}
