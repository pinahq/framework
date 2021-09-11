<?php

namespace Pina\Components;

use Pina\App;
use Pina\Types\TypeInterface;

class Field
{

    protected $key = '';
    protected $title = '';
    protected $type = '';
    protected $isMandatory = '';
    protected $default = null;

    /**
     * Создает экземпляр поля
     * @param string $key
     * @param string $title
     * @param mixed $type
     * @param boolean $isMandatory
     * @param mixed $default
     * @return \static
     */
    public static function make($key, $title, $type = 'string', $isMandatory = false, $default = null)
    {
        $field = new static;
        $field->key = $key;
        $field->title = $title;
        $field->type = $type;
        $field->isMandatory = $isMandatory;
        $field->default = $default;
        return $field;
    }

    /**
     * Получает ключ поля
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Получить наименование поля
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Получить тип поля
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        if (!is_null($this->default)) {
            return $this->default;
        }
        return App::type($this->type)->getDefault();
    }

    public function makeSQLDeclaration($definitions)
    {
        $type = App::type($this->type);
        $default = $this->getFormattedDefault($type);
        if (in_array('AUTO_INCREMENT', $definitions)) {
            $default = 'AUTO_INCREMENT';
        }
        return \implode(
            ' ',
            \array_filter(
                [$type->getSQLType(), $type->isNullable() ? "" : "NOT NULL", $default]
            )
        );
    }

    /**
     * @param TypeInterface $type
     * @return mixed|string|null
     */
    private function getFormattedDefault($type)
    {
        $default = $this->getDefault();
        if (is_null($default)) {
            if ($type->isNullable()) {
                return 'DEFAULT NULL';
            } else {
                return '';
            }
        }

        if ($default == 'CURRENT_TIMESTAMP') {
            return 'DEFAULT ' . $default;
        }

        if (is_string($default)) {
            return "DEFAULT '" . $default . "'";
        }

        return 'DEFAULT ' . $default;
    }

}
