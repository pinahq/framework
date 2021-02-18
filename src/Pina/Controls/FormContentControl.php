<?php


namespace Pina\Controls;

/**
 * Контрол с произвольным контентом на месте поле ввода, но оформленный по требованиям формы: с лейблом и внутри form-group
 * @package Pina\Controls
 */
class FormContentControl extends FormInput
{

    protected $content;

    /**
     * Указать контент, который будет отрисовываться на месте поля ввода
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    protected function drawControl()
    {
        return $this->content;
    }

}