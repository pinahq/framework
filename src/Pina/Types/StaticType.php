<?php

namespace Pina\Types;

use Pina\Controls\FormStatic;

class StaticType extends StringType
{

    public function makeControl()
    {
        /** @var FormInput $input */
        $input = \Pina\App::make(FormStatic::class);
        return $input;
    }

}
