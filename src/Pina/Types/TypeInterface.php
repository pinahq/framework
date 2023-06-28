<?php

namespace Pina\Types;

use Pina\Controls\Control;
use Pina\Controls\FormControl;
use Pina\Data\Field;
use Pina\TableDataGateway;

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
    public function format($value): string;

    /**
     *
     * @param mixed $value
     * @return string
     */
    public function draw($value): string;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * @return mixed
     */
    public function getDefault();

    /**
     * Определяет, является ли тип принципиально nullable (как text и blob)
     * Даже если тип возвращает здесь true, nullable может быть задано на уровне типа
     * @return bool
     */
    public function isNullable(): bool;

    public function isSearchable(): bool;

    public function isFiltrable(): bool;

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
    public function getSQLType(): string;


    /**
     * Фильтрует данные в таблице, схема которой описана в терминах этого типа
     * @param TableDataGateway $query
     * @param array|string $key поле или список полей одного типа, по которым фильтруем
     * @param $value
     */
    public function filter(TableDataGateway $query, $key, $value): void;
}
