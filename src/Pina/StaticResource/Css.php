<?php

namespace Pina\StaticResource;

class Css extends StaticResource
{

    public function getType()
    {
        return 'css';
    }

    public function getTag()
    {
        if (empty($this->src)) {
            return '<style>' . $this->getUnwrappedContent() . '</style>';
        }

        $src = $this->isExternalUrl() ? $this->src : $this->makeLocalUrl();

        return '<link rel="stylesheet" href="' . $src . '" />';
    }

    public function getUnwrappedContent()
    {
        $trimmed = trim($this->content);
        if (preg_match('/^<style>(.*)<\/style>$/si', $trimmed, $matches)) {
            return $matches[1];
        }
        return $trimmed;
    }

}
