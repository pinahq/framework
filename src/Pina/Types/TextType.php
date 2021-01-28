<?php

namespace Pina\Types;

use Pina\Controls\FormTextarea;

class TextType implements TypeInterface
{

    public function makeControl()
    {
        /** @var FormInput $input */
        $input = \Pina\App::make(FormTextarea::class);
        $input->setType('text');
        return $input;
    }

    public function getSize()
    {
        return 1024 * 1024;
    }

    public function getDefault()
    {
        return '';
    }

    public function isNullable()
    {
        return true;
    }

    public function getVariants()
    {
        return [];
    }

}
