<?php

namespace Pina\Types;

use Pina\Controls\FormInput;

class StringType implements TypeInterface
{

    public function makeControl()
    {
        /** @var FormInput $input */
        $input = \Pina\App::make(FormInput::class);
        $input->setType('text');
        return $input;
    }

    public function getSize()
    {
        return 512;
    }

    public function getDefault()
    {
        return '';
    }

    public function isNullable()
    {
        return false;
    }
    
    public function getVariants()
    {
        return [];
    }

}
