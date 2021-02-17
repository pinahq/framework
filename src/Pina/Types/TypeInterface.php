<?php

namespace Pina\Types;

use Pina\Controls\Control;
use Pina\Components\Field;

interface TypeInterface
{

    /**
     *
     * @param array $context
     * @return static
     */
    public function setContext($context);

    /**
     * @return Control
     */
    public function makeControl(Field $field, $value);

    /**
     * 
     * @param mixed $value
     * @return string
     */
    public function format($value);

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
