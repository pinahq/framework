<?php


namespace Pina\Controls;


class FormContentControl extends FormInput
{

    protected $content;

    public function setContent($content)
    {
        $this->content = $content;
    }

    protected function drawControl()
    {
        return $this->content;
    }

}