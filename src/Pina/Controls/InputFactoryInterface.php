<?php

namespace Pina\Controls;

use Pina\Data\DataRecord;
use Pina\Data\Field;

interface InputFactoryInterface
{
    public function makeInput(Field $field, DataRecord $record);
}