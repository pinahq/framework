<?php

namespace Pina\Types;

use Pina\Controls\Control;
use Pina\Components\Field;

interface TypeInterface
{

    /**
     * @return Control 
     */
    public function makeControl(Field $field, $value);

    /**
     * @return int
     */
    public function getSize();

    /**
     * @return mixed
     */
    public function getDefault();

    /**
     * @return bool
     */
    public function isNullable();

    /*
     * @return array
     */
    public function getVariants();

    /*
     * Проверяет значение на предмет наличия ошибок перед сохранением
     * Возвращает описание ошибки и меняет значение при необходимости
     * @param mixed $value
     * @return null|string
     */
    public function validate(&$value);
}
