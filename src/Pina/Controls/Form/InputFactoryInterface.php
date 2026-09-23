<?php

namespace Pina\Controls\Form;

use Pina\Data\Field;

interface InputFactoryInterface
{
    public function makeInput(Field $field);
}