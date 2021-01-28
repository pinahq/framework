<?php

namespace Pina\Types;

use Pina\Controls\FormInput;

class IntegerType implements TypeInterface
{

    public function makeControl()
    {
        return \Pina\App::make(FormInput::class)->setType('number');
    }

    public function getSize()
    {
        return 11;
    }

    public function getDefault()
    {
        return 0;
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
