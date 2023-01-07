<?php

namespace Pina\Types;

use Pina\Controls\Control;
use Pina\Controls\FormControl;
use Pina\Data\Field;
use Pina\SQL;

interface TypeInterface
{

    /**
     *
     * @param array $context
     * @return static
     */
    public function setContext($context);

    /**
     * @param Field $field
     * @param mixed $value
     * @return Control
     */
    public function makeControl(Field $field, $value): FormControl;

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
     * Загружает данные из внешнего источника, если таковой подразумевается, иначе возвращает null
     * @param $id
     * @return mixed
     */
    public function getData($id);

    /**
     * Сохраняет данные во внешний источник, если таковой подразумевается, иначе ничего не делает
     * @param $id
     * @param $value
     * @return mixed
     */
    public function setData($id, $value);

    /**
     * @return string
     */
    public function getSQLType();


    public function filter(SQL $query, string $key, $value);
}
