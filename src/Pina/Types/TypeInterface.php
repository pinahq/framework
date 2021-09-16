<?php

namespace Pina\Types;

use Pina\Controls\Control;
use Pina\Data\Field;

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
     * Определяет, является ли тип принципиально nullable (как text и blob)
     * Даже если тип возвращает здесь true, nullable может быть задано на уровне типа
     * @return bool
     */
    public function isNullable();

    /**
     * @return array
     */
    public function getVariants();

    /**
     * Проверяет значение на предмет наличия ошибок и нормализует его
     * Возвращает нормализованное значение
     * @param mixed $value
     * @param bool $isMandatory
     * @return mixed
     */

    public function normalize($value, $isMandatory);

    /**
     * @return string
     */
    public function getSQLType();
}
